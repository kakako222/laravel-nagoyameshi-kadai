<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // 会員情報ページ（自分の情報のみ）
    public function index()
    {
        // 管理者がアクセスした場合は、管理者用ホームへリダイレクト
        if (auth()->guard('admin')->check()) {
            return redirect()->route('admin.home');
        }

        if (!Auth::check()) {
            return to_route('login');
        }

        $user = Auth::user();

        // ユーザー情報をビューに渡す
        return view('user.index', compact('user'));
    }

    // 会員情報編集ページ
    public function edit(User $user)
    {
        // 管理者がアクセスした場合は、管理者用ホームへリダイレクト
        if (auth()->guard('admin')->check()) {
            return redirect()->route('admin.home')->with('error_message', '管理者は他のユーザーの情報を編集できません。');
        }

        // 他のユーザーの情報にアクセスできないようにリダイレクト
        if ($user->id !== Auth::id()) {
            return redirect()->route('user.index')->with('error_message', '不正なアクセスです。');
        }

        // 編集フォームを表示
        return view('user.edit', compact('user'));
    }

    // 会員情報更新機能(updateアクション)
    public function update(Request $request, User $user)
    {
        $authUser_id = Auth::id();

        // 管理者がアクセスした場合は、管理者用ホームへリダイレクト
        if (auth()->guard('admin')->check()) {
            return redirect()->route('admin.home');
        }

        if ($user->id !== $authUser_id) {
            return to_route('user.index')->with('error_message', '不正なアクセスです。');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kana' => ['required', 'string', 'regex:/\A[ァ-ヴー\s]+\z/u', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'postal_code' => ['required', 'digits:7'],
            'address' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'digits_between:10,11'],
            'birthday' => ['nullable', 'digits:8'],
            'occupation' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return to_route('user.index')->with('flash_message', '会員情報を編集しました。');
    }
}
