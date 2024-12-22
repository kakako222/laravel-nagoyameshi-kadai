<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TermTest extends TestCase
{
    use RefreshDatabase;

    // 利用規約ページのアクセス権限テスト
    public function test_guest_cannot_access_term_index_page()
    {
        $response = $this->get(route('admin.terms.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_user_cannot_access_term_index_page()
    {
        $user = User::factory()->create(); // 一般ユーザー作成
        $this->actingAs($user);

        $response = $this->get(route('admin.terms.index'));
        $response->assertRedirect(route('admin.login')); // ゲスト・一般ユーザは管理者ログイン画面へリダイレクト
    }

    public function test_admin_can_access_term_index_page()
    {
        $admin = User::factory()->create(['is_admin' => true]); // 管理者ユーザー作成
        $this->actingAs($admin, 'admin');

        /* 規約インスタンス生成 */
        Term::factory()->create([
            'content' => '初期の利用規約内容', // 必要なフィールドを設定
        ]);

        $response = $this->get(route('admin.terms.index'));
        $response->assertStatus(200); // 成功
    }

    // 利用規約編集ページのアクセス権限テスト
    public function test_guest_cannot_access_term_edit_page()
    {
        $term = Term::factory()->create();
        $response = $this->get(route('admin.terms.edit', $term));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_user_cannot_access_term_edit_page()
    {
        $user = User::factory()->create(); // 一般ユーザー作成
        $this->actingAs($user);

        $term = Term::factory()->create();
        $response = $this->get(route('admin.terms.edit', $term));
        $response->assertRedirect(route('admin.login')); // ゲスト・一般ユーザは管理者ログイン画面へリダイレクト
    }

    public function test_admin_can_access_term_edit_page()
    {
        $admin = User::factory()->create(['is_admin' => true]); // 管理者ユーザー作成
        $this->actingAs($admin, 'admin');

        $term = Term::factory()->create();
        $response = $this->get(route('admin.terms.edit', $term));
        $response->assertStatus(200); // 成功
    }

    // 利用規約更新テスト
    public function test_admin_can_update_term()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'admin');

        /* 規約インスタンス生成 */
        $term = Term::factory()->create([
            'content' => '初期の利用規約内容', // 必要なフィールドを設定
        ]);

        $data = [
            'content' => 'Updated Content',
        ];

        $response = $this->put(route('admin.terms.update', $term), $data);
        $response->assertRedirect(route('admin.terms.index'));
        $this->assertDatabaseHas('terms', $data); // データベースに更新内容が反映されていることを確認
    }

    // バリデーションテスト
    public function test_term_update_validation()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'admin');

        $term = Term::factory()->create();
        $response = $this->put(route('admin.terms.update', $term), []);
        $response->assertSessionHasErrors(); // バリデーションエラーが発生することを確認
    }
}
