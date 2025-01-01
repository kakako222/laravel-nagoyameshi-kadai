<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RegularHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;


class RestaurantController extends Controller
{
    //////////////////////index////////////////////////
    //一覧
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

    //////////////////////show///////////////////////
    //
    public function show(Restaurant $restaurant)
    {
        return view('admin.restaurants.show', compact('restaurant'));
    }

    //////////////////////create//////////////////////
    //表示
    public function create()
    {
        // categoriesテーブルから全カテゴリを取得
        $categories = Category::all();

        // 定休日のデータを取得
        $regular_holidays = RegularHoliday::all();

        // 店舗登録ページを表示
        return view('admin.restaurants.create', compact('categories', 'regular_holidays'));
    }


    //////////////////////store/////////////////////
    //店舗一覧
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
            //'category_ids' => 'nullable|array', // カテゴリIDの配列
            //'category_ids.*' => 'exists:categories,id',
            //'regular_holiday_ids' => 'array',  // 定休日のIDは配列として受け取る
            //'regular_holiday_ids.*' => 'exists:regular_holidays,id', // 定休日IDが正しいか確認
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
        } else {
            $restaurant->image = "";
        }

        // 店舗情報をデータベースに保存
        $restaurant->save();

        // カテゴリの関連付け（多対多）
        $category_ids = array_filter($request->input('category_ids', []));
        $restaurant->categories()->sync($category_ids);


        // 定休日のID配列を同期
        $regular_holiday_ids = $request->input('regular_holiday_ids');
        $restaurant->regular_holidays()->sync($regular_holiday_ids);

        // フラッシュメッセージとリダイレクト
        return redirect()->route('admin.restaurants.index')
            ->with('flash_message', '店舗を登録しました！');
    }
    ///////////////////////edit/////////////////////////
    //編集
    public function edit(Restaurant $restaurant)
    {

        $categories = Category::all();
        $category_ids = $restaurant->categories->pluck('id')->toArray();

        $regular_holidays = RegularHoliday::all();

        // ビューにデータを渡す
        return view('admin.restaurants.edit', compact('restaurant', 'categories', 'category_ids', 'regular_holidays'));
    }


    //////////////////////update/////////////////////////
    //更新
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
            //'category_ids' => 'nullable|array',  // カテゴリIDの配列（オプション）
            //'category_ids.*' => 'exists:categories,id',
            //'regular_holiday_ids' => 'nullable|array',
            //'regular_holiday_ids.*' => 'exists:regular_holidays,id',
        ]);

        $restaurant->name = $request->input('name');
        $restaurant->description = $request->input('description');
        $restaurant->lowest_price = $request->input('lowest_price');
        $restaurant->highest_price = $request->input('highest_price');
        $restaurant->postal_code = $request->input('postal_code');
        $restaurant->address = $request->input('address');
        $restaurant->opening_time = $request->input('opening_time');
        $restaurant->closing_time = $request->input('closing_time');
        $restaurant->seating_capacity = $request->input('seating_capacity');

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('public/restaurants');
            $restaurant->image = basename($image);
        }
        $restaurant->update();

        $category_ids = array_filter($request->input('category_ids', []));
        $restaurant->categories()->sync($category_ids);

        $regular_holiday_ids = array_filter($request->input('regular_holiday_ids', []));
        $restaurant->regular_holidays()->sync($regular_holiday_ids);

        return redirect()->route('admin.restaurants.show', $restaurant)->with('flash_message', '店舗を編集しました。');
    }


    //////////////////////detroy//////////////////////
    //削除
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
}
