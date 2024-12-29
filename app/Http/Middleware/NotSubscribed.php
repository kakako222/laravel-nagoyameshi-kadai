<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Cashier\Billable;
use Illuminate\Support\Facades\Auth;

class NotSubscribed
{
    /**有料プランに未登録
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        dd('fuga');
        $user = Auth::user(); // ユーザー情報を取得
        // 未ログインの場合、会員登録ページにリダイレクト
        if (!$user) {
            return redirect()->route('login');
        }

        // 管理者はアクセスできないようにする
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.home');
        }

        // すでに有料会員のユーザーは、編集ページにリダイレクト

        if ($user->subscribed('premium_plan')) {
            return redirect()->route('subscription.edit');
        }

        return $next($request);
    }
}
