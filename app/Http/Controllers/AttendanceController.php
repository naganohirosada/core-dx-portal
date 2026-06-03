<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /** 各種打刻処理 */
    public function punch(Request $request)
    {
        $type = $request->input('type'); // clock_in, clock_out, break_start, break_end
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        // 今日のデータがすでにあるか確認、なければ新規作成
        $attendance = Attendance::firstOrNew([
            'user_id' => $userId,
            'date' => $today
        ]);

        switch ($type) {
            case 'clock_in':
                $attendance->clock_in = $now;
                $attendance->status = '勤務中';
                break;
            case 'break_start':
                $attendance->break_start = $now;
                $attendance->status = '休憩中';
                break;
            case 'break_end':
                $attendance->break_end = $now;
                $attendance->status = '勤務中';
                break;
            case 'clock_out':
                $attendance->clock_out = $now;
                $attendance->status = '退勤済';
                break;
        }

        $attendance->save();

        return redirect()->back(); // ダッシュボードへ戻る
    }
}