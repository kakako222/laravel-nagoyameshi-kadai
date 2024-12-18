<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    /**
     * 店舗一覧ページ
     */
    public function index(Request $request)
    {
        // 検索キーワードの取得
        $keyword = $request->input('keyword', '');

        // 店舗の検索条件（部分一致検索）
        $restaurantsQuery = Restaurant::query();

        if ($keyword) {
            // 店舗名で部分一致検索
            $restaurantsQuery->where('name', 'like', '%' . $keyword . '%');
        }

        // ページネーション
        $restaurants = $restaurantsQuery->paginate(10);
        $total = Restaurant::count(); // レストランの総件数

        // ビューにデータを渡す
        return view('admin.restaurants.index', compact('restaurants', 'keyword', 'total'));
    }

    /**
     * 店舗詳細ページ
     */
    public function show(Restaurant $restaurant)
    {
        return view('admin.restaurants.show', compact('restaurant'));
    }

    /**
     * 店舗登録ページ表示
     */
    public function create()
    {
        return view('admin.restaurants.create');
    }

    /**
     * 店舗登録処理
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif,svg,webp|max:2048',
            'description' => 'required',
            'lowest_price' => 'required|numeric|min:0|lte:highest_price',
            'highest_price' => 'required|numeric|min:0|gte:lowest_price',
            'postal_code' => 'required|numeric|digits:7',
            'address' => 'required',
            'opening_time' => 'required|before:closing_time',
            'closing_time' => 'required|after:opening_time',
            'seating_capacity' => 'required|numeric|min:0',
        ]);

        // 店舗データの作成
        $restaurant = new Restaurant();
        $restaurant->name = $validated['name'];
        $restaurant->description = $validated['description'];
        $restaurant->lowest_price = $validated['lowest_price'];
        $restaurant->highest_price = $validated['highest_price'];
        $restaurant->postal_code = $validated['postal_code'];
        $restaurant->address = $validated['address'];
        $restaurant->opening_time = $validated['opening_time'];
        $restaurant->closing_time = $validated['closing_time'];
        $restaurant->seating_capacity = $validated['seating_capacity'];

        // 画像アップロード処理
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/restaurants');
            $validated['image'] = basename($imagePath);

            // 古い画像の削除
            if ($restaurant->image && Storage::exists('public/restaurants/' . $restaurant->image)) {
                Storage::delete('public/restaurants/' . $restaurant->image);
            }
        } else {
            // 画像がない場合は、元の画像名を保持
            $validated['image'] = $restaurant->image;
        }

        // データを更新
        $restaurant->update($validated);

        // フラッシュメッセージとリダイレクト
        return redirect()
            ->route('admin.restaurants.show', $restaurant)
            ->with('flash_message', '店舗を編集しました。');
    }

    /**
     * 店舗編集ページ表示
     */
    public function edit(Restaurant $restaurant)
    {
        return view('admin.restaurants.edit', compact('restaurant'));
    }

    /**
     * 店舗更新処理
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        // バリデーションルール
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,bmp,gif,svg,webp|max:2048',
            'description' => 'required|string',
            'lowest_price' => 'required|integer|min:0|lte:highest_price',
            'highest_price' => 'required|integer|min:0|gte:lowest_price',
            'postal_code' => 'required|digits:7',
            'address' => 'required|string|max:255',
            'opening_time' => 'required|date_format:H:i|before:closing_time',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'seating_capacity' => 'required|integer|min:0',
        ]);

        // 画像アップロード処理
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/restaurants');
            $validated['image'] = basename($imagePath);

            // 古い画像の削除
            if ($restaurant->image && Storage::exists('public/restaurants/' . $restaurant->image)) {
                Storage::delete('public/restaurants/' . $restaurant->image);
            }
        }

        // データを更新
        $restaurant->update($validated);

        // フラッシュメッセージとリダイレクト
        return redirect()
            ->route('admin.restaurants.show', $restaurant)
            ->with('flash_message', '店舗を編集しました。');
    }

    /**
     * 店舗削除処理
     */
    public function destroy(Restaurant $restaurant)
    {
        // 画像ファイルが存在する場合、削除（任意）
        if ($restaurant->image && Storage::exists('public/restaurants/' . $restaurant->image)) {
            Storage::delete('public/restaurants/' . $restaurant->image);
        }

        // 店舗データの削除
        $restaurant->delete();

        // フラッシュメッセージを設定してリダイレクト
        return redirect()->route('admin.restaurants.index')
            ->with('flash_message', '店舗を削除しました。');
    }
}
