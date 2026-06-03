<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PaidLeaveController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // 🛠️ プロフィール関連ルート（ここが消えていたのが原因です）
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 💼 顧客管理（画面分割版）
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::patch('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');

    // 💡 郵便番号検索APIルート
    Route::get('/postal-code/{code}', [CompanyController::class, 'searchPostalCode'])->name('postal-code.search');

    // 📂 案件管理（3画面分割版）
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');

    // 🗓️ スケジュール管理
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // 📋 カンバンタスク管理
    Route::get('/projects/{project}/tasks', [TaskController::class, 'board'])->name('projects.tasks.board');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status.update');

    // ⏰ 勤怠管理の打刻ルート
    Route::post('/attendance/punch', [AttendanceController::class, 'punch'])->name('attendance.punch');

    // 🌴 有給休暇管理ルート
    Route::post('/paid-leaves', [PaidLeaveController::class, 'store'])->name('paid-leaves.store');
    Route::patch('/paid-leaves/{paidLeave}/approve', [PaidLeaveController::class, 'approve'])->name('paid-leaves.approve');
});

require __DIR__.'/auth.php';
