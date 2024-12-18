<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;  // Gateファサードをインポート

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // モデルとポリシーのマッピング
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // 権限の定義を追加
        Gate::define('admin-access', function ($user) {
            return $user->role === 'admin';  // 'admin'ロールを持つユーザーのみ許可
        });
    }
}
