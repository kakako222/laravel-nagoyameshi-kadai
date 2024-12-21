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
        Schema::create('regular_holiday_restaurant', function (Blueprint $table) {
            $table->id(); // ID
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete(); // 店舗のID
            $table->foreignId('regular_holiday_id')->constrained()->cascadeOnDelete(); // 定休日のID
            $table->timestamps(); // 作成日時・更新日時
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regular_holiday_restaurant');
    }
};
