<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /** 💡 特定の案件のタスクボード（カンバン）表示 */
    public function board(Project $project)
    {
        // 案件に紐づくタスクを全件取得
        $tasks = $project->tasks()->latest()->get();

        return Inertia::render('Tasks/Board', [
            'project' => $project,
            'tasks' => $tasks
        ]);
    }

    /** タスクの新規追加 */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        // 💡 案件に紐づけて作成
        $project->tasks()->create($validated);

        return redirect()->route('projects.tasks.board', $project->id);
    }

    /** 💡 ドラッグ＆ドロップ等によるステータス更新 */
    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,doing,done',
        ]);

        $task->update($validated);

        // クイック更新なので、直前の画面（ボード）にバックする
        return redirect()->back();
    }
}
