<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin; // 管理者モデルをインポート
use App\Models\Restaurant; // 店舗モデルをインポート
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
}
