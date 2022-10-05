<?php

namespace Database\Factories\Kos;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kos\KosImage;

class KosImageFactory extends Factory
{
    protected $models = KosImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'image' => $this->faker->image('storage/kos', 360, 360, 'animals'),
            'type' => $this->faker->randomElement(['depan', 'dalam']),
        ];
    }
}
