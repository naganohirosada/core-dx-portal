<?php

namespace App\Http\Controllers;

use App\Models\PaidLeave;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class PaidLeaveController extends Controller
{
    /** 💡 有給の申請処理 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        // 残日数（申請中の分を差し引いた日数）を超える申請は受け付けない
        $requestedDays = PaidLeave::countWeekdays(Carbon::parse($validated['start_date']), Carbon::parse($validated['end_date']));
        $balance = Auth::user()->paidLeaveBalance();
        $available = $balance['remaining'] - $balance['pending'];
        if ($requestedDays > $available) {
            return redirect()->back()->withErrors([
                'start_date' => "有給の残日数が不足しています（申請 {$requestedDays}日 / 申請可能 {$available}日）。",
            ]);
        }

        // ログインユーザーに紐づけて承認待ち(pending)で作成
        Auth::user()->paidLeaves()->create($validated);

        return redirect()->back();
    }

    /** 💡 有給の承認処理 ＆ 【神ギミック：カレンダー自動連動】 */
    public function approve(PaidLeave $paidLeave)
    {
        Gate::authorize('approve-and-invoice');
        // 1. 申請のステータスを「承認済」に更新
        $paidLeave->update(['status' => 'approved']);

        // 2. ★★★ 神ギミック発動 ★★★
        // 共有カレンダー（eventsテーブル）に自動で予定をねじ込む！
        Event::create([
            'title' => "[有給] " . $paidLeave->user->name,
            'start' => $paidLeave->start_date->format('Y-m-d') . 'T00:00:00',
            // 終日予定として綺麗に見せるため、終了日の23:59まで指定
            'end' => $paidLeave->end_date->format('Y-m-d') . 'T23:59:59',
            'is_all_day' => true,
            'description' => "有給取得理由: " . ($paidLeave->reason ?? '特になし'),
        ]);

        return redirect()->back();
    }
}