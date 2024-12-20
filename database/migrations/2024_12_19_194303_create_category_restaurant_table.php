<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_restaurant', function (Blueprint $table) {
            $table->id(); //ID
            $table->foreignId('restaurant_id') //店舗ID
                ->constrained()  //外部制約キー
                ->cascadeOnDelete(); //参照先が削除されたら削除
            $table->foreignId('category_id') //カテゴリーID
                ->constrained() //外部制約キー
                ->cascadeOnDelete(); //参照先が削除されたら削除
            $table->timestamps(); //作成日時・更新日時
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_restaurant');
    }
};
