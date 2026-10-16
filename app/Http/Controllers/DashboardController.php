<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\WorkLog;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Workflow;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\PaidLeave;
use App\Models\User;

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
        // 📊 勤怠打刻（出勤〜退勤 − 休憩）から今月の週別の実労働時間を集計
        // 💡 ----------------------------------------------------
        $now = Carbon::now();

        // 月の何週目か（1〜7日は第1週、8〜14日は第2週、15〜21日は第3週、22日以降は第4週）
        $weekIndex = fn ($date) => min(intdiv(Carbon::parse($date)->day - 1, 7), 3);

        $attendances = Attendance::whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->get();

        $personalHours = [0, 0, 0, 0];
        $companyHours = [0, 0, 0, 0];
        foreach ($attendances as $attendance) {
            $hours = $attendance->workedHours();
            $week = $weekIndex($attendance->date);
            $companyHours[$week] += $hours;
            if ($attendance->user_id === $userId) {
                $personalHours[$week] += $hours;
            }
        }

        // 全社平均 = 自社の全社員の実労働時間合計 ÷ 社員数
        $memberCount = max(User::count(), 1);
        $teamAvgHours = array_map(fn ($h) => round($h / $memberCount, 1), $companyHours);

        $workTimeData = [
            'labels' => ['第1週', '第2週', '第3週', '第4週'],
            'personal' => array_map(fn ($h) => round($h, 1), $personalHours),
            'team_avg' => $teamAvgHours,
        ];

        $realWorkflows = Workflow::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $allNotifications = [];
        foreach ($pendingLeaves as $leave) {
            $allNotifications[] = [
                'id' => $leave->id,
                'id_type' => 'leave',
                'type' => '有給申請',
                'title' => ($leave->user ? $leave->user->name : '不明') . " さんの有給取得",
                'meta' => "期間: {$leave->start_date->format('m/d')} 〜 {$leave->end_date->format('m/d')}",
                'detail' => "理由: " . ($leave->reason ?? '未入力')
            ];
        }
        foreach ($realWorkflows as $wf) {
            $typeLabel = $wf->type === 'expense' ? '経費精算' : '稟議申請';
            $amountLabel = $wf->amount ? "【" . number_format($wf->amount) . "円】" : "";
            $allNotifications[] = [
                'id' => $wf->id,
                'id_type' => 'workflow',
                'type' => $typeLabel,
                'title' => ($wf->user ? $wf->user->name : '不明') . " さんの" . $typeLabel,
                'meta' => "{$amountLabel} {$wf->title}",
                'detail' => "詳細: " . ($wf->description ?? '未入力')
            ];
        }

        return Inertia::render('Dashboard', [
            'tasks' => $realTasks,
            'currentStatus' => $currentStatus,
            'workTimeData' => $workTimeData,
            'today' => Carbon::today()->format('Y年m月d日'),
            'notifications' => $allNotifications,
            'paidLeaveBalance' => Auth::user()->paidLeaveBalance(),
        ]);
    }
}
