<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

/**
 * 社員アカウント管理（自社の社員の追加・権限変更・有給付与日数の設定）。
 * 社員数は契約上の上限（system_settings.max_users）までに制限する。
 */
class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-users');

        return Inertia::render('Users/Index', [
            'users' => User::orderBy('id')->get(['id', 'name', 'email', 'role', 'paid_leave_days']),
            'maxUsers' => $this->maxUsers($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-users');

        $maxUsers = $this->maxUsers($request->user());
        if (User::count() >= $maxUsers) {
            return back()->withErrors(['email' => "契約上のユーザー数上限（{$maxUsers}名）に達しているため、追加できません。"]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', Rules\Password::defaults()],
            'role' => 'required|in:admin,manager,general',
            'paid_leave_days' => 'required|integer|min:0|max:40',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        // 会社IDは BelongsToTenant により作成時に自動で付与される
        $user->forceFill([
            'role' => $validated['role'],
            'paid_leave_days' => $validated['paid_leave_days'],
            'email_verified_at' => now(),
        ])->save();

        return back();
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'role' => 'required|in:admin,manager,general',
            'paid_leave_days' => 'required|integer|min:0|max:40',
        ]);

        // 自分自身の管理者権限は外せない（管理者不在を防ぐ）
        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => '自分自身の管理者権限は変更できません。']);
        }

        $user->forceFill($validated)->save();

        return back();
    }

    private function maxUsers(User $user): int
    {
        return (int) (SystemSetting::find($user->system_setting_id)?->max_users ?? 10);
    }
}
