<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition()
    {
        return [
            'reserved_datetime' => Carbon::now(),
            'number_of_people' => $this->faker->numberBetween(1, 50),
        ];
    }
}
