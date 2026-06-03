<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\PaidLeave;

class DashboardController extends Controller
{
    public function index()
    {
        $realTasks = Task::whereIn('status', ['todo', 'doing'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $currentStatus = $todayAttendance ? $todayAttendance->status : '未出勤';

        // 💡 既存のモックを廃止し、本物の「承認待ち(pending)」の有給申請をユーザー情報付きで取得
        $pendingLeaves = PaidLeave::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $workTimeData = [
            'labels' => ['第1週', '第2週', '第3週', '第4週'],
            'personal' => [35, 42, 38, 40],
            'team_avg' => [38, 39, 41, 38],
        ];

        return Inertia::render('Dashboard', [
            'tasks' => $realTasks,
            'currentStatus' => $currentStatus,
            'workTimeData' => $workTimeData,
            'today' => Carbon::today()->format('Y年m月d日'),
            // 💡 承認待ちデータをVueにトス！
            'notifications' => $pendingLeaves, 
        ]);
    }
}
