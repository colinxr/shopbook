<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Uuid::uuid4()->toString(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'notes' => fake()->paragraph(),
        ];
    }

    /**
     * Indicate that the client has a user account.
     */
    public function withUser(): static
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->create([
                'email' => $attributes['email'],
                'name' => $attributes['name'],
                'phone' => $attributes['phone'],
                'role' => 'client',
            ]);

            return [
                'user_id' => $user->id,
            ];
        });
    }
}
