<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\RegularHoliday;
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
        $total = $restaurantsQuery->count(); // 検索結果の総数を取得

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
        // categoriesテーブルから全カテゴリを取得
        $categories = Category::all();

        // 定休日のデータを取得
        $regular_holidays = RegularHoliday::all();

        // 店舗登録ページを表示
        return view('admin.restaurants.create', compact('categories', 'regular_holidays'));
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
            'category_ids' => 'nullable|array', // カテゴリIDの配列
            'category_ids.*' => 'exists:categories,id',
            'regular_holiday_ids' => 'array',  // 定休日のIDは配列として受け取る
            'regular_holiday_ids.*' => 'exists:regular_holidays,id', // 定休日IDが正しいか確認
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
            $restaurant->image = basename($imagePath);  // 画像パスを保存
        }

        // 店舗情報をデータベースに保存
        $restaurant->save();

        // カテゴリの関連付け（多対多）
        $category_ids = $validated['category_ids'] ?? [];
        $restaurant->categories()->sync($category_ids);  // カテゴリの関連付け

        // 定休日のID配列を同期
        if (isset($validated['regular_holiday_ids'])) {
            $restaurant->regular_holidays()->sync($validated['regular_holiday_ids']);
        }

        // フラッシュメッセージとリダイレクト
        return redirect()->route('admin.restaurants.index')
            ->with('flash_message', '店舗を登録しました！');
    }

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
            'category_ids' => 'nullable|array',  // カテゴリIDの配列（オプション）
            'category_ids.*' => 'exists:categories,id',
            'regular_holiday_ids' => 'nullable|array',
            'regular_holiday_ids.*' => 'exists:regular_holidays,id',
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

        // 店舗データの更新
        $restaurant->update($validated);

        // カテゴリの関連付けを同期
        if (isset($validated['category_ids'])) {
            $restaurant->categories()->sync($validated['category_ids']);
        }

        // 定休日の同期
        if (isset($validated['regular_holiday_ids'])) {
            $restaurant->regular_holidays()->sync($validated['regular_holiday_ids']);
        }

        // フラッシュメッセージとリダイレクト
        return redirect()->route('admin.restaurants.show', $restaurant)
            ->with('flash_message', '店舗を編集しました。');
    }



    /**
     * 店舗削除処理
     */
    public function destroy(Restaurant $restaurant)
    {
        // 画像ファイルが存在する場合、削除（任意）
        $imagePath = 'public/restaurants/' . $restaurant->image;
        if ($restaurant->image && Storage::exists($imagePath)) {
            Storage::delete($imagePath);
        }


        // 店舗データの削除
        $restaurant->delete();

        // フラッシュメッセージを設定してリダイレクト
        return redirect()->route('admin.restaurants.index')
            ->with('flash_message', '店舗を削除しました。');
    }
    /**
     * 店舗編集ページ
     */
    public function edit($id)
    {
        // 編集対象の店舗を取得
        $restaurant = Restaurant::findOrFail($id);

        // 店舗に関連するカテゴリのIDの配列を取得
        $category_ids = $restaurant->categories->pluck('id')->toArray();

        // 全てのカテゴリを取得
        $categories = Category::all();

        // 定休日のデータを取得
        $regular_holidays = RegularHoliday::all();

        // 店舗が選択している定休日のIDを取得
        $selected_regular_holidays = $restaurant->regular_holidays()->pluck('id')->toArray();

        // ビューにデータを渡す
        return view('admin.restaurants.edit', compact('restaurant', 'categories', 'category_ids', 'regular_holidays', 'selected_regular_holidays'));
    }
}
