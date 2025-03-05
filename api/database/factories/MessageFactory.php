<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly decide if the message is from a user or a client
        $isFromUser = fake()->boolean();

        return [
            'id' => Uuid::uuid4()->toString(),
            'content' => fake()->paragraph(),
            'read_at' => fake()->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'user_id' => $isFromUser ? User::factory() : null,
            'client_id' => !$isFromUser ? Client::factory() : null,
        ];
    }

    /**
     * Indicate that the message is from a user.
     */
    public function fromUser(User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user ? $user->id : User::factory(),
                'client_id' => null,
            ];
        });
    }

    /**
     * Indicate that the message is from a client.
     */
    public function fromClient(Client $client = null): static
    {
        return $this->state(function (array $attributes) use ($client) {
            return [
                'user_id' => null,
                'client_id' => $client ? $client->id : Client::factory(),
            ];
        });
    }

    /**
     * Indicate that the message is read.
     */
    public function read(): static
    {
        return $this->state(fn(array $attributes) => [
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the message is unread.
     */
    public function unread(): static
    {
        return $this->state(fn(array $attributes) => [
            'read_at' => null,
        ]);
    }
}
