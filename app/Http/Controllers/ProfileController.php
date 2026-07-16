<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        // 💡 修正・追加：ログインユーザーが所属する企業（テナント）の設定を取得、なければ自動生成
        $systemSetting = \App\Models\SystemSetting::firstOrCreate(
            ['id' => $request->user()->system_setting_id],
            [
                'company_name' => $request->user()->name . 'の会社', 
                'registration_number' => 'T1234567890123'
            ]
        );

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
            'status' => session('status'),
            'systemSetting' => $systemSetting, // ★ 追加：Vue側に自社マスタのデータをトス
        ]);
    }
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateSystemSettings(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'registration_number' => 'required|string|regex:/^T[0-9]{13}$/', // T+13桁のバリデーション
            'postal_code' => 'nullable|string|max:8',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // 💡 修正：['id' => 1] での固定上書きを廃止し、ログインユーザー自身の所属企業IDで安全に更新
        $setting = \App\Models\SystemSetting::updateOrCreate(
            ['id' => auth()->user()->system_setting_id], 
            $validated
        );

        return back()->with('status', 'system-settings-updated');
    }
}