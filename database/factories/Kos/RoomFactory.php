<?php

namespace Database\Factories\Kos;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kos\Room;

class RoomFactory extends Factory
{
    protected $models = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'size' => $this->faker->randomElement(['3x3', '3x4']),
            'total_room' => $this->faker->numberBetween(1,20),
            'available_room' => $this->faker->numberBetween(1,20),
        ];
    }
}
