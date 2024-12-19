<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    // 管理者用のログインテスト用のヘルパーメソッド
    protected function loginAsAdmin()
    {
        $admin = Admin::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        return $admin;
    }

    // 未ログインのユーザーはカテゴリ一覧ページにアクセスできないことをテスト
    public function test_guest_cannot_access_categories()
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_access_categories()
    {
        $user = User::factory()->create(); // 非管理者ユーザーを作成

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        // リダイレクトが発生し、ログインページに遷移することを確認
        $response->assertRedirect(route('admin.login'));
    }

    // 管理者はカテゴリ一覧ページにアクセスできることをテスト
    public function test_admin_user_can_access_categories()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
    }

    // 管理者はカテゴリを登録できることをテスト
    public function test_admin_can_create_category()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'テストカテゴリ',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'テストカテゴリ',
        ]);
    }

    // 管理者はカテゴリを更新できることをテスト
    public function test_admin_can_update_category()
    {
        $admin = $this->loginAsAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.categories.update', $category), [
            'name' => '更新されたカテゴリ',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => '更新されたカテゴリ',
        ]);
    }

    // 管理者はカテゴリを削除できることをテスト
    public function test_admin_can_delete_category()
    {
        $admin = $this->loginAsAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
