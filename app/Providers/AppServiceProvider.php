<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 💡 管理者(admin)またはマネージャー(manager)のみ決裁権限を持つルールを定義
        Gate::define('approve-and-invoice', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });
    }
}
