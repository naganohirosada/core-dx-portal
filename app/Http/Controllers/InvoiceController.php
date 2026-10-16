<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\Account;
use App\Models\Journal;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /** 💡 請求書一覧・作成画面 */
    public function index()
    {
        $invoices = Invoice::with('project.company')->withSum('payments as paid_amount', 'amount')->latest()->get();
        $projects = Project::with('company')->get();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'projects' => $projects,
        ]);
    }

    /**
     * 💡 請求書の新規発行処理（マルチテナント ＆ 自動仕訳連携）
     */
    public function store(Request $request)
    {
        Gate::authorize('approve-and-invoice');

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'transaction_date' => 'required|date',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|integer|min:0',
            'items.*.tax_rate' => 'required|in:8,10,0',
        ]);

        // 紐づく案件から顧客の company_id を自動取得
        $project = Project::findOrFail($validated['project_id']);
        $companyId = $project->company_id;

        // 入力は明細ごとに税抜きで与えられる想定。サーバ側で税計算し合計を算出する
        $subtotal10 = 0;
        $taxAmount10 = 0;
        $subtotal8 = 0;
        $taxAmount8 = 0;
        $subtotal0 = 0;

        $itemsData = [];
        foreach ($validated['items'] as $it) {
            $lineSubtotal = intval($it['quantity']) * intval($it['unit_price']);
            $taxRate = intval($it['tax_rate']);
            if ($taxRate === 10) {
                $subtotal10 += $lineSubtotal;
            } else if ($taxRate === 8) {
                $subtotal8 += $lineSubtotal;
            } else {
                $subtotal0 += $lineSubtotal;
            }

            $itemsData[] = array_merge($it, ['subtotal' => $lineSubtotal]);
        }

        $taxAmount10 = (int) floor($subtotal10 * 0.10);
        $taxAmount8 = (int) floor($subtotal8 * 0.08);
        $totalAmount = $subtotal10 + $subtotal8 + $subtotal0 + $taxAmount10 + $taxAmount8;

        $dateStr = Carbon::parse($validated['issue_date'])->format('Ymd');
        $count = Invoice::whereDate('issue_date', $validated['issue_date'])->count() + 1;
        $invoiceNumber = 'INV-' . $dateStr . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        // 🛠️ トランザクション処理開始
        DB::transaction(function () use ($validated, $companyId, $invoiceNumber, $subtotal10, $taxAmount10, $subtotal8, $taxAmount8, $totalAmount, $itemsData) {
            
            // 1. 請求書の作成（company_id を追加結合）
            $invoice = Invoice::create(array_merge($validated, [
                'company_id'            => $companyId,
                'invoice_number'        => $invoiceNumber,
                'subtotal_10_percent'   => $subtotal10,
                'tax_amount_10_percent' => $taxAmount10,
                'subtotal_8_percent'    => $subtotal8,
                'tax_amount_8_percent'  => $taxAmount8,
                'amount'                => $totalAmount, 
                'status'                => 'sent',       
            ]));

            // 1.1 明細の保存
            foreach ($itemsData as $it) {
                $invoice->items()->create([
                    'description' => $it['description'] ?? null,
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'tax_rate' => $it['tax_rate'],
                    'subtotal' => $it['subtotal'],
                ]);
            }

            // 2. 勘定科目の確保（グローバルスコープ/テナントの縛りに配慮）
            $uriageAccount = Account::firstOrCreate(['code' => '4111'], ['name' => '売上高', 'type' => 'revenue']);
            $urikakeAccount = Account::firstOrCreate(['code' => '1131'], ['name' => '売掛金', 'type' => 'asset']);

            // 3. 仕訳（Journal）の自動生成
            // ※JournalモデルにもBelongsToTenantが効いている場合、自動で現在のテナントIDが注入されます
            $journal = Journal::create([
                'date' => $invoice->issue_date,
                'description' => "請求書発行による売上計上: {$invoice->invoice_number}",
            ]);

            // 借方: 売掛金 (資産の増加)
            $journal->details()->create([
                'account_id' => $urikakeAccount->id,
                'side' => 'debit',
                'amount' => $totalAmount,
            ]);

            // 貸方: 売上高 (収益の発生)
            $journal->details()->create([
                'account_id' => $uriageAccount->id,
                'side' => 'credit',
                'amount' => $totalAmount,
            ]);
        });

        return redirect()->route('invoices.index');
    }

    /**
     * 💡 請求書の詳細画面
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('project.company', 'items');

        // 発行元はログインユーザーが所属する会社（テナント）の設定を使う
        $issuer = \App\Models\SystemSetting::find(auth()->user()->system_setting_id) ?? new \App\Models\SystemSetting([
            'company_name' => '自社未設定',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
            'issuer'  => $issuer, 
        ]);
    }

    /** 💡 入金ステータスの更新（「入金済」にした場合は未回収残高を全額消込して自動仕訳） */
    public function updateStatus(Request $request, Invoice $invoice, JournalService $journals)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,paid',
        ]);

        DB::transaction(function () use ($validated, $invoice, $journals) {
            $oldStatus = $invoice->status;
            $invoice->update($validated);

            // 「paid（入金済）」に変更されたタイミングで、残っている売掛金を全額入金として消込む
            if ($validated['status'] === 'paid' && $oldStatus !== 'paid') {
                $outstanding = $invoice->outstandingAmount();
                if ($outstanding > 0) {
                    $this->recordPayment($invoice, Carbon::now()->toDateString(), $outstanding, $journals);
                }
            }
        });

        return redirect()->back();
    }

    /** 💡 入金消込（入金日と入金額を登録。一部入金にも対応し、全額回収で「入金済」にする） */
    public function storePayment(Request $request, Invoice $invoice, JournalService $journals)
    {
        Gate::authorize('approve-and-invoice');

        $outstanding = $invoice->outstandingAmount();
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => "required|integer|min:1|max:{$outstanding}",
        ], [
            'amount.max' => "入金額が未回収残高（{$outstanding}円）を超えています。",
        ]);

        DB::transaction(function () use ($validated, $invoice, $journals) {
            $this->recordPayment($invoice, $validated['payment_date'], (int) $validated['amount'], $journals);

            if ($invoice->outstandingAmount() <= 0) {
                $invoice->update(['status' => 'paid']);
            }
        });

        return redirect()->back();
    }

    /** 💡 売掛金の年齢調べ（支払期日からの経過日数で未回収債権を区分） */
    public function aging()
    {
        $today = Carbon::today();
        $buckets = ['current' => '期日内', 'd1_30' => '1〜30日', 'd31_60' => '31〜60日', 'd61_90' => '61〜90日', 'd91_plus' => '91日超'];

        $rows = Invoice::with('company')
            ->withSum('payments as paid_amount', 'amount')
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->get()
            ->map(function (Invoice $invoice) use ($today) {
                $outstanding = (int) $invoice->amount - (int) $invoice->paid_amount;
                $overdueDays = $invoice->due_date->lt($today) ? (int) $invoice->due_date->diffInDays($today) : 0;
                $bucket = match (true) {
                    $overdueDays === 0 => 'current',
                    $overdueDays <= 30 => 'd1_30',
                    $overdueDays <= 60 => 'd31_60',
                    $overdueDays <= 90 => 'd61_90',
                    default => 'd91_plus',
                };

                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'company_name' => $invoice->company?->name ?? '（顧客未設定）',
                    'due_date' => $invoice->due_date->toDateString(),
                    'amount' => (int) $invoice->amount,
                    'outstanding' => $outstanding,
                    'overdue_days' => $overdueDays,
                    'bucket' => $bucket,
                ];
            })
            ->filter(fn ($row) => $row['outstanding'] > 0)
            ->values();

        // 顧客別 × 経過区分の集計
        $summary = $rows->groupBy('company_name')->map(function ($items, $companyName) use ($buckets) {
            $line = ['company_name' => $companyName, 'total' => $items->sum('outstanding')];
            foreach (array_keys($buckets) as $key) {
                $line[$key] = $items->where('bucket', $key)->sum('outstanding');
            }
            return $line;
        })->values();

        $totals = ['total' => $rows->sum('outstanding')];
        foreach (array_keys($buckets) as $key) {
            $totals[$key] = $rows->where('bucket', $key)->sum('outstanding');
        }

        return Inertia::render('Accounting/Receivables', [
            'buckets' => $buckets,
            'summary' => $summary,
            'totals' => $totals,
            'rows' => $rows,
            'today' => $today->toDateString(),
        ]);
    }

    /** 入金を記録し「普通預金 / 売掛金」の仕訳を起票する */
    private function recordPayment(Invoice $invoice, string $date, int $amount, JournalService $journals): void
    {
        $invoice->payments()->create(['payment_date' => $date, 'amount' => $amount]);

        $journals->post($date, "売掛金回収(入金消込): {$invoice->invoice_number}", [
            ['code' => '1112', 'name' => '普通預金', 'type' => 'asset', 'side' => 'debit', 'amount' => $amount],
            ['code' => '1131', 'name' => '売掛金', 'type' => 'asset', 'side' => 'credit', 'amount' => $amount],
        ]);
    }
}