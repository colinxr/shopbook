<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
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
            'client_id' => function () {
                return Client::factory()->create()->id;
            },
            'artist_id' => null, // Artist ID, nullable
            'title' => fake()->sentence(3),
            'status' => fake()->randomElement(['new', 'active', 'completed', 'archived']),
            'last_message_at' => fake()->optional(0.8)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the conversation is new.
     */
    public function newStatus(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'new',
        ]);
    }

    /**
     * Indicate that the conversation is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the conversation is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the conversation is archived.
     */
    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Conversation $conversation) {
            //
        })->afterCreating(function (Conversation $conversation) {
            // Create messages for the conversation if it has a last_message_at timestamp
            if ($conversation->last_message_at) {
                $conversation->messages()->saveMany(
                    \App\Models\Message::factory()
                        ->count(fake()->numberBetween(1, 10))
                        ->make(['conversation_id' => $conversation->id])
                );
            }
        });
    }
}
