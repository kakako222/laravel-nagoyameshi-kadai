<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin; // 管理者モデルをインポート
use App\Models\Restaurant; // 店舗モデルをインポート
use App\Models\Category;
use App\Models\RegularHoliday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;



class RestaurantTest extends TestCase
{
    use RefreshDatabase; // テスト用DBリセット

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者の作成
        $this->admin = Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 一般ユーザーの作成
        $this->user = \App\Models\User::factory()->create();
    }


    /** @test */
    public function test_未ログインのユーザーは管理者側の店舗一覧ページにアクセスできない()
    {
        $response = $this->get(route('admin.restaurants.index'));
        $response->assertRedirect(route('admin.login')); // ログインページへリダイレクト
    }

    /** @test */
    public function test_ログイン済みの一般ユーザーは管理者側の店舗一覧ページにアクセスできない()
    {
        $response = $this->actingAs($this->user)->get(route('admin.restaurants.index'));
        // リダイレクトが発生するかを確認
        $response->assertRedirect(route('admin.login'));  // ログインページへのリダイレクトを確認
    }
    /** @test */
    public function test_ログイン済みの管理者は管理者側の店舗一覧ページにアクセスできる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.restaurants.index'));
        $response->assertStatus(200); // 正常アクセス
    }

    /** @test */
    public function test_未ログインのユーザーは管理者側の店舗詳細ページにアクセスできない()
    {
        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('admin.restaurants.show', $restaurant));
        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function test_ログイン済みの一般ユーザーは管理者側の店舗詳細ページにアクセスできない()
    {
        $restaurant = Restaurant::factory()->create();
        $response = $this->actingAs($this->user)->get(route('admin.restaurants.show', $restaurant));
        // ログインページへのリダイレクトを確認
        $response->assertRedirect(route('admin.login'));  // admin.loginへのリダイレクトを確認
    }

    /** @test */
    public function test_ログイン済みの管理者は管理者側の店舗詳細ページにアクセスできる()
    {
        $restaurant = Restaurant::factory()->create();
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.restaurants.show', $restaurant));
        $response->assertStatus(200);
    }

    /** @test */
    public function test_未ログインのユーザーは店舗を登録できない()
    {
        $data = Restaurant::factory()->make()->toArray();

        $response = $this->post(route('admin.restaurants.store'), $data);
        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function test_ログイン済みの一般ユーザーは店舗を登録できない()
    {
        $data = Restaurant::factory()->make()->toArray();

        // 一般ユーザーでアクセスし、ログインページへのリダイレクトを確認
        $response = $this->actingAs($this->user)->post(route('admin.restaurants.store'), $data);
        //dd(auth()->user());

        // 管理者用のログインページへリダイレクトされることを確認
        $response->assertRedirect(route('admin.login'));  // admin.loginへのリダイレクトを確認
    }

    /** @test */
    public function test_ログイン済みの管理者は店舗を登録できる()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'role' => 'admin',  // adminロールを付与
        ]);

        $data = Restaurant::factory()->make()->toArray();

        // 管理者としてログインし、POSTリクエストを送信
        $response = $this->actingAs($admin, 'admin')->post(route('admin.restaurants.store'), $data);

        // リダイレクトされることを確認
        $response->assertRedirect(route('admin.restaurants.index'));

        // データベースにレストランが追加されたことを確認
        $this->assertDatabaseHas('restaurants', ['name' => $data['name']]);
    }
    /** @test */
    public function test_ログイン済みの管理者は店舗を削除できる()
    {
        // $restaurant を生成する部分
        $restaurant = \App\Models\Restaurant::factory()->create();

        // レストランを削除する処理
        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.restaurants.destroy', $restaurant)); // この行が正しいか確認

        // リダイレクトを確認
        $response->assertRedirect(route('admin.restaurants.index'));

        // データベースから削除されたことを確認
        $this->assertDatabaseMissing('restaurants', ['id' => $restaurant->id]);
    }
    /** @test */
    public function test_ログイン済みの管理者は店舗にカテゴリを設定できる()
    {
        // カテゴリのダミーデータを3つ作成
        $categories = Category::factory(3)->create();

        // 作成したカテゴリのIDを配列として取得
        $categoryIds = $categories->pluck('id')->toArray();

        // 店舗データの作成
        $restaurant_data = Restaurant::factory()->make()->toArray();

        // `category_ids` を追加
        $restaurant_data['category_ids'] = $categoryIds;

        // 管理者としてログインして店舗を登録
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.restaurants.store'), $restaurant_data);

        // リダイレクトされることを確認
        $response->assertRedirect(route('admin.restaurants.index'));

        // データベースにレストランが追加されたことを確認
        $this->assertDatabaseHas('restaurants', ['name' => $restaurant_data['name']]);

        // category_restaurantテーブルにデータがあることを確認
        foreach ($categoryIds as $categoryId) {
            $this->assertDatabaseHas('category_restaurant', [
                'restaurant_id' => Restaurant::where('name', $restaurant_data['name'])->first()->id,
                'category_id' => $categoryId,
            ]);
        }
    }

    /** @test */
    public function test_ログイン済みの管理者は店舗を更新しカテゴリも設定できる()
    {
        // 既存の店舗とカテゴリを作成
        $restaurant = Restaurant::factory()->create([
            'description' => 'テスト用の説明',
            'lowest_price' => 1000,
            'highest_price' => 5000,
            'postal_code' => '1234567',
            'address' => '名古屋市テスト区',
            'opening_time' => '10:00',
            'closing_time' => '22:00',
            'seating_capacity' => 50,
        ]);

        $categories = Category::factory(3)->create();
        $categoryIds = $categories->pluck('id')->toArray();
        // 店舗にカテゴリを設定
        $restaurant->categories()->attach($categoryIds);

        // 更新データの準備
        $new_restaurant_data = [
            'name' => '更新されたレストラン名',
            'description' => '新しい説明', // 必須フィールド
            'lowest_price' => 1500,        // 必須フィールド
            'highest_price' => 6000,       // 必須フィールド
            'postal_code' => '9876543',   // 必須フィールド
            'address' => '名古屋市新しい区', // 必須フィールド
            'opening_time' => '09:00',     // 必須フィールド
            'closing_time' => '23:00',     // 必須フィールド
            'seating_capacity' => 60,      // 必須フィールド
            'category_ids' => $categoryIds,
        ];

        // 管理者としてログインして店舗を更新
        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.restaurants.update', $restaurant), $new_restaurant_data);

        // リダイレクトされることを確認
        $response->assertRedirect(route('admin.restaurants.show', $restaurant));

        // 店舗名が更新されたことを確認
        $this->assertDatabaseHas('restaurants', ['name' => '更新されたレストラン名']);

        // category_restaurantテーブルにデータが存在することを確認
        foreach ($categoryIds as $categoryId) {
            $this->assertDatabaseHas('category_restaurant', [
                'restaurant_id' => $restaurant->id,
                'category_id' => $categoryId,
            ]);
        }
    }

    /** @test */
    public function test_ログイン済みの一般ユーザーは店舗にカテゴリを設定できない()
    {
        // 一般ユーザーとしてログイン
        $user = User::factory()->create();

        // カテゴリのダミーデータを3つ作成
        $categories = Category::factory(3)->create();

        // 作成したカテゴリのIDを配列として取得
        $categoryIds = $categories->pluck('id')->toArray();

        // 店舗データの作成
        $restaurant_data = Restaurant::factory()->make()->toArray();
        $restaurant_data['category_ids'] = $categoryIds;

        // category_idsを削除
        unset($restaurant_data['category_ids']);

        // 一般ユーザーとしてログインして店舗登録リクエストを送信
        $response = $this->actingAs($user)->post(route('admin.restaurants.store'), $restaurant_data);

        // ログインページにリダイレクトされることを確認
        $response->assertRedirect(route('admin.login')); // 一般ユーザーは店舗登録できない
    }
    /** @test */
    public function test_ログイン済みの管理者は店舗の定休日を更新できる()
    {
        // 事前準備：レストランと定休日を作成
        $restaurant = Restaurant::factory()->create([
            'name' => 'レストラン名',
            'description' => 'レストランの説明',
            'lowest_price' => 1000,
            'highest_price' => 5000,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
            'opening_time' => '10:00',
            'closing_time' => '22:00',
            'seating_capacity' => 50
        ]);
        $regularHoliday = RegularHoliday::factory()->create(); // 定休日の作成

        // 定休日をレストランに関連付け
        $restaurant->regular_holidays()->attach($regularHoliday->id);

        // 更新するデータ
        $newData = [
            'regular_holiday_ids' => [$regularHoliday->id] // 更新する定休日ID
        ];

        // 管理者としてリクエストを送信
        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.restaurants.update', $restaurant), $newData);

        // レスポンスを確認
        $response->assertStatus(302); // リダイレクトが期待される場合

        // 定休日が正しく関連付けられていることを確認
        $this->assertDatabaseHas('regular_holiday_restaurant', [
            'restaurant_id' => $restaurant->id,
            'regular_holiday_id' => $regularHoliday->id,
        ]);

        // 更新データの準備（必須フィールドを追加）
        $newRestaurantData = [
            'name' => '更新されたレストラン名',
            'description' => '新しい説明',
            'lowest_price' => 1500,
            'highest_price' => 6000,
            'postal_code' => '9876543',
            'address' => '新しい住所',
            'opening_time' => '09:00',
            'closing_time' => '23:00',
            'seating_capacity' => 60,
            'regular_holiday_ids' => [$regularHoliday->id], // 定休日の更新
        ];

        // 管理者としてログインして店舗を更新
        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.restaurants.update', $restaurant), $newRestaurantData);

        // リダイレクトを確認
        $response->assertRedirect(route('admin.restaurants.show', $restaurant));

        // データベースに変更が保存されたことを確認
        $this->assertDatabaseHas('restaurants', ['name' => '更新されたレストラン名']);
        $this->assertDatabaseHas('restaurants', ['description' => '新しい説明']);
        $this->assertDatabaseHas('restaurants', ['lowest_price' => 1500]);
        $this->assertDatabaseHas('restaurants', ['highest_price' => 6000]);
        $this->assertDatabaseHas('restaurants', ['postal_code' => '9876543']);
        $this->assertDatabaseHas('restaurants', ['address' => '新しい住所']);
        $this->assertDatabaseHas('restaurants', ['opening_time' => '09:00']);
        $this->assertDatabaseHas('restaurants', ['closing_time' => '23:00']);
        $this->assertDatabaseHas('restaurants', ['seating_capacity' => 60]);

        // 定休日が更新されたことを確認
        foreach ($newRestaurantData['regular_holiday_ids'] as $holidayId) {
            $this->assertDatabaseHas('regular_holiday_restaurant', [
                'restaurant_id' => $restaurant->id,
                'regular_holiday_id' => $holidayId,
            ]);
        }
    }
}
