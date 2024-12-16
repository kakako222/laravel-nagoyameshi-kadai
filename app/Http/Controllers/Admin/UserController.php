<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 会員一覧ページを表示
     */
    public function index(Request $request)
    {
        // 検索ボックスに入力されたキーワードを取得
        $keyword = $request->input('keyword', '');

        // 検索があれば、氏名またはフリガナで部分一致検索
        $users = User::query()
            ->where('name', 'like', "%{$keyword}%")
            ->orWhere('kana', 'like', "%{$keyword}%")
            ->paginate(10); // ページネーションを適用

        // 会員一覧ページに渡す変数
        $total = $users->total(); // 取得したデータの総数

        return view('admin.users.index', compact('users', 'keyword', 'total'));
    }

    /**
     * 会員詳細ページを表示
     */
    public function show(User $user)
    {
        // 会員詳細ページに渡す変数
        return view('admin.users.show', compact('user'));
    }
}
