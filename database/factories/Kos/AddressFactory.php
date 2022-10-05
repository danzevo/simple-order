<?php

namespace Database\Factories\Kos;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Kos\Address;

class AddressFactory extends Factory
{
    protected $models = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'province' => $this->faker->randomElement(['jawa barat', 'jawa tengah', 'jawa timur']),
            'city' => $this->faker->randomElement(['bandung', 'banten', 'purwakarta', 'karawang', 'yogyakarta','surabaya']),
            'district' => $this->faker->randomElement(['kiaracondong', 'dago', 'tegalega', 'malioboro','bsd']),
            'address' => $this->faker->randomElement(['jl kiaracondong', 'jl. dago', 'jl. tegalega', 'jl. malioboro']),
        ];
    }
}
