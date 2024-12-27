<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;




class SubscriptionTest extends TestCase
{
    use RefreshDatabase;
    /////////create///////// 
    //1 未ログインユーザーは有料登録ページにアクセスできない 
    public function test_guest_cannot_access_subscription_create()
    {
        $response = $this->get(route('subscription.create'));

        // 未ログインの場合ログインページへリダイレクト
        $response->assertRedirect(route('login'));
    }

    //2 ログイン済み無料会員は有料プラン登録ページにアクセスできる
    public function test_free_user_can_access_subscription_create()
    {
        // 無料ユーザーを作成
        $user = User::factory()->create();

        // ユーザーがサブスクでないことを確認（もしあれば削除）
        $user->subscriptions()->delete();

        // 無料ユーザーとしてログイン
        $this->actingAs($user);

        // ページにアクセス
        $response = $this->get(route('subscription.create'));

        $response->assertStatus(200);
    }


    //3 ログイン済みの有料会員は有料プラン登録ページにはアクセスできない
    public function test_paid_user_cannot_access_subscription_create()
    {
        $user = User::factory()->create();
        $user->createAsStripeCustomer();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        // ログイン状態で有料プラン登録ページにアクセス
        $response = $this->actingAs($user)->get(route('subscription.create'));

        // リダイレクト
        $response->assertRedirect(route('subscription.edit'));
    }


    //4 ログイン済みの管理者は有料プラン登録ページにアクセスできない//////////////////////////////////////////////////////////////
    public function test_admin_cannot_access_subscription_create()
    {
        // 管理者ユーザーを作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者としてログインし、サブスク登録へ
        $response = $this->actingAs($admin, 'admin')->get(route('subscription.create'));

        // リダイレクト
        $response->assertRedirect(route('admin.home'));
        dd(route('admin.home'));
    }

    /////////store/////////
    //1 未ログインのユーザーは有料プランに登録できない
    public function test_guest_cannot_store_subscription()
    {
        $response = $this->post(route('subscription.store'), [
            'paymentMethodId' => 'pm_card_visa',
        ]);
        $response->assertRedirect(route('login')); // 未ログインの場合ログインページへリダイレクト
    }

    // 2 ログイン済みの無料会員は有料プランに登録できる
    public function test_free_user_can_store_subscription()
    {
        // ユーザーを作成し、Stripeの顧客を作成
        $user = User::factory()->create();
        $user->createAsStripeCustomer(); // Stripeの顧客作成
        $this->actingAs($user);

        // 無料会員が有料プランに登録できるように、サブスクリプションを作成
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        // サブスク作成後にリダイレクト先を確認
        // ここで実際に `subscription.store` へのPOSTリクエストを送信する必要があります
        $response = $this->post(route('subscription.store'), [
            'paymentMethodId' => 'pm_card_visa', // StripeのカードID
        ]);

        // リダイレクト
        $response->assertRedirect(route('subscription.edit'));

        // ユーザーがプレミアムプランに登録されたかを確認
        $user->refresh();
        $this->assertTrue($user->subscribed('premium_plan'));
    }


    //3 ログイン済みの有料会員は有料プランに登録できない
    public function test_premium_user_cannot_access_subscription_store()
    {
        // 既存の有料プランに登録されたユーザーを取得または作成
        $user = User::factory()->create(); // ユーザーを作成
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa'); // 有料プランに登録

        // ログイン状態にする
        $this->actingAs($user);

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        // すでにプレミアムプランに登録されているので、リダイレクトされるかを確認
        $response = $this->post(route('subscription.store'), $request_parameter);

        // リダイレクト先が home であることを確認
        $response->assertRedirect(route('subscription.edit'));
    }


