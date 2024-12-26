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

        // 管理者の場合、サブスクリプションのチェックをスキップ
        if ($user instanceof \App\Models\Admin) {
            return $next($request); // 管理者はそのまま処理を続行
        }

        // ユーザーがサブスクリプションを持っていない場合
        if (! $user?->subscribed('premium_plan')) {
            if ($request->is('subscription/create')) {
                return redirect()->route('subscription.create');
            }

            if ($request->is('subscription/store')) {
                return redirect()->route('subscription.create');
            }

            return redirect()->route('home'); // デフォルトはhomeにリダイレクト
        }

        return $next($request);
    }
}
