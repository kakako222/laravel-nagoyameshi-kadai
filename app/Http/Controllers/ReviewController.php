<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // レビュー一覧
    public function index(Restaurant $restaurant)
    {
        $user = Auth::user();
        $reviews = $user->is_premium
            ? $restaurant->reviews()->latest()->paginate(5)
            : $restaurant->reviews()->latest()->take(3)->get();

        return view('reviews.index', compact('restaurant', 'reviews'));
    }

    // レビュー投稿ページ
    public function create(Restaurant $restaurant)
    {
        return view('reviews.create', compact('restaurant'));
    }

    // レビュー投稿機能
    public function store(Request $request, Restaurant $restaurant)
    {
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

        return redirect()->route('reviews.index', $restaurant)
            ->with('flash_message', 'レビューを投稿しました。');
    }

    // レビュー編集ページ
    public function edit(Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        return view('reviews.edit', compact('restaurant', 'review'));
    }

    // レビュー更新機能
    public function update(Request $request, Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        $request->validate([
            'score' => 'required|integer|min:1|max:5',
            'content' => 'required',
        ]);

        $review->update([
            'score' => $request->score,
            'content' => $request->content,
        ]);

        return redirect()->route('reviews.index', $restaurant)
            ->with('flash_message', 'レビューを編集しました。');
    }

    // レビュー削除機能
    public function destroy(Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            return redirect()->route('reviews.index', $restaurant)
                ->with('error_message', '不正なアクセスです。');
        }

        $review->delete();

        return redirect()->route('reviews.index', $restaurant)
            ->with('flash_message', 'レビューを削除しました。');
    }
}
