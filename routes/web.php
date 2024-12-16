<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/auth.php';

// 管理者用のルーティンググループ
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    // ホームページ
    Route::get('home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');

    // 会員一覧ページ
    Route::get('users', [UserController::class, 'index'])->name('users.index');

    // 会員詳細ページ
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
});

// 管理者用認証ルート（認証が必要ないため、外に定義）
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
// ユーザー用のルート（認証済みユーザー）
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('profile');  // 'profile' ビューを表示
    })->name('profile');
});
