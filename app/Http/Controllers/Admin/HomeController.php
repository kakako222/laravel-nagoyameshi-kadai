<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 一般ユーザーのホームページ（/home）へのアクセスを制限
        if (Auth::guard('admin')->check()) {
            // 管理者が一般ユーザーのホームページにアクセスしようとした場合
            if (request()->is('home')) {
                abort(403, '管理者は一般ユーザーのページにアクセスできません。');
            }
        }

        // 各種データの取得
        $total_users = User::count(); // usersテーブルの件数
        $total_premium_users = DB::table('subscriptions')
            ->where('stripe_status', 'active')
            ->count(); // 有料会員数（subscriptionsテーブルのstripe_statusが'active'の件数）
        $total_free_users = $total_users - $total_premium_users; // 無料会員数
        $total_restaurants = Restaurant::count(); // restaurantsテーブルの件数
        $total_reservations = Reservation::count(); // reservationsテーブルの件数
        $sales_for_this_month = $total_premium_users * 300; // 月間売上（有料会員数×300）

        // ビューにデータを渡す
        return view('admin.home', compact(
            'total_users',
            'total_premium_users',
            'total_free_users',
            'total_restaurants',
            'total_reservations',
            'sales_for_this_month'
        ));
    }
}
