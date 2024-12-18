<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RestaurantFactory extends Factory
{
    /**
     * モデルに対応するファクトリ
     *
     * @var string
     */
    protected $model = Restaurant::class;

    /**
     * ファクトリのデフォルト状態を定義
     *
     * @return array
     */
    public function definition()
    {

        return [
            'name' => 'テスト',
            'description' => 'テスト',
            'lowest_price' => 1000,
            'highest_price' => 5000,
            'postal_code' => '0000000',
            'address' => 'テスト',
            'opening_time' => '10:00:00',
            'closing_time' => '20:00:00',
            'seating_capacity' => 50,
        ];
    }
}
