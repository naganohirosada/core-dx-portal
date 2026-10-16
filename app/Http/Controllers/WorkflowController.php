<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Services\JournalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class WorkflowController extends Controller
{
    /** 💡 申請一覧画面の表示 */
    public function index()
    {
        // 自分が申請したデータ
        $myWorkflows = Workflow::where('user_id', Auth::id())->latest()->get();
        
        // 【管理用】全ユーザーの承認待ちデータ
        $pendingWorkflows = Workflow::with('user')->where('status', 'pending')->latest()->get();

        $expenseCategories = collect(Workflow::EXPENSE_ACCOUNTS)
            ->map(fn ($account, $key) => ['value' => $key, 'label' => $account[1]])
            ->values();

        return Inertia::render('Workflows/Index', [
            'myWorkflows' => $myWorkflows,
            'pendingWorkflows' => $pendingWorkflows,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /** 💡 新規申請の保存 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:expense,ringi',
            // 経費精算は承認時に仕訳を起こすため、経費区分と金額を必須にする
            'expense_category' => 'nullable|required_if:type,expense|in:' . implode(',', array_keys(Workflow::EXPENSE_ACCOUNTS)),
            'title' => 'required|string|max:255',
            'amount' => 'nullable|required_if:type,expense|integer|min:0',
            'description' => 'nullable|string',
        ]);

        if ($validated['type'] !== 'expense') {
            $validated['expense_category'] = null;
        }

        Auth::user()->workflows()->create($validated);

        return redirect()->route('workflows.index');
    }

    /** 💡 申請の承認（経費精算は承認と同時に「経費 / 未払金」の仕訳を自動起票） */
    public function approve(Workflow $workflow, JournalService $journals)
    {
        Gate::authorize('approve-and-invoice');

        // 二重承認による仕訳の重複を防ぐ
        if ($workflow->status !== 'pending') {
            return redirect()->back()->withErrors(['workflow' => 'この申請はすでに処理済みです。']);
        }

        DB::transaction(function () use ($workflow, $journals) {
            $workflow->update(['status' => 'approved']);

            if ($workflow->type === 'expense' && $workflow->amount > 0) {
                [$code, $name] = Workflow::EXPENSE_ACCOUNTS[$workflow->expense_category] ?? Workflow::EXPENSE_ACCOUNTS['misc'];
                $journals->post(Carbon::today()->toDateString(), "経費精算承認: {$workflow->title}", [
                    ['code' => $code, 'name' => $name, 'type' => 'expense', 'side' => 'debit', 'amount' => $workflow->amount],
                    ['code' => '2121', 'name' => '未払金', 'type' => 'liability', 'side' => 'credit', 'amount' => $workflow->amount],
                ]);
            }
        });

        return redirect()->back();
    }

    /** 💡 申請の却下 */
    public function reject(Workflow $workflow)
    {
        Gate::authorize('approve-and-invoice');

        if ($workflow->status !== 'pending') {
            return redirect()->back()->withErrors(['workflow' => 'この申請はすでに処理済みです。']);
        }

        $workflow->update(['status' => 'rejected']);
        return redirect()->back();
    }
}