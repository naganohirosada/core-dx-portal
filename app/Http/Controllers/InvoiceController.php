<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\Account;
use App\Models\Journal;
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
        $invoices = Invoice::with('project.company')->latest()->get();
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

        $issuer = \App\Models\SystemSetting::first() ?? new \App\Models\SystemSetting([
            'company_name' => '自社未設定',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
            'issuer'  => $issuer, 
        ]);
    }

    /** 💡 入金ステータスの更新（売掛消込の自動仕訳） */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,paid',
        ]);

        DB::transaction(function () use ($validated, $invoice) {
            $oldStatus = $invoice->status;
            $invoice->update($validated);

            // 「paid（入金済）」に変更されたタイミングで入金仕訳を生成
            if ($validated['status'] === 'paid' && $oldStatus !== 'paid') {
                $futsuAccount = Account::firstOrCreate(['code' => '1112'], ['name' => '普通預金', 'type' => 'asset']);
                $urikakeAccount = Account::firstOrCreate(['code' => '1131'], ['name' => '売掛金', 'type' => 'asset']);

                $journal = Journal::create([
                    'date' => Carbon::now()->toDateString(), 
                    'description' => "売掛金回収(入金完了): {$invoice->invoice_number}",
                ]);

                // 借方: 普通預金
                $journal->details()->create([
                    'account_id' => $futsuAccount->id,
                    'side' => 'debit',
                    'amount' => $invoice->amount,
                ]);

                // 貸方: 売掛金 (消込)
                $journal->details()->create([
                    'account_id' => $urikakeAccount->id,
                    'side' => 'credit',
                    'amount' => $invoice->amount,
                ]);
            }
        });

        return redirect()->back();
    }
}