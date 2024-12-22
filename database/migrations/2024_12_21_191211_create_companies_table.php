<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();  // IDカラム
            $table->string('name');  // 会社名
            $table->string('postal_code');  // 郵便番号
            $table->string('address');  // 所在地
            $table->string('representative');  // 代表者
            $table->string('establishment_date');  // 設立年月日
            $table->string('capital');  // 資本金
            $table->string('business');  // 事業内容
            $table->string('number_of_employees');  // 従業員数
            $table->timestamps();  // 作成日時、更新日時
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
}
