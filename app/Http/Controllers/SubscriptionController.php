<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;




class SubscriptionController extends Controller
{
    public function __construct()
    {
        // 未ログインのユーザーはアクセスできないようにする
        $this->middleware('auth');
    }
    ////////////////////create////////////////////////
    //作成
    public function create()
    {
        if (!Auth::check()) {
            // ユーザーが未ログインの場合はリダイレクト
            return redirect()->route('login');
        }
        $intent = Auth::user()->createSetupIntent();

        return view('subscription.create', compact('intent'));
    }

    ////////////////////store////////////////////////
    // 登録機能
    public function store(Request $request)
    {
        $user = Auth::user();

        // 既にプレミアムプランに登録されているかをチェック
        if ($user->subscribed('premium_plan')) {
            // すでに登録されている場合、リダイレクト
            return redirect()->route('home');
        }

        // サブスクの作成（premium_planプランに登録）
        $user->newSubscription('premium_plan', 'price_1QZk9BK6fTyCyP966vB53Xje')->create($request->paymentMethodId);

        session()->flash('flash_message', '有料プランへの登録が完了しました。');

        // 会員側のトップページへ
        return redirect()->route('home');
    }

    ////////////////////edit////////////////////////
    // 編集ページ
    public function edit()
    {
        // 管理者がアクセスした場合
        //if (auth()->guard('admin')->check()) {
        //  return redirect()->route('admin.home');
        //}
        $user = Auth::user();

        // 現在ログイン中のユーザーのSetupIntentオブジェクトを作成
        $intent = $user->createSetupIntent();
        return view('subscription.edit', compact('user', 'intent'));
    }


    ////////////////////update////////////////////////
    //更新
    public function update(Request $request)
    {
        $user = $request->user();

        $user->updateDefaultPaymentMethod($request->paymentMethodId);


        return redirect()->route('home')->with('flash_message', 'お支払方法を変更しました。');
    }
    ////////////////////cancel//////////////////////
    //解約ページ
    public function cancel()
    {
        $user = Auth::user();

        if (auth('admin')->check()) {
            return redirect()->route('admin.home');
        }

        // サブスクリプションが有効かどうかをチェック
        if (!$user->subscribed('premium_plan')) {
            // サブスクリプションが無効な場合、エラーメッセージと共にホームページへ
            return redirect()->route('home')->with('error', '現在、解約できるサブスクリプションはありません。');
        }

        // サブスクが有効な場合、解約ページを表示
        return view('subscription.cancel');
    }


    ////////////////////destroy//////////////////////
    // 解約機能
    public function destroy()
    {
        $user = Auth::user();
        $admin = Auth::guard('admin')->user();  // 管理者ユーザーを取得

        // 管理者がアクセスしようとした場合はホームページへ
        if ($admin) {
            return redirect()->route('admin.home');
        }

        // サブスクの解約
        $subscription = $user->subscription('premium_plan');

        // 即座にサブスクを解約
        $subscription->cancelNow();

        session()->flash('flash_message', '有料プランを解約しました。');

        // 会員側のトップページへ
        return redirect()->route('home');
    }
}
