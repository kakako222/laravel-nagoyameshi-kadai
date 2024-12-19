@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-center">カテゴリ一覧</h1>

    <!-- 検索ボックス -->
    <div class="d-flex justify-content-between align-items-end flex-wrap mb-3">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="nagoyameshi-admin-search-box">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="カテゴリ名で検索" name="keyword" value="{{ $keyword }}">
                <button type="submit" class="btn text-white shadow-sm nagoyameshi-btn">検索</button>
            </div>
        </form>

        <a href="#" class="btn text-white shadow-sm nagoyameshi-btn" data-bs-toggle="modal" data-bs-target="#createCategoryModal">＋ 新規登録</a>
    </div>

    <!-- フラッシュメッセージ -->
    @if (session('flash_message'))
    <div class="alert alert-info" role="alert">
        <p class="mb-0">{{ session('flash_message') }}</p>
    </div>
    @endif

    <!-- エラーメッセージ -->
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- 総件数表示 -->
    <div class="mb-3">
        <p class="mb-0">計{{ number_format($total) }}件</p>
    </div>

    <!-- カテゴリ一覧テーブル -->
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">カテゴリ名</th>
                <th scope="col"></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>{{ $category->name }}</td>
                <td>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}">編集</a>
                </td>
                <td>
                    <a href="#" class="link-secondary" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal" data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}">削除</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">カテゴリが見つかりません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ページネーション -->
    <div class="d-flex justify-content-center">
        {{ $categories->appends(request()->query())->links() }}
    </div>
</div>
@endsection