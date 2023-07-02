<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->words(4, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(100, 1_000),
            'quantity' => $this->faker->numberBetween(0, 100),
        ];
    }
}
