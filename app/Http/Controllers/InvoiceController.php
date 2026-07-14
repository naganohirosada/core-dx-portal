<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    /** 💡 請求書一覧・作成画面 */
    public function index()
    {
        // 案件およびその親の顧客データまでガバッと取得（ネストされたリレーション）
        $invoices = Invoice::with('project.company')->latest()->get();
        $projects = Project::with('company')->get();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'projects' => $projects,
        ]);
    }

    /**
     * 💡 請求書の新規発行処理（インボイス制度準拠版）
     */
    public function store(Request $request)
    {
        Gate::authorize('approve-and-invoice');

        // 1. バリデーションの拡張（税率指定を追加）
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'amount' => 'required|integer|min:0', // ※税抜の基準金額として扱います
            'tax_rate' => 'nullable|in:8,10',     // ★ 8%（軽減税率）か10%（標準税率）を選択可能に
            'notes' => 'nullable|string',
        ]);


        // デフォルト税率を10%に設定
        $taxRate = $validated['tax_rate'] ?? 10;

        // 2. ★ インボイス要件：税率ごとの区分計算と端数処理（切り捨て）
        // インボイス法では「1つの請求書につき、税率ごとに1回のみ端数処理を行う」と定められています
        $subtotal10 = 0;
        $taxAmount10 = 0;
        $subtotal8 = 0;
        $taxAmount8 = 0;

        if ($taxRate == 10) {
            $subtotal10 = $validated['amount'];
            $taxAmount10 = floor($subtotal10 * 0.10); // 切り捨て処理
        } else if ($taxRate == 8) {
            $subtotal8 = $validated['amount'];
            $taxAmount8 = floor($subtotal8 * 0.08); // 切り捨て処理
        }

        // 税込総合計を算出
        $totalAmount = $validated['amount'] + $taxAmount10 + $taxAmount8;

        // 🛠️ 請求書番号の自動生成ロジック (例: INV-20260714-001)
        $dateStr = Carbon::parse($validated['issue_date'])->format('Ymd');
        $count = Invoice::whereDate('issue_date', $validated['issue_date'])->count() + 1;
        $invoiceNumber = 'INV-' . $dateStr . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        // 3. データベースへ保存（計算したインボイス用データをマージ）
        Invoice::create(array_merge($validated, [
            'invoice_number'        => $invoiceNumber,
            'subtotal_10_percent'   => $subtotal10,
            'tax_amount_10_percent' => $taxAmount10,
            'subtotal_8_percent'    => $subtotal8,
            'tax_amount_8_percent'  => $taxAmount8,
            'amount'          => $totalAmount, // 税込合計金額
        ]));

        return redirect()->route('invoices.index');
    }

    /**
     * 💡 請求書のPDF風プレビュー詳細画面
     */
    public function show(Invoice $invoice)
    {
        // 請求先（クライアント）の会社情報をロード
        $invoice->load('project.company');

        $issuer = \App\Models\SystemSetting::first() ?? new \App\Models\SystemSetting([
            'company_name' => '自社未設定',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
            'issuer'  => $issuer, // DBから取得した自社情報をVueへ渡す
        ]);

    }

    /** 💡 入金ステータスなどのクイック更新 */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,paid',
        ]);

        $invoice->update($validated);
        return redirect()->back();
    }
}