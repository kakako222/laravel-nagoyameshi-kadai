<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;  // これを追加

class UserTest extends TestCase
{

    use RefreshDatabase;

    //////////index/////////

    //未ログインのユーザーは会員側の会員情報ページにアクセスできない
    public function test_guest_cannot_access_user_index()
    {
        $response = $this->get(route('user.index'));

        $response->assertRedirect(route('login'));
    }

    //ログイン済みの一般ユーザーは会員側の会員情報ページにアクセスできる
    public function test_user_can_access_user_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.index'));

        $response->assertStatus(200);
    }

    //ログイン済みの管理者は会員側の会員情報ページにアクセスできない
    public function test_authenticated_admin_cannot_access_user_member_list_page(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者のログイン
        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi',
        ]);
        $response = $this->actingAs($admin, 'admin')->get(route('user.index'));

        $response->assertRedirect(route('admin.home'));
    }

    //////////edit/////////

    // 未ログインのユーザーは会員側の会員情報編集ページにアクセスできない
    public function test_unauthenticated_user_cannnot_access_user_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->get(route('user.edit', $user));

        $response->assertRedirect(route('login'));
    }

    // ログイン済みの一般ユーザーは会員側の他人の会員情報編集ページにアクセスできない
    public function test_authenticated_regular_user_cannot_access_another_user_edit_page(): void
    {
        $user = User::factory()->create();
        $another_user = User::factory()->create();

        $response = $this->actingAs($another_user)->get(route('user.edit', ['user' => $user->id]));

        $response = $this->actingAs($user)->get(route('user.edit', $another_user));
        $response->assertRedirect(route('user.index')); // 自分の会員ページにリダイレク
    }

    // ログイン済みの一般ユーザーは会員側の自身の会員情報編集ページにアクセスできる
    public function test_authenticated_regular_user_can_access_user_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.edit', ['user' => $user->id]));

        // リダイレクト先を確認
        //dd($response->headers->get('Location'), $response->getContent());

        $response->assertStatus(200);
    }

    // ログイン済みの管理者は会員側の会員情報編集ページにアクセスできない
    public function test_authenticated_admin_cannot_access_user_edit_page(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi',
        ]);

        $user = User::factory()->create();

        // 管理者のログイン
        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi',
        ]);
        $response = $this->actingAs($admin, 'admin')->get(route('user.edit', $user));

        $response->assertRedirect(route('admin.home'));
    }

    //////////update/////////

    // 未ログインのユーザーは会員情報を更新できない
    public function test_unauthenticated_user_cannot_update_user_profile(): void
    {
        $old_user = User::factory()->create();

        $new_user_data = [
            'name' => 'テスト更新',
            'kana' => 'テストコウシン',
            'email' => 'test.update@example.com',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'phone_number' => '0123456789',
            'birthday' => '20150319',
            'occupation' => 'テスト更新',
        ];

        $response = $this->put(route('user.update', $old_user), $new_user_data);

        $this->assertDatabaseMissing('users', $new_user_data);
        $response->assertRedirect(route('login'));
    }

    // ログイン済みの一般ユーザーは他人の会員情報を更新できない
    public function test_authenticated_regular_user_cannot_update_other_user_profile(): void
    {
        $user = User::factory()->create();
        $old_other_user = User::factory()->create();

        $new_other_user_data = [
            'name' => 'テスト更新',
            'kana' => 'テストコウシン',
            'email' => 'test.update@example.com',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'phone_number' => '0123456789',
            'birthday' => '20150319',
            'occupation' => 'テスト更新'
        ];

        $response = $this->actingAs($user)->put(route('user.update', $old_other_user), $new_other_user_data);

        $this->assertDatabaseMissing('users', $new_other_user_data);
        $response->assertRedirect(route('user.index'));
    }

    // ログイン済みの一般ユーザーは自身の会員情報を更新できる
    public function test_authenticated_regular_user_can_update_own_profile(): void
    {
        $old_user = User::factory()->create();

        $new_user_data = [
            'name' => 'テスト更新',
            'kana' => 'テストコウシン',
            'email' => 'test.update@example.com',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'phone_number' => '0123456789',
            'birthday' => '20150319',
            'occupation' => 'テスト更新'
        ];

        $response = $this->actingAs($old_user)->put(route('user.update', $old_user), $new_user_data);

        // データベースに新しい情報が保存されたか確認
        $this->assertDatabaseHas('users', $new_user_data);

        $response->assertRedirect(route('user.index'));
    }

    // ログイン済みの管理者は会員情報を更新できない
    public function test_authenticated_admin_cannot_update_user_profile(): void
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi',
        ]);

        $old_user = User::factory()->create();

        $new_user_data = [
            'name' => 'テスト更新',
            'kana' => 'テストコウシン',
            'email' => 'test.update@example.com',
            'postal_code' => '1234567',
            'address' => 'テスト更新',
            'phone_number' => '0123456789',
            'birthday' => '20150319',
            'occupation' => 'テスト更新'
        ];

        $response = $this->actingAs($admin)->put(route('user.update', $old_user), $new_user_data);
        $this->assertDatabaseMissing('users', $new_user_data);
        $response->assertRedirect(route('admin.home'));
    }
}
