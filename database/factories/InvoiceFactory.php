<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
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
            'customer_id' => fn (array $attributes) => Customer::factory()->for(User::find($attributes['user_id'])),
            'amount' => fake()->numberBetween(100, 1_000),
            'issued_at' => $issuedAt = fake()->dateTimeBetween('-1 month'),
            'due_at' => fake()->dateTimeBetween($issuedAt, '+1 month')
        ];
    }
}
