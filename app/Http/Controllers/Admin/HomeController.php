<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

        return view('admin.home');
    }
}
