<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\ProfileController;

require __DIR__ . '/auth.php';

// 管理者用認証ルート（認証不要）
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// 一般ユーザー用ルート
Route::middleware(['auth'])->group(function () {
    // 明示的に名前付きルートを定義
    Route::get('user', [UserController::class, 'index'])->name('user.index');
    Route::get('user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::get('restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');


    // プロフィール関連ルート
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show'); // 表示
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit'); // 編集フォーム
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update'); // 更新
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // 削除
});

// 一般ユーザーやゲスト用ルート
Route::group(['middleware' => 'guest:admin'], function () {
    // トップページ
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::resource('restaurants', RestaurantController::class)->only(['index', 'show'])->names('restaurants');
});

// 店舗一覧ページ（管理者としてログインしていない場合にのみアクセス可能）
//Route::middleware(['guest'])->group(function () {
//  Route::resource('restaurants',  App\Http\Controllers\RestaurantController::class)->only(['index']); // 店舗一覧（管理者としてログインしていない状態でアクセス）
//});

// ゲスト（管理者としてログインしていない状態）用のルートグループ
//Route::get('/', [HomeController::class, 'index'])->name('home');


// 管理者用ルート(認証が必要)
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home'); //管理者ホームページ
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index'); //会員一覧
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');  // 会員詳細ページ
    Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('user.update');

    Route::get('restaurants', [AdminRestaurantController::class, 'index'])->name('admin.restaurants.index');
    Route::resource('restaurants', AdminRestaurantController::class); // 店舗関連のルート(edit)
    Route::resource('categories', CategoryController::class)->except(['show']);  // カテゴリ管理

    // 会社概要関連
    Route::get('company', [CompanyController::class, 'index'])->name('company.index');
    Route::get('company/{company}/edit', [CompanyController::class, 'edit'])->name('company.edit');
    Route::match(['put', 'patch'], 'company/{company}', [CompanyController::class, 'update'])->name('company.update');

    // 利用規約関連
    Route::get('terms', [TermController::class, 'index'])->name('terms.index');
    Route::get('terms/{term}/edit', [TermController::class, 'edit'])->name('terms.edit');
    Route::patch('terms/{term}', [TermController::class, 'update'])->name('terms.update');
});
