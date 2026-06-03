<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WorkflowController extends Controller
{
    /** 💡 申請一覧画面の表示 */
    public function index()
    {
        // 自分が申請したデータ
        $myWorkflows = Workflow::where('user_id', Auth::id())->latest()->get();
        
        // 【管理用】全ユーザーの承認待ちデータ
        $pendingWorkflows = Workflow::with('user')->where('status', 'pending')->latest()->get();

        return Inertia::render('Workflows/Index', [
            'myWorkflows' => $myWorkflows,
            'pendingWorkflows' => $pendingWorkflows,
        ]);
    }

    /** 💡 新規申請の保存 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:expense,ringi',
            'title' => 'required|string|max:255',
            'amount' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        Auth::user()->workflows()->create($validated);

        return redirect()->route('workflows.index');
    }

    /** 💡 申請の承認 */
    public function approve(Workflow $workflow)
    {
        $workflow->update(['status' => 'approved']);
        return redirect()->back();
    }
    
    /** 💡 申請の却下 */
    public function reject(Workflow $workflow)
    {
        $workflow->update(['status' => 'rejected']);
        return redirect()->back();
    }
}