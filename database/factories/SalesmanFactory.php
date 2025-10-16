<?php

namespace Database\Factories;

use App\Models\Salesman;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Salesman>
 */
class SalesmanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Salesman>
     */
    protected $model = Salesman::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'titles_before' => null,
            'titles_after' => null,
            'prosight_id' => $this->faker->unique()->numerify('#####'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'gender' => $this->faker->randomElement(['m', 'f']),
            'marital_status' => $this->faker->randomElement(['single', 'married', 'divorced', 'widowed']),
        ];
    }
}
