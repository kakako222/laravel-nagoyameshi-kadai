<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        //セッションを再生成
        $request->session()->regenerate();

        //ログイン成功後、フラッシュメッセージをセッションに保存
        return redirect()->intended(RouteServiceProvider::HOME)->with('flash_message', 'ログインしました。');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        //セッションの無効化とトークンの再生成
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        //ログアウト後、フラッシュメッセージをセッションに保存
        return redirect('/')->with('flash_message', 'ログアウトしました。');
    }
}
