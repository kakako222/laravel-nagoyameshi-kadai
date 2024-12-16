<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
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

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // パスワードのバリデーション
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'], // 現在のパスワードを確認
        ], [
            'password.current_password' => 'パスワードが間違っています。',
        ]);

        // ユーザーの削除処理
        $user = $request->user();

        Auth::logout();  // ログアウト

        $user->delete();  // ユーザー削除

        // セッションの無効化と再生成
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');  // ホームへリダイレクト
    }
}
