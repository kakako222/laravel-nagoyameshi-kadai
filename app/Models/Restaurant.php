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


    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_restaurant');
    }

    /**
     * 店舗の平均評価順で並べ替える
     */
    public function ratingSortable($query, $direction)
    {
        return $query->leftJoin('reviews', 'restaurants.id', '=', 'reviews.restaurant_id')
            ->select('restaurants.*')
            ->selectRaw('COALESCE(AVG(reviews.rating), 0) as average_rating')
            ->groupBy('restaurants.id')
            ->orderBy('average_rating', $direction);
    }

    /**
     * レストランの定休日を取得
     */
    public function regular_holidays()
    {
        return $this->belongsToMany(RegularHoliday::class)->withTimestamps();
    }

    /**
     * レストランのレビュー
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * レストランの予約
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    /**
     * お気に入り
     */
    public function favorite_users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    /**
     * 人気順
     */
    public function popularSortable($query, $direction)
    {
        return $query->withCount('reservations')->orderBy('reservations_count', $direction);
    }
    /**
     * 金額順で並べ替えるメソッド（低価格順）
     */
    public function lowestPriceSortable($query, $direction)
    {
        return $query->orderBy('lowest_price', $direction);
    }
}
