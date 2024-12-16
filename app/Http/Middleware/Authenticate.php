<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('admin/*')) {
            // 管理者専用ページに認証なしでアクセスした場合、403を返す
            abort(403, 'Access denied');
        }
        return $request->expectsJson() ? null : route('login');
    }
}
