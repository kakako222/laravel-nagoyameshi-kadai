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
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 管理者ユーザーでログイン
        $response = $this->actingAs($admin, 'admin')->get(route('admin.categories.index'));

        $response->assertStatus(200);
    }

    // 管理者はカテゴリを登録できることをテスト
    public function test_admin_can_create_category()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
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
        /* ダミーデータにnameしかない */
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();

        /*
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.categories.update', $category), [
            'name' => '更新されたカテゴリ',
        ]);*/
        // 必須フィールドも含めて更新リクエストを送信
        $response = $this->actingAs($admin, 'admin')->put(route('admin.categories.update', $category), [
            'name' => '更新されたカテゴリ',
            'postal_code' => '1234567',  // 必須フィールド
            'address' => '新しい住所',    // 必須フィールド
            'representative' => '代表者名', // 必須フィールド
            'establishment_date' => '2024-01-01', // 必須フィールド
            'capital' => '1000000',         // 必須フィールド
            'business' => '新しい事業内容', // 必須フィールド
            'number_of_employees' => '50',  // 必須フィールド
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => '更新されたカテゴリ',
            //'postal_code' => '1234567',  // データベースに更新内容が反映されていることを確認
            //'address' => '新しい住所',
            //'representative' => '代表者名',
            //'establishment_date' => '2024-01-01',
            //'capital' => '1000000',
            //'business' => '新しい事業内容',
            //'number_of_employees' => '50',
        ]);
        /*
        $this->assertDatabaseHas('categories', [
            'name' => '更新されたカテゴリ',
        ]);*/
    }

    // 管理者はカテゴリを削除できることをテスト
    public function test_admin_can_delete_category()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
