<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\WorkLog;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\PaidLeave;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $realTasks = Task::whereIn('status', ['todo', 'doing'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $todayAttendance = Attendance::where('user_id', $userId)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $currentStatus = $todayAttendance ? $todayAttendance->status : '未出勤';

        $pendingLeaves = PaidLeave::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        // 💡 ----------------------------------------------------
        // 📊 本物の作業ログから今月（2026年6月など）の週別稼働時間を集計！
        // 💡 ----------------------------------------------------
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        // 週ごとの個人の稼働時間を初期化 [第1週, 第2週, 第3週, 第4週]
        $personalHours = [0, 0, 0, 0];

        // 今月の全作業ログを取得
        $logs = WorkLog::where('user_id', $userId)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->get();

        foreach ($logs as $log) {
            $logDate = Carbon::parse($log->date);
            // 月の何週目かをざっくり計算（1〜7日は1週目、8〜14日は2週目...）
            $dayNum = $logDate->day;
            if ($dayNum <= 7)  $personalHours[0] += (float)$log->hours;
            elseif ($dayNum <= 14) $personalHours[1] += (float)$log->hours;
            elseif ($dayNum <= 21) $personalHours[2] += (float)$log->hours;
            else $personalHours[3] += (float)$log->hours;
        }

        // 全社平均は、とりあえず綺麗に見せるための固定ライン（または簡単な全社平均ロジック）
        $teamAvgHours = [25, 30, 28, 35]; 

        $workTimeData = [
            'labels' => ['第1週', '第2週', '第3週', '第4週'],
            'personal' => $personalHours, // 💡 ここが本物になりました！
            'team_avg' => $teamAvgHours,
        ];

        return Inertia::render('Dashboard', [
            'tasks' => $realTasks,
            'currentStatus' => $currentStatus,
            'workTimeData' => $workTimeData,
            'today' => Carbon::today()->format('Y年m月d日'),
            'notifications' => $pendingLeaves,
        ]);
    }
}
