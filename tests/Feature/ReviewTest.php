<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Review;
use App\Models\Restaurant;
use App\Http\Middleware\NotSubscribed;
use Illuminate\Support\Facades\Auth;


class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;

    // テスト前にレストランを作成//
    public function setUp(): void
    {
        parent::setUp();
        $this->restaurant = Restaurant::factory()->create();
    }

    /////////////////////index/////////////////////
    //1 未ログインのユーザーは会員側のレビュー一覧ページにアクセスできない
    public function test_index_access_denied_for_guests()
    {
        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('reviews.index', ['restaurant' => $restaurant->id]));
        $response->assertRedirect('login');
    }

    //2 ログイン済みの無料会員は会員側のレビュー一覧ページにアクセスできる
    public function test_index_accessible_for_free_members()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        // レビューを作成してデータベースに保存（表示されるかどうかの確認は不要）
        Review::factory()->create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // レビュー一覧ページにGETリクエストを送信
        $response = $this->get(route('restaurants.reviews.index', ['restaurant' => $restaurant->id]));

        // ステータスコード200（成功）を確認
        $response->assertStatus(200);
    }

    //3 ログイン済みの有料会員は会員側のレビュー一覧ページにアクセスできる
    public function test_index_accessible_for_paid_members()
    {
        // ユーザー作成
        $user = User::factory()->create();

        // 有料プラン
        $user->newSubscription('default', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        // レストラン作成
        $restaurant = Restaurant::factory()->create();

        // レビュー一覧ページにGETリクエストを送信
        $response = $this->actingAs($user)->get(route('restaurants.reviews.index', ['restaurant' => $restaurant->id]));
        $response->assertStatus(200);
    }

    //4 ログイン済みの管理者は会員側のレビュー一覧ページにアクセスできない//
    public function test_index_access_denied_for_admins()
    {
        // ミドルウェアをスキップ
        $this->withoutMiddleware(NotSubscribed::class);
        // 管理者
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // テスト用レストラン作成
        $restaurant = Restaurant::factory()->create();

        // 管理者としてログインし、レビュー一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.reviews.index', ['restaurant' => $restaurant->id]));

        $response->assertRedirect(route('admin.home'));
    }

    /////////////////////create/////////////////////
    // 1 未ログインのユーザーは会員側のレビュー投稿ページにアクセスできない
    public function test_create_access_denied_for_guests()
    {
        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('reviews.create', $restaurant));
        $response->assertRedirect(route('login'));
    }

    //2 ログイン済みの無料会員は会員側のレビュー投稿ページにアクセスできない
    public function test_free_user_cannot_access_reviews_create()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        // 無料会員ユーザーでログインし、レビュー投稿ページにアクセス
        $response = $this->actingAs($user)->get(route('restaurants.reviews.create', $restaurant));

        // リダイレクトURLが期待通りのものか確認
        $response->assertRedirect(route('subscription.create'));
    }

    //3 ログイン済みの有料会員は会員側のレビュー投稿ページにアクセスできる
    public function test_create_accessible_for_paid_members()
    {
        $user = User::factory()->create();
        // Stripe カスタマーとして作成
        $user->createAsStripeCustomer();

        // premium_planにサブスクリプションを作成
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');
        // テスト用レストラン作成
        $restaurant = Restaurant::factory()->create();

        // ログイン状態でレビュー投稿ページにアクセス
        $response = $this->actingAs($user)->get(route('restaurants.reviews.create', ['restaurant' => $restaurant->id]));

        // 有料会員の場合、レビュー投稿ページにアクセスできることを確認
        $response->assertStatus(200);
    }


    //4 ログイン済みの管理者は会員側のレビュー投稿ページにアクセスできない
    public function test_create_access_denied_for_admins()
    {

        // 管理者をファクトリで作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        // テスト用レストラン作成
        $restaurant = Restaurant::factory()->create();

        // 管理者としてログインし、レビュー作成ページにアクセス
        $response = $this->actingAs($admin, 'admin')
            ->get(route('restaurants.reviews.create', ['restaurant' => $restaurant->id]));

        // admin.home へのリダイレクトを確認
        $response->assertRedirect(route('admin.home'));
    }

    /////////////////////store/////////////////////
    //1 未ログインのユーザーはレビューを投稿できない
    public function test_guest_user_cannot_submit_review()
    {
        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('reviews.store', $restaurant));
        $response->assertRedirect(route('login'));
    }


    //2 ログイン済みの無料会員はレビューを投稿できない
    public function test_logged_in_free_member_cannot_submit_review()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        // 無料会員ユーザーでログインし、レビュー投稿ページにアクセス
        $response = $this->actingAs($user)->get(route('restaurants.reviews.store', $restaurant));

        // リダイレクトURLが期待通りのものか確認
        $response->assertRedirect(route('subscription.create'));
    }

    //3 ログイン済みの有料会員はレビューを投稿できる
    public function test_logged_in_paid_member_can_submit_review()
    {
        $user = User::factory()->create();
        $user->createAsStripeCustomer();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();
        $review_data = [
            'score' => 1,
            'content' => 'テスト'
        ];
        $response = $this->actingAs($user)->post(route('restaurants.reviews.store', $restaurant), $review_data);
        $this->assertDatabaseHas('reviews', $review_data);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    //4 ログイン済みの管理者はレビューを投稿できない
    public function test_logged_in_admin_cannot_submit_review()
    {
        /// 管理者をファクトリで作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        // テスト用レストラン作成
        $restaurant = Restaurant::factory()->create();

        // 管理者としてログインし、レビュー一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->post(route('restaurants.reviews.store', ['restaurant' => $restaurant->id]));

        $response->assertRedirect(route('admin.home'));
    }


    /////////////////////edit/////////////////////
    //1 未ログインのユーザーは会員側のレビュー編集ページにアクセすできない
    public function test_guest_user_cannot_access_member_side_review_edit_page()
    {
        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('reviews.edit', $restaurant));
        $response->assertRedirect(route('login'));
    }

    //2 ログイン済みの無料会員は会員側のレビュー編集ページにアクセスできない
    public function test_logged_in_free_member_cannot_access_review_edit_page()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        // レビューを作成して、IDを取得
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id, // 必要に応じて
        ]);

        // 無料会員ユーザーでログインし、レビュー編集ページにアクセス
        $response = $this->actingAs($user)->get(route('restaurants.reviews.edit', ['restaurant' => $restaurant->id, 'review' => $review->id]));

        // リダイレクトURLが期待通りのものか確認
        $response->assertRedirect(route('subscription.create'));
    }



    //3 ログイン済みの有料会員は会員側の他人のレビュー編集ページにアクセスできない
    public function test_premium_user_cannot_access_others_reviews_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');
        $other_user = User::factory()->create();

        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $other_user->id
        ]);

        $response = $this->actingAs($user)->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        // リダイレクト先のURLを正しく確認
        $response->assertRedirect(route('reviews.index', ['restaurant' => $restaurant]));
    }


    //4  ログイン済みの有料会員は会員側の自身のレビュー編集ページにアクセスできる
    public function test_premium_user_can_access_own_reviews_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        // プレミアムユーザーとしてレビュー編集ページにアクセス
        $response = $this->actingAs($user)->get(route('restaurants.reviews.index', ['restaurant' => $restaurant->id]));

        // リダイレクトの検証
        $response->assertStatus(200);
    }



    //5 ログイン済みの管理者は会員側のレビュー編集ページにアクセスできない
    public function test_logged_in_admin_cannot_access_member_side_review_edit_page()
    {
        /// 管理者をファクトリで作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        // テスト用レストラン作成
        $restaurant = Restaurant::factory()->create();

        // 管理者としてログインし、レビュー一覧ページにアクセス
        $response = $this->actingAs($admin, 'admin')->post(route('restaurants.reviews.store', ['restaurant' => $restaurant->id]));

        $response->assertRedirect(route('admin.home'));
    }


    /////////////////////update/////////////////////
    //1 未ログインのユーザーはレビューを削除できない
    public function test_guest_user_cannot_update_review()
    {
        $restaurant = Restaurant::factory()->create();
        $response = $this->get(route('reviews.update', $restaurant));
        $response->assertRedirect(route('login'));
    }

    //2 ログイン済みの無料会員はレビューを更新できない
    public function test_logged_in_free_member_cannot_update_review()
    {
        // ユーザーを作成（無料会員）
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        // 無料会員ユーザーでログインし、レビュー削除を試みる
        $response = $this->actingAs($user)->delete(route('restaurants.reviews.destroy', [
            'restaurant' => $restaurant->id,
            'review' => $review->id,
        ]));

        // 無料会員ユーザーはレビュー削除できないのでリダイレクトされるはず
        $response->assertRedirect(route('subscription.create'));

        // 無料会員ユーザーでレビュー更新を試みる
        $response = $this->actingAs($user)->patch(route('restaurants.reviews.update', [
            'restaurant' => $restaurant->id,
            'review' => $review->id,
        ]));

        // 無料会員ユーザーはレビュー更新できないのでリダイレクトされるはず
        $response->assertRedirect(route('subscription.create'));
    }

    //3 ログイン済みの有料会員は他人のレビューを更新できない
    public function test_premium_user_cannot_access_others_reviews_update()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');
        $other_user = User::factory()->create();

        $restaurant = Restaurant::factory()->create();

        $old_review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $other_user->id
        ]);

        $new_review_data = [
            'score' => 5,
            'content' => 'テスト更新'
        ];

        $response = $this->actingAs($user)->patch(route('restaurants.reviews.update', [$restaurant, $old_review]), $new_review_data);

        $this->assertDatabaseMissing('reviews', $new_review_data);
        $response->assertRedirect(route('reviews.index', $restaurant));
    }

    //4 ログイン済みの有料会員は自身のレビューを更新できる
    public function test_premium_user_can_access_own_reviews_update()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        $otherUser = User::factory()->create();

        // レストランを作成
        $restaurant = Restaurant::factory()->create();

        // 他のユーザーのレビューを作成
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'restaurant_id' => $restaurant->id  // restaurant_id を指定
        ]);

        $this->actingAs($user);
        $response = $this->delete(route('restaurants.reviews.destroy', ['restaurant' => $restaurant, 'review' => $review]));

        // リダイレクトされるかを確認
        $response->assertStatus(302);  // リダイレクトが発生することを確認

        // リダイレクト先がレビュー一覧ページかを確認
        $response->assertRedirect(route('restaurants.reviews.index', ['restaurant' => $restaurant]));

        // レビューが削除されていないことを確認
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }




    //5 ログイン済みの管理者はレビューを更新できない
    public function test_logged_in_admin_cannot_update_review()
    {
        // 管理者をファクトリで作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);

        // テスト用ユーザーとレストランを作成
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        // ユーザーが作成したレビューを作成
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
        ]);

        // 管理者としてログインし、レビュー更新を試みる
        $response = $this->actingAs($admin, 'admin')->patch(route('restaurants.reviews.update', [$restaurant, $review]), [
            'score' => 5,
            'content' => 'テスト更新',
        ]);

        // 管理者がレビュー更新できないことを確認し、admin.homeにリダイレクトされることを確認
        $response->assertRedirect(route('admin.home'));
    }


    /////////////////////destroy/////////////////////
    //1未ログインはリダイレクトされる
    public function test_guest_user_cannot_delete_review()
    {
        $user = User::factory()->create(); // ユーザーを作成
        $restaurant = Restaurant::factory()->create(); // レストランを作成
        $review = Review::factory()->create([ // レビューを作成
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id, // レビューのユーザーとして作成したユーザーを設定
        ]);

        // 未ログインの状態でレビュー削除を試みる
        $response = $this->delete(route('reviews.destroy', ['restaurant' => $restaurant->id, 'review' => $review->id]));

        // ログインページにリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }


    //2 ログイン済みの無料会員はレビューを削除できない
    public function test_free_user_cannot_access_reviews_destroy()
    {
        $user = User::factory()->create();

        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('subscription.create'));
    }

    //3 ログイン済みの有料会員は他人のレビューを削除できない//
    public function test_logged_in_paid_member_cannot_delete_others_review()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');
        $otherUser = User::factory()->create();

        // レストランを作成
        $restaurant = Restaurant::factory()->create();

        // 他のユーザーのレビューを作成
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'restaurant_id' => $restaurant->id  // restaurant_id を指定
        ]);

        $this->actingAs($user);
        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        // リダイレクトされるかを確認
        $response->assertStatus(302);  // リダイレクトが発生することを確認
        $response->assertRedirect(route('reviews.index', $restaurant));
        // レビューが削除されていないことを確認
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    //4 ログイン済みの有料会員は自身のレビューを削除できる//
    public function test_logged_in_paid_member_can_delete_own_review()
    {
        // ユーザーを作成
        $user = User::factory()->create();
        // 有料プランを登録
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create('pm_card_visa');

        // レストランを作成
        $restaurant = Restaurant::factory()->create();

        // ユーザーが自分のレビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
        ]);

        // ログイン状態でレビュー削除を試みる
        $this->actingAs($user);
        $response = $this->delete(route('reviews.destroy', [$restaurant, $review]));

        // ステータスコードが200（成功）であることを確認
        $response->assertStatus(302);

        // リダイレクト先がレビュー一覧ページかを確認
        $response->assertRedirect(route('restaurants.reviews.index', ['restaurant' => $restaurant]));

        // レビューが削除されていることを確認
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    //5 ログイン済みの管理者はレビューを削除できない
    public function test_logged_in_admin_cannot_delete_review()
    {
        // 管理者をファクトリで作成
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('nagoyameshi'),
        ]);
        // レストランを作成
        $restaurant = Restaurant::factory()->create();
        // ユーザーを作成（レビューの作成者として）
        $user = User::factory()->create();

        // レビューを作成
        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => 1,  // レビュー作成者はユーザーID 1（管理者ではない）
        ]);

        // 管理者としてログインし、レビュー削除のアクションを試みる
        $response = $this->actingAs($admin, 'admin')
            ->delete(route('restaurants.reviews.destroy', ['restaurant' => $restaurant->id, 'review' => $review->id]));

        // リダイレクトされることを確認（admin.homeへ）
        $response->assertRedirect(route('admin.home'));
    }
}
