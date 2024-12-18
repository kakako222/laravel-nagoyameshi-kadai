<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // 新規ユーザーの登録
        $response = $this->post('/register', [
            'name' => 'Test User',
            'kana' => 'テスト ユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'postal_code' => '0000000',
            'address' => 'テスト',
            'phone_number' => '00000000000',
        ]);

        // ユーザーが認証されていることを確認
        $this->assertAuthenticated();

        // ユーザーが作成されていることを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // 登録後にメール確認ページへリダイレクトされることを確認
        $response->assertRedirect('/verify-email');
    }
}
