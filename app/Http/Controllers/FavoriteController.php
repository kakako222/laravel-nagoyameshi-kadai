<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    //会員のお気に入り店舗一覧を表示
    public function index()
    {
        // ログインユーザーのお気に入り店舗を新しい順に取得（ページネーションあり）
        $favorite_restaurants = auth()->user()->favorite_restaurants()
            ->orderBy('restaurant_user.created_at', 'desc') // 作成日時で並び替え
            ->paginate(15); // 1ページあたり15件

        return view('favorites.index', compact('favorite_restaurants'));
    }

    //会員のお気に入りに店舗を追加
    public function store($restaurantId)
    {
        // ログインユーザーを取得
        $user = auth()->user();

        // 既にお気に入りに入っているか確認
        $alreadyFavorited = $user->favorite_restaurants()->where('restaurant_id', $restaurantId)->exists();

        if (!$alreadyFavorited) {
            // お気に入りに店舗を追加
            $user->favorite_restaurants()->attach($restaurantId);
        }

        // 元のページにリダイレクトし、フラッシュメッセージを設定
        return redirect()->back()->with('flash_message', 'お気に入りに追加しました。');
    }

    //  会員のお気に入りから店舗を削除
    public function destroy($restaurantId)
    {
        // ログインユーザーを取得
        $user = auth()->user();

        // お気に入りから店舗を削除
        $user->favorite_restaurants()->detach($restaurantId);

        // 元のページにリダイレクトし、フラッシュメッセージを設定
        return redirect()->back()->with('flash_message', 'お気に入りを解除しました。');
    }
}
