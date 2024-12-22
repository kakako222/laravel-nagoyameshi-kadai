<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    // 会社概要ページのアクセス権限テスト
    public function test_guest_cannot_access_company_index_page()
    {
        $response = $this->get(route('admin.company.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_regular_user_cannot_access_company_index_page()
    {
        $user = User::factory()->create(); // 一般ユーザー作成
        $this->actingAs($user);

        $response = $this->get(route('admin.company.index'));
        $response->assertRedirect(route('admin.login')); // ゲスト・一般ユーザは管理者ログイン画面へリダイレクト
    }

    public function test_admin_can_access_company_index_page()
    {
        Company::factory()->create();
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['is_admin' => true]);

        // 管理者ユーザーでログイン
        $this->actingAs($admin, 'admin');

        // 会社一覧ページへのリクエスト
        $response = $this->get(route('admin.company.index'));

        // レスポンスステータスが200であることを確認
        $response->assertStatus(200); // 成功
    }

    // 会社概要編集ページのアクセス権限テスト
    public function test_guest_cannot_access_company_edit_page()
    {
        $company = Company::factory()->create();
        $response = $this->get(route('admin.company.edit', $company));
        $response->assertRedirect(route('admin.login')); // ゲスト・一般ユーザは管理者ログイン画面へリダイレクト
    }

    public function test_regular_user_cannot_access_company_edit_page()
    {
        $user = User::factory()->create(); // 一般ユーザー作成
        $this->actingAs($user);

        $company = Company::factory()->create();
        $response = $this->get(route('admin.company.edit', $company));
        $response->assertRedirect(route('admin.login')); // ゲスト・一般ユーザは管理者ログイン画面へリダイレクト
    }

    public function test_admin_can_access_company_edit_page()
    {
        $admin = User::factory()->create(['is_admin' => true]); // 管理者ユーザー作成
        $this->actingAs($admin, 'admin');

        $company = Company::factory()->create();
        $response = $this->get(route('admin.company.edit', $company));
        $response->assertStatus(200); // 成功
    }

    // 会社概要更新テスト
    public function test_admin_can_update_company()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'admin');

        $company = Company::factory()->create();
        $data = [
            'name' => 'Updated Name',
            'postal_code' => '1234567',
            'address' => 'Updated Address',
            'representative' => 'Updated Representative',
            'establishment_date' => 'Updated Date',
            'capital' => 'Updated Capital',
            'business' => 'Updated Business',
            'number_of_employees' => 'Updated Employees',
        ];

        // セッションを使用してPUTリクエストを送信
        $response = $this->put(route('admin.company.update', $company), $data);
        $response->assertRedirect(route('admin.company.index'));
        $this->assertDatabaseHas('companies', $data); // データベースに更新内容が反映されていることを確認
    }

    // バリデーションテスト
    public function test_company_update_validation()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'admin');

        $company = Company::factory()->create();
        $response = $this->put(route('admin.company.update', $company), []);
        $response->assertSessionHasErrors(); // バリデーションエラーが発生することを確認
    }
}
