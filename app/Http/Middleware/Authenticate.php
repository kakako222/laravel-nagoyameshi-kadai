<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('admin/*') && !Auth::guard('admin')->check()) {
            return route('admin.login'); // 管理者用のログインページ
        }
        // デフォルトは一般ユーザー用のログインページ
        return route('login');
    }
}
