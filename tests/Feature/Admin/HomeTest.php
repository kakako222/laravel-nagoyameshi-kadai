<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{

    use RefreshDatabase;

    // 未ログインのユーザーは管理者側のトップページにアクセスできない
    public function test_guest_cannot_access_admin_home()
    {
        $response = $this->get(route('admin.home'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの一般ユーザーは管理者側のトップページにアクセスでない
    public function test_regular_user_cannot_access_admin_home()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.home'));

        $response->assertRedirect(route('admin.login'));
    }

    // ログイン済みの管理者は管理者側のトップページにアクセスできる
    public function test_admin_can_access_admin_home()
    {
        // 既存の admin@example.com を削除
        Admin::where('email', 'admin@example.com')->delete();

        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.home'));

        $response->assertStatus(200);
    }
}
