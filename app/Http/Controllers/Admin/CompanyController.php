<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Auth ファサードを追加

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');  // 管理者用の認証を必須
    }

    public function index()
    {
        $company = Company::first();
        return view('admin.company.index', compact('company'));
    }

    public function edit(Company $company)
    {
        // 管理者として認証されているかを確認する
        dd(auth('admin')->user());

        /* 管理者以外は403 */
        if (Auth::user() && !Auth::user()->is_admin) {
            abort(403, 'アクセスが拒否されました。');
        }
        return view('admin.company.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required',
            'postal_code' => 'required|numeric|digits:7',
            'address' => 'required',
            'representative' => 'required',
            'establishment_date' => 'required',
            'capital' => 'required',
            'business' => 'required',
            'number_of_employees' => 'required',
        ]);

        $company->update($request->all());

        return redirect()->route('admin.company.index')->with('flash_message', '会社概要を編集しました。');
    }
}
