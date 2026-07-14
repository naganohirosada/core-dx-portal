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
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
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
            'registration_number' => 'required|string|regex:/^T[0-9]{13}$/', // T+13桁の正規表現バリデーション
            'postal_code' => 'nullable|string|max:8',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // 最初のレコードを更新、なければ新規作成
        $setting = \App\Models\SystemSetting::updateOrCreate(
            ['id' => 1], // シングルテナントなので常にID:1で上書き
            $validated
        );

        return back()->with('status', 'system-settings-updated');
    }
}
