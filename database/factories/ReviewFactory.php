<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    public function definition()
    {
        return [
            'score' => 1, // 固定値: 1
            'content' => 'テスト', // 固定値: 'テスト'

        ];
    }
}
