<?php

namespace Database\Factories\Kos;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kos\Facility;

class FacilityFactory extends Factory
{
    protected $models = Facility::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'public_facility' => $this->faker->randomElement(['musholla','gym']),
            'room_facility' => $this->faker->randomElement(['lemari','kasur','bantal']),
            'bath_facility' => $this->faker->randomElement(['didalam','shower','diluar']),
            'park_facility' => $this->faker->randomElement(['mobil','motor']),
        ];
    }
}
