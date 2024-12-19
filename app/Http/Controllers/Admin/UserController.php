<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
    // ユーザー登録処理
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 新しいユーザーを作成
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        // パスワードをBcryptでハッシュ化して保存
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'ユーザーが登録されました');
    }
}
