<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * 会社概要ページを表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // companiesテーブルから最初のデータを取得
        $company = Company::first();

        // 会社概要ページのビューを表示
        return view('company.index', compact('company'));
    }
}
