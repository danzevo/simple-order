<?php

namespace Database\Factories\Kos;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kos\Kos;

class KosFactory extends Factory
{
    protected $models = Kos::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name,
            "price" => $this->faker->randomNumber(6, true),
            "kos_type" => $this->faker->randomElement(['putra','putri','campur']),
            "description" => $this->faker->sentence(),
            "kos_established" => $this->faker->year(),
            "room_type" => $this->faker->sentence(3),
            "admin_name" => $this->faker->name
        ];
    }
}
