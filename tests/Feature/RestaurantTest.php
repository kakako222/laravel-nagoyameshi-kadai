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

        $this->actingAs($admin, 'admin');  // 'admin' ガードでログイン

        // 店舗一覧ページにアクセス
        $response = $this->get(route('restaurants.index'));
        $response->assertStatus(302);

        // リダイレクト先を確認
        $response->assertRedirect(route('admin.home'));
    }
}
