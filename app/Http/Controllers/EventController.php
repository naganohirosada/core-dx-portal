<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    /** カレンダー画面の表示 */
    public function index()
    {
        // 💡 すべての予定を取得
        $events = Event::all();
        // 💡 予定に紐づけるための案件一覧も取得
        $projects = Project::orderBy('name')->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'projects' => $projects,
        ]);
    }

    /** 予定の保存 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'is_all_day' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        Event::create($validated);
        return redirect()->route('events.index');
    }

    /** 予定の削除 */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index');
    }
}