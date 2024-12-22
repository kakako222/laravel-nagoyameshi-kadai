<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/auth.php';

// 管理者用認証ルート（認証が必要ないため、外に定義）
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    // 管理者ログイン用ルート
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// 管理者用のルーティンググループ(認証が必要)
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    // 管理者ホームページ
    Route::get('home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');

    // 会員一覧ページ
    Route::get('users', [UserController::class, 'index'])->name('users.index');

    // 会員詳細ページ
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

    // 店舗関連のルート(edit)
    Route::resource('restaurants', RestaurantController::class);

    // カテゴリ管理
    Route::resource('categories', CategoryController::class)->except(['show']);

    // 会社概要関連
    Route::get('company', [CompanyController::class, 'index'])->name('company.index');
    Route::get('company/{company}/edit', [CompanyController::class, 'edit'])->name('company.edit');
    Route::put('company/{company}', [CompanyController::class, 'update'])->name('company.update');

    // 利用規約関連
    Route::get('terms', [TermController::class, 'index'])->name('terms.index');
    Route::get('terms/{term}/edit', [TermController::class, 'edit'])->name('terms.edit');
    Route::put('terms/{term}', [TermController::class, 'update'])->name('terms.update');
});

// ユーザー用のルート（認証済みユーザー）
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('profile');  // 'profile' ビューを表示
    })->name('profile');

    // プロフィール編集フォーム
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

    // プロフィール情報の更新
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // アカウント削除
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
