<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantsTable extends Migration
{
    public function up()
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();  // nullable() を追加
            $table->text('description');
            $table->unsignedInteger('lowest_price');
            $table->unsignedInteger('highest_price');
            $table->string('postal_code');
            $table->string('address');
            $table->time('opening_time');
            $table->time('closing_time');
            $table->unsignedInteger('seating_capacity');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('restaurants');
    }
}