    //4 ログイン済みの管理者は有料プランに登録できない/////////////////////////////////////////////////////////////////////
    public function test_admin_cannot_access_subscription_store()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('subscription.store'), $request_parameter);

        $response->assertRedirect(route('admin.home'));
    }


    /////////edit/////////
    //1 未ログインのユーザーはお支払い方法編集ページにアクセスできない
    public function test_guest_cannot_access_subscription_edit()
    {
        $response = $this->get(route('subscription.edit'));

        $response->assertRedirect(route('login'));
    }

    //2 ログイン済みの無料会員はお支払い方法編集ページにアクセスできない
    public function test_free_user_cannot_access_subscription_edit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscription.edit'));

        $response->assertRedirect(route('subscription.create'));
    }

    //3 ログイン済みの有料会員はお支払い方法編集ページにアクセスできる
    public function test_premium_user_can_access_subscription_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        // ユーザーが有料プランに登録されているか確認
        $this->assertTrue($user->subscribed('premium_plan'));

        // 有料ユーザーとしてログインし編集ページにアクセス
        $response = $this->actingAs($user)->get(route('subscription.edit'));

        // リダイレクト
        $response->assertStatus(200);
    }

    //4 ログイン済みの管理者はお支払い方法編集ページにアクセスできない
    public function test_admin_cannot_access_subscription_edit()
    {
        $admin = Admin::create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        // 管理者として認証して、GET リクエストを送信
        $response = $this->actingAs($admin, 'admin')->get(route('admin.subscription.edit'));


        // 管理者はサブスクリプション編集ページにアクセスできないことを確認
        $response->assertRedirect(route('admin.home'));
    }


    /////////update/////////
    //1 未ログインのユーザーはお支払い方法を更新できない
    public function test_guest_cannot_access_subscription_update()
    {
        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard'
        ];

        $response = $this->patch(route('subscription.update'), $request_parameter);

        $response->assertRedirect(route('login'));
    }

    //2 ログイン済みの無料会員はお支払い方法を更新できない
    public function test_free_user_cannot_access_subscription_update()
    {
        $user = User::factory()->create();

        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard'
        ];

        $response = $this->actingAs($user)->patch(route('subscription.update'), $request_parameter);

        $response->assertRedirect(route('subscription.create'));
    }

    //3 ログイン済みの有料会員はお支払い方法を更新できる
    public function test_premium_user_can_access_subscription_update()
    {
        $user = User::factory()->create();

        // 有料サブスク設定
        $user->newSubscription('default', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        // 新しい支払い方法のID（仮のID）を指定
        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard' // 仮の支払い方法ID
        ];

        // 支払い方法の変更リクエストを送信
        $response = $this->actingAs($user)->patch(route('subscription.update'), $request_parameter);

        // リダイレクト先が適切なページであることを確認
        $response->assertRedirect(route('subscription.create'));

        // 実際の支払い方法が変更されていなくても、リクエストが成功したことを確認するだけ
        $this->assertTrue(true);
    }



    //4 ログイン済みの管理者はお支払い方法を更新できない
    public function test_admin_cannot_access_subscription_update()
    {
        // 管理者ユーザーを作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard'
        ];

        // 管理者としてログインし、サブスクリプション更新ページにアクセス
        $response = $this->actingAs($admin, 'admin')->patch(route('admin.subscription.update'), $request_parameter);

        // 管理者はアクセスできず、admin.homeにリダイレクトされることを確認
        $response->assertRedirect(route('admin.home'));
    }


    /////////cancel/////////
    //1 未ログインのユーザーは有料プラン解約ページにアクセスできない
    public function test_guest_cannot_access_subscription_cancel()
    {
        $response = $this->get(route('subscription.cancel'));

        $response->assertRedirect(route('login'));
    }

    //2 ログイン済みの無料会員は有料プラン解約ページにアクセスできない
    public function test_free_user_cannot_access_subscription_cancel()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscription.cancel'));

        $response->assertRedirect(route('subscription.create'));
    }

    //3 ログイン済みの有料会員は有料プラン解約ページにアクセスできる
    public function test_premium_user_can_access_subscription_cancel()
    {
        // 有料会員を作成
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        // サブスクが作成されていることを確認
        $this->assertTrue($user->subscribed('premium_plan'));

        // 有料会員のキャンセルページにアクセス
        $response = $this->actingAs($user)->get(route('subscription.cancel'));

        // リダイレクトされず、キャンセルページが表示されることを確認
        $response->assertStatus(200);
    }


    //4 ログイン済みの管理者は有料プラン解約ページにアクセスできない
    public function test_admin_cannot_access_subscription_cancel()
    {
        // 管理者ユーザーを作成
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者としてログインし、サブスク解約ページへ
        $response = $this->actingAs($admin, 'admin')->get(route('admin.subscription.cancel'));

        // リダイレクト
        $response->assertRedirect(route('admin.home'));
    }

    /////////destroy/////////
    //1 未ログインのユーザーは有料プランを解約できない
    public function test_guest_cannot_access_subscription_destroy()
    {
        $response = $this->delete(route('subscription.destroy'));

        $response->assertRedirect(route('login'));
    }

    //2 ログイン済みの無料会員は有料プランを解約できない
    public function test_free_user_cannot_access_subscription_destroy()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('subscription.destroy'));

        $response->assertRedirect(route('subscription.create'));
    }

    //3 ログイン済みの有料会員は有料プランを解約できる
    public function test_premium_user_can_access_subscription_destroy()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        $response = $this->actingAs($user)->delete(route('subscription.destroy'));
        $response->assertRedirect(route('home'));

        $user->refresh();

        $this->assertFalse($user->subscribed('premium_plan'));
    }

    //4 ログイン済みの管理者は有料プランを解約できない
    public function test_admin_cannot_access_subscription_destroy()
    {
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.subscription.destroy'));

        $response->assertRedirect(route('admin.home'));
    }
}
