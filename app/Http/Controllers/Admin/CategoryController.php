<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');  // 管理者認証の確認
    }

    /**
     * カテゴリ一覧ページ
     */
    public function index(Request $request)
    {
        // 検索キーワードを取得
        $keyword = $request->input('keyword', '');

        // 検索条件に応じてカテゴリを取得
        $query = Category::query();
        if (!empty($keyword)) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        // ページネーションを適用
        $categories = $query->paginate(10);

        // 総件数
        $total = $query->count();

        // ビューにデータを渡す
        return view('admin.categories.index', compact('categories', 'keyword', 'total'));
    }

    /**
     * カテゴリ登録機能
     */
    public function store(Request $request)
    {
        // バリデーションの実行
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'カテゴリ名は必須です。',
            'name.max' => 'カテゴリ名は255文字以内で入力してください。',
        ]);

        // カテゴリの登録
        Category::create([
            'name' => $request->input('name'),
        ]);

        // フラッシュメッセージとリダイレクト
        return redirect()
            ->route('admin.categories.index')
            ->with('flash_message', 'カテゴリを登録しました。');
    }

    /**
     * カテゴリ更新機能
     */
    public function update(Request $request, Category $category)
    {
        // バリデーションの実行
        $request->validate([
            'name' => 'required|max:255',
        ]);

        // カテゴリの更新
        $category->update([
            'name' => $request->input('name'),
        ]);

        // フラッシュメッセージとリダイレクト
        return redirect()
            ->route('admin.categories.index')
            ->with('flash_message', 'カテゴリを更新しました。');
    }

    /**
     * カテゴリ削除機能
     */
    public function destroy(Category $category)
    {
        // カテゴリの削除
        $category->delete();

        // フラッシュメッセージとリダイレクト
        return redirect()->route('admin.categories.index')
            ->with('flash_message', 'カテゴリを削除しました。');
    }
}
