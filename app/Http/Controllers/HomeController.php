<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Category;



class HomeController extends Controller
{
    // トップページの表示
    public function index()
    {
        // 管理者がアクセスしていた場合は、管理者用ページにリダイレクト
        if (auth()->guard('admin')->check()) {
            return redirect()->route('admin.home'); // 管理者用トップページ
        }

        // 評価が高いレストラン（評価順で並べ替えて6件取得）
        $highly_rated_restaurants = Restaurant::take(6)->get();

        // すべてのカテゴリデータ
        $categories = Category::all();

        // 新着レストラン（作成日時が新しい順で6件取得）
        $new_restaurants = Restaurant::orderBy('created_at', 'desc')->take(6)->get();

        // ビューにデータを渡して表示
        return view('home', compact('highly_rated_restaurants', 'categories', 'new_restaurants'));
    }
}
