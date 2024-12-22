<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未ログインのユーザーは会員側のトップページにアクセスできる
     *
     * @return void
     */
    public function test_guest_can_access_home_page()
    {
        // 未ログインユーザーでトップページにアクセス
        $response = $this->get('/');

        // アクセスできることを確認
        $response->assertStatus(200);
    }

    /**
     * ログイン済みの一般ユーザーは会員側のトップページにアクセスできる
     *
     * @return void
     */
    public function test_logged_in_user_can_access_home_page()
    {
        // 一般ユーザーを作成してログイン
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password')
        ]);

        $response = $this->actingAs($user)->get('/home');

        // アクセスできることを確認
        $response->assertStatus(200);
    }

    /**
     * ログイン済みの管理者は会員側のトップページにアクセスできない
     *
     * @return void
     */
    public function test_logged_in_admin_cannot_access_home_page()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        // 管理者を作成してログイン
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi')
        ]);

        // 管理者が一般ユーザーのページにアクセスしようとする
        $response = $this->actingAs($admin, 'admin')->get('/home');
        //$admin->save();

        // レスポンス内容を出力して確認
        //dd($response->getContent());

        // アクセスできないことを確認（403 Forbidden）
        $response->assertStatus(403);
    }
}
