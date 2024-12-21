<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RegularHolidayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\RegularHoliday::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'day' => $this->faker->dayOfWeek(),
        ];
    }
}
