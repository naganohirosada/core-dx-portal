<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * 💡 ログイン認証の実行処理
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // 2. セッションの再生成（固定化攻撃対策）
        $request->session()->regenerate();

        // 3. ログイン成功後、ダッシュボードへリダイレクト
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * 💡 ログアウト処理
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後は、新しく作った製品LP（トップページ）に綺麗に戻します
        return redirect('/');
    }
}
