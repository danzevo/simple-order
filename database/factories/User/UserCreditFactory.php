<?php

namespace Database\Factories\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User\UserCredit;

class UserCreditFactory extends Factory
{
    protected $models = UserCredit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'credit' => $this->faker->numberBetween(0, 100)
        ];
    }
}
