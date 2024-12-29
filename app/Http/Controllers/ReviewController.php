<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;
use App\Models\User;

class ReviewController extends Controller
{
    public function index(Request $request, Restaurant $restaurant)
    {
        // ログインしているユーザーが管理者の場合
        if (Auth::guard('admin')->check()) {
            // 管理者が店舗一覧ページにアクセスしようとした場合、admin.homeにリダイレクト
            return redirect()->route('admin.home');
        }

        // ログインしていない場合、ログインページへリダイレクト
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // ソート条件の設定
        $sorts = [
            '掲載日が新しい順' => 'created_at desc',
        ];

        // ソートの処理
        $sorted = $this->getSortedQuery($request);

        // レビューの取得処理
        $reviews = $this->getReviews($request, $restaurant, $sorted);

        // ビューにデータを渡す
        return view('reviews.index', compact('restaurant', 'reviews'));
    }
    /**
     * ソートクエリを取得するメソッド
     */
    private function getSortedQuery(Request $request)
    {
        // ソート条件が指定されていれば、それを適用
        if ($request->has('select_sort')) {
            return $request->input('select_sort');
        }

        // デフォルトのソート
        return 'created_at desc';
    }

    /**
     * レビューの取得処理
     */
    private function getReviews(Request $request, Restaurant $restaurant, $sorted)
    {
        // 有料会員かどうかでレビューの取得方法を切り替え
        if (! $request->user()->subscribed('premium_plan')) {
            // 無料会員は最新の3件のみ
            return Review::where('restaurant_id', $restaurant->id)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
        }

        // 有料会員の場合は全てのレビューを取得（ソートを適用）
        $reviews = Review::whereHas('restaurant', function ($query) use ($restaurant) {
            $query->where('restaurants.id', $restaurant->id);
        });
        $reviews = $reviews->orderByRaw($sorted);

        // ページネーション
        return $reviews->paginate(5);
    }


    // レビュー投稿ページ
    public function create(Request $request, Restaurant $restaurant)
    {
        // ログインしていない場合、ログインページへリダイレクト
        if (! $request->user()) {
            return redirect()->route('login');
        }
        // ログインしているユーザーが管理者の場合
        if ($request->user()->is_admin) {
            // 管理者がアクセスしてきた場合、admin.homeにリダイレクト
            return redirect()->route('admin.home');
        }

        // 有料会員かどうかでレビューの取得方法を切り替え
        if (!$request->user()->subscribed('premium_plan')) {
            // 有料会員でない場合、サブスクリプション登録ページへリダイレクト
            return redirect()->route('subscription.create');
        }


        return view('reviews.create', compact('restaurant'));
    }

    // レビュー投稿機能
    public function store(Request $request, Restaurant $restaurant)
    {
        //管理者のチェック
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.home');
        }

        $request->validate([
            'score' => 'required|integer|min:1|max:5',
            'content' => 'required',
        ]);

        Review::create([
            'score' => $request->score,
            'content' => $request->content,
            'restaurant_id' => $restaurant->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを投稿しました。');
    }

    // レビュー編集ページ
    public function edit(Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        // 管理者がアクセスしてきた場合、admin.homeにリダイレクト
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.home');
        }
        return view('reviews.edit', compact('restaurant', 'review'));
    }

    // レビュー更新機能
    public function update(Request $request, Restaurant $restaurant, Review $review)
    {
        // ユーザーが自分のレビューを更新しているか確認
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        // 管理者がアクセスしてきた場合、admin.homeにリダイレクト
        if (Auth::user()->is_admin) {
            return redirect()->route('admin.home');
        }

        // バリデーション
        $request->validate([
            'score' => 'required|integer|min:1|max:5',
            'content' => 'required',
        ]);

        // レビューの更新
        $review->update([
            'score' => $request->score,
            'content' => $request->content,
        ]);

        return redirect()->route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを編集しました。');
    }


    // レビュー削除機能
    public function destroy(Restaurant $restaurant, Review $review)
    {
        // ユーザーが自分のレビューを削除しているか確認
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('restaurants.reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        // 管理者がアクセスしてきた場合、admin.homeにリダイレクト
        if (Auth::user()->is_admin) {
            return redirect()->route('admin.home');
        }

        // レビューの削除
        $review->delete();

        // レビュー削除後、レビュー一覧ページにリダイレクト
        return redirect()->route('restaurants.reviews.index', ['restaurant' => $restaurant])
            ->with('flash_message', 'レビューを削除しました。');
    }
}
