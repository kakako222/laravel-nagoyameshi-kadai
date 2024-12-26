<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class NotSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 管理者の場合、サブスクリプションのチェックをスキップし、リダイレクト
        if ($user instanceof \App\Models\Admin) {
            return redirect()->route('admin.home'); // 管理者はホームにリダイレクト
        }

        // サブスクリプションに未加入のユーザーがcreateページにアクセスできるようにする
        if (! $user?->subscribed('premium_plan')) {
            // createページへのアクセスは許可
            if ($request->is('subscription/create')) {
                return $next($request); // createページへ遷移
            }

            // その他のページはhomeにリダイレクト
            return redirect()->route('home');
        }

        return $next($request);
    }
}
