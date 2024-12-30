<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    /**
     * 利用規約ページを表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // termsテーブルから最初のデータを取得
        $term = Term::first();

        // 利用規約ページのビューを表示
        return view('terms.index', compact('term'));
    }
}
