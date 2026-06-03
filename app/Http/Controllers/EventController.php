<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class EventController extends Controller
{
    /** カレンダー画面の表示 */
    public function index()
    {
        // 💡 紐づいているユーザー情報(users)も含めて予定を取得
        $events = Event::with('users')->get()->map(function($event) {
            // FullCalendarが認識しやすいように、タイトルに共有メンバーの名前をオマケで付与
            $memberNames = $event->users->pluck('name')->join(', ');
            $suffix = $memberNames ? " (共同: {$memberNames})" : "";
            
            return [
                'id' => $event->id,
                'title' => $event->title . $suffix,
                'start' => $event->start,
                'end' => $event->end,
                'is_all_day' => $event->is_all_day,
                'description' => $event->description,
            ];
        });

        $projects = Project::orderBy('name')->get();
        // 💡 セレクトボックス用に全ユーザーを取得
        $users = User::orderBy('name')->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'projects' => $projects,
            'users' => $users, // 💡 Vueへトス
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
            'user_ids' => 'nullable|array', // 💡 共有メンバーのID配列
            'user_ids.*' => 'exists:users,id',
        ]);

        // 1. スケジュール本体を保存
        $event = Event::create($validated);

        // 2. 💡 中間テーブルに選択されたユーザーIDを一撃で同期保存（多対多の神メソッド）
        if (!empty($validated['user_ids'])) {
            $event->users()->sync($validated['user_ids']);
        }

        return redirect()->route('events.index');
    }

    /** 予定の削除 */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index');
    }
}