<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * 定義するモデルのデフォルト状態
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'テスト',  // nameに 'テスト' を指定
        ];
    }
}
