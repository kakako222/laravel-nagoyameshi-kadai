<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant_user', function (Blueprint $table) {
            $table->id();  // ID
            $table->foreignId('restaurant_id')  // restaurant_id
                ->constrained()  // 外部キー制約（restaurantテーブルを参照）
                ->cascadeOnDelete();  // 参照先が削除されたときに参照元も削除
            $table->foreignId('user_id')  // user_id
                ->constrained()  // 外部キー制約（usersテーブルを参照）
                ->cascadeOnDelete();  // 参照先が削除されたときに参照元も削除
            $table->timestamps();  // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurant_user');
    }
}
