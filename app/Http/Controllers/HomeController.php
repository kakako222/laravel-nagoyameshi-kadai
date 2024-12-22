<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    // トップページの表示
    public function index()
    {
        //管理者がアクセスしていた場合は403を返す
        if (auth()->guard('admin')->check()) {
            dd('管理者がアクセスしている');
            abort(403, 'Access denied');
        }

        // 評価が高いレストラン（現時点では並べ替えず、take()メソッドで6件取得）
        $highly_rated_restaurants = Restaurant::take(6)->get();

        // すべてのカテゴリデータ
        $categories = Category::all();

        // 新着レストラン（作成日時が新しい順で6件取得）
        $new_restaurants = Restaurant::orderBy('created_at', 'desc')->take(6)->get();

        // ビューにデータを渡して表示
        return view('home', compact('highly_rated_restaurants', 'categories', 'new_restaurants'));

        //一般ユーザのトップページ
        //return view('home');
    }
}
