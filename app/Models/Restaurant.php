<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Restaurant extends Model
{
    use HasFactory, Sortable;

    protected $fillable = [
        'name',
        'image',
        'description',
        'lowest_price',
        'highest_price',
        'postal_code',
        'address',
        'opening_time',
        'closing_time',
        'seating_capacity'
    ];

    // 並べ替え可能なカラムを指定
    public $sortable = ['name', 'lowest_price', 'highest_price', 'created_at'];

    /**
     * このレストランが関連するカテゴリを取得する。
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_restaurant');
    }

    /**
     * 店舗の平均評価順で並べ替えるメソッド
     */
    public function ratingSortable($query, $direction)
    {
        return $query->leftJoin('reviews', 'restaurants.id', '=', 'reviews.restaurant_id')
            ->select('restaurants.*')
            ->selectRaw('COALESCE(AVG(reviews.rating), 0) as average_rating')
            ->groupBy('restaurants.id')
            ->orderBy('average_rating', $direction);
    }


    public function regular_holidays()
    {
        return $this->belongsToMany(RegularHoliday::class, 'regular_holiday_restaurant', 'restaurant_id', 'regular_holiday_id')
            ->select('regular_holidays.id as holiday_id', 'regular_holidays.day'); // カラムにエイリアスをつける
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
