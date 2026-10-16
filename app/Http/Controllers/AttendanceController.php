<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Inertia\Inertia;
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

    /** 💡 月次の勤怠集計（社員ごとの出勤日数・実労働時間）。管理者・マネージャーのみ */
    public function summary(Request $request)
    {
        Gate::authorize('approve-and-invoice');

        $month = $request->filled('month') && preg_match('/^\d{4}-\d{2}$/', $request->input('month'))
            ? Carbon::createFromFormat('Y-m-d', $request->input('month') . '-01')
            : Carbon::today()->startOfMonth();

        $attendances = Attendance::whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->get()
            ->groupBy('user_id');

        $rows = User::orderBy('id')->get(['id', 'name'])->map(function (User $user) use ($attendances) {
            $records = $attendances->get($user->id, collect());
            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'work_days' => $records->whereNotNull('clock_in')->count(),
                'total_hours' => round($records->sum(fn ($a) => $a->workedHours()), 1),
                'missing_clock_out' => $records->whereNotNull('clock_in')->whereNull('clock_out')->count(),
            ];
        });

        return Inertia::render('Attendance/Summary', [
            'month' => $month->format('Y-m'),
            'rows' => $rows,
        ]);
    }
}