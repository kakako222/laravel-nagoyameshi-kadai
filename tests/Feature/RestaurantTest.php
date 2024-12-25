<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Models\Restaurant;
use App\Models\Category;
use Illuminate\Support\Facades\Log;




class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    //////////index//////////
    /**
     * 未ログインのユーザーは会員側の店舗一覧ページにアクセスできる
     *
     * @return void
     */
    public function test_unauthenticated_user_can_access_restaurant_index_page()
    {
        // 未ログイン状態で店舗一覧ページにアクセス
        $response = $this->get(route('restaurants.index'));

        // ステータスコード200（正常なアクセス）を確認
        $response->assertStatus(200);
    }

    /**
     * ログイン済みの一般ユーザーは会員側の店舗一覧ページにアクセスできる
     *
     * @return void
     */
    public function test_authenticated_user_can_access_restaurant_index_page()
    {
        // 一般ユーザーを作成してログイン
        $user = User::factory()->create();

        // ログイン状態で店舗一覧ページにアクセス
        $response = $this->withoutMiddleware()->actingAs($user)->get(route('restaurants.index'));

        // ステータスコード200（正常なアクセス）を確認
        $response->assertStatus(200);
    }

    /**
     * ログイン済みの管理者は会員側の店舗一覧ページにアクセスできない
     *
     * @return void
     */
    public function test_authenticated_admin_cannot_access_restaurant_index_page()
    {
        // 管理者ユーザーを作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $this->actingAs($admin);

        // 店舗一覧ページにアクセス
        $response = $this->withoutMiddleware()->actingAs($admin, 'admin')->get(route('restaurants.index'));
        $response->assertStatus(302);

        // リダイレクト先を確認
        $response->assertRedirect(route('admin.home'));
    }

    //////////show//////////
    /**
     * 未ログインのユーザーは会員側の店舗詳細ページにアクセスできる
     *
     * @return void
     */
    public function test_unauthenticated_user_can_access_restaurant_show_page()
    {
        // 店舗データを作成
        $restaurant = Restaurant::factory()->create();

        // 未ログイン状態で店舗詳細ページにアクセス
        $response = $this->get(route('restaurants.show', $restaurant));

        // ステータスコード200（正常なアクセス）を確認
        $response->assertStatus(200);
    }
    /**
     * ログイン済みの一般ユーザーは会員側の店舗詳細ページにアクセスできる
     *
     * @return void
     */
    public function test_authenticated_user_can_access_restaurant_show_page()
    {
        // 一般ユーザーを作成してログイン
        $user = User::factory()->create();

        // 店舗データを作成
        $restaurant = Restaurant::factory()->create();

        // ログイン状態で店舗詳細ページにアクセス
        $response = $this->actingAs($user)->get(route('restaurants.show', $restaurant));

        // ステータスコード200（正常なアクセス）を確認
        $response->assertStatus(200);
    }
    /**
     * ログイン済みの管理者は会員側の店舗詳細ページにアクセスできない
     *
     * @return void
     */
    public function test_authenticated_admin_cannot_access_restaurant_show_page()
    {
        // 管理者ユーザーを作成してログイン
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 店舗データを作成
        $restaurant = Restaurant::factory()->create();

        // ログイン状態で管理者が店舗詳細ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.show', $restaurant));

        // ステータスコード302（リダイレクト）を確認
        $response->assertStatus(302);

        // リダイレクト先を確認
        $response->assertRedirect(route('admin.home'));
    }
}
