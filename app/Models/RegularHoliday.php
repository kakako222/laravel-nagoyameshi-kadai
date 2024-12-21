<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegularHoliday extends Model
{
    use HasFactory;

    //定休日テーブルに対応
    protected $table = 'regular_holidays';

    //許可するカラム
    protected $fillable = [
        'day',
        'day_index',
    ];
    public function restaurants()
    {
        return $this->belongsToMany(Restaurant::class, 'regular_holiday_restaurant');
    }
}
