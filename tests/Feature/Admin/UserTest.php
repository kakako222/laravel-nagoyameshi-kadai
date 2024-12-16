<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未ログインのユーザーは管理者側の会員一覧ページにアクセスできない
     */
    public function test_guest_user_cannot_access_admin_users_index()
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('admin.login')); // ログインページへのリダイレクトを確認
    }

    /**
     * ログイン済みの一般ユーザーは管理者側の会員一覧ページにアクセスできない
     */
    public function test_regular_user_cannot_access_admin_users_index()
    {
        // 一般ユーザーを作成してログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.users.index'));

        // リダイレクトされるべきなので 302 を確認
        $response->assertStatus(302);  // リダイレクトされることを確認

        // ログインページへのリダイレクトを確認
        $response->assertRedirect(route('admin.login'));
    }

    /**
     * ログイン済みの管理者は管理者側の会員一覧ページにアクセスできる
     */
    public function test_admin_user_can_access_admin_users_index()
    {
        // 管理者ユーザーを作成してログイン
        $admin = User::factory()->create(['is_admin' => true]); // is_adminカラムで管理者フラグを確認
        $this->actingAs($admin, 'admin'); // 管理者としてログイン

        // 会員一覧ページへのアクセスを確認
        $response = $this->get(route('admin.users.index'));

        $response->assertOk(); // 期待されるステータスコード 200
    }


    /**
     * 未ログインのユーザーは管理者側の会員詳細ページにアクセスできない
     */
    public function test_guest_user_cannot_access_admin_users_show()
    {
        $user = User::factory()->create(); // 会員データを作成

        $response = $this->get(route('admin.users.show', $user));

        $response->assertRedirect(route('admin.login')); // ログインページへのリダイレクトを確認
    }

    /**
     * ログイン済みの一般ユーザーは管理者側の会員詳細ページにアクセスできない
     */
    public function test_regular_user_cannot_access_admin_users_show()
    {
        // 一般ユーザーを作成してログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        $targetUser = User::factory()->create(); // 会員データを作成

        $response = $this->get(route('admin.users.show', $targetUser));

        // ログインページへのリダイレクトが期待される
        $response->assertRedirect(route('admin.login')); // ログインページへのリダイレクトを確認
    }

    /**
     * ログイン済みの管理者は管理者側の会員詳細ページにアクセスできる
     */
    public function test_admin_user_can_access_admin_users_show()
    {
        // 管理者ユーザーを作成してログイン
        $admin = User::factory()->create(['is_admin' => true]); // is_adminカラムで管理者フラグを確認
        $this->actingAs($admin, 'admin');

        $targetUser = User::factory()->create(); // 会員データを作成

        $response = $this->get(route('admin.users.show', $targetUser));

        $response->assertOk(); // アクセス成功（200 OK）
    }
}
