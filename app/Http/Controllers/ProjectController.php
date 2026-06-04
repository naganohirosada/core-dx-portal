<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /** 案件一覧 */
    public function index()
    {
        // 💡 リレーション先のcompany情報も一緒にガバッと取得（Eager Loading）
        $projects = Project::with('company')->latest()->get();
        return Inertia::render('Projects/Index', ['projects' => $projects]);
    }

    /** 💡 案件登録画面 */
    public function create()
    {
        // 💡 セレクトボックスで選ばせるために、有効な顧客一覧を渡す
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        return Inertia::render('Projects/Create', ['companies' => $companies]);
    }

    public function show(\App\Models\Project $project)
    {
        // 案件の親である顧客(company)と、子であるタスク(tasks)を同時ロード
        $project->load(['company', 'tasks']);

        return \Inertia\Inertia::render('Projects/Show', [
            'project' => $project
        ]);
    }

    /** 案件登録処理 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|integer|min:0',
        ]);

        Project::create($validated);
        return redirect()->route('projects.index');
    }

    /** 💡 案件編集画面 */
    public function edit(Project $project)
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'companies' => $companies
        ]);
    }

    /** 💡 案件更新処理 */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|integer|min:0',
            'status' => 'required|string|in:lead,ongoing,completed,suspended',
        ]);

        $project->update($validated);
        return redirect()->route('projects.index');
    }
}