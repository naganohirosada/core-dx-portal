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

    /** 💡 請求書の新規発行処理 */
    public function store(Request $request)
    {
        Gate::authorize('approve-and-invoice');
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'amount' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // 🛠️ 請求書番号の自動生成ロジック (例: INV-20260603-001)
        $dateStr = Carbon::parse($validated['issue_date'])->format('Ymd');
        $count = Invoice::whereDate('issue_date', $validated['issue_date'])->count() + 1;
        $invoiceNumber = 'INV-' . $dateStr . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        Invoice::create(array_merge($validated, ['invoice_number' => $invoiceNumber]));

        return redirect()->route('invoices.index');
    }

    /** 💡 請求書のPDF風プレビュー詳細画面 */
    public function show(Invoice $invoice)
    {
        $invoice->load('project.company');
        return Inertia::render('Invoices/Show', ['invoice' => $invoice]);
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