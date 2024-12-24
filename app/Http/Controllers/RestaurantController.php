<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    /**
     * 会員向けの店舗一覧を表示する。
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // ログインしているユーザーが管理者の場合
        if (Auth::guard('admin')->check()) {
            // 管理者が店舗一覧ページにアクセスしようとした場合、admin.homeにリダイレクト
            return redirect()->route('admin.home');
        }

        // ログインしているユーザーが会員の場合、店舗一覧を表示
        Log::info('Authenticated user:', ['user' => auth()->user()]);

        // 並べ替え用の$sorts配列
        $sorts = [
            '掲載日が新しい順' => 'created_at desc',
            '価格が安い順' => 'lowest_price asc',
        ];

        // 初期設定として並べ替えのカラムと順番を'created_at desc'に設定
        $sorted = 'created_at desc';
        $sort_query = [];

        // セレクトボックスから並べ替えの値が送信されてきた場合
        if ($request->has('select_sort')) {
            $slices = explode(' ', $request->input('select_sort'));
            $sort_query[$slices[0]] = $slices[1];
            $sorted = $request->input('select_sort');
        }

        // クエリビルダの初期設定
        $restaurants = Restaurant::query();
        $keyword = null; // ここで初期化

        // 検索ボックスのキーワード（$keyword）がある場合
        if ($request->has('keyword')) {
            $keyword = $request->input('keyword');
            $restaurants = $restaurants->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%")
                    ->orWhereHas('categories', function ($query) use ($keyword) {
                        $query->where('categories.name', 'like', "%{$keyword}%");
                    });
            });
        }

        // カテゴリID（$category_id）が選択されている場合
        $category_id = null; // ここで初期化
        if ($request->has('category_id')) {
            $category_id = $request->input('category_id');
            $restaurants = $restaurants->whereHas('categories', function ($query) use ($category_id) {
                $query->where('categories.id', $category_id);
            });
        }

        // 予算（$price）が選択されている場合
        $price = null; // ここで初期化
        if ($request->has('price')) {
            $price = $request->input('price');
            $restaurants = $restaurants->where('lowest_price', '<=', $price);
        }

        // 並べ替え処理（Kyslik/column-sortableのsortable()メソッドを使う）
        $restaurants = $restaurants->sortable($sort_query)->orderBy('created_at', 'desc');

        // ページネーション（1ページあたり15件）
        $restaurants = $restaurants->paginate(15);

        // カテゴリ一覧を取得
        $categories = Category::all();

        // 取得したデータの総数
        $total = $restaurants->total();

        // ビューにデータを渡して表示
        return view('restaurants.index', compact('restaurants', 'keyword', 'category_id', 'price', 'sorts', 'sorted', 'categories', 'total'));
    }
}
