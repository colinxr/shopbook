<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Conversation Seeder
 * 
 * Creates test conversations and messages for development and testing.
 */
class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $artists = User::where('role', 'artist')->get();

        if ($clients->isEmpty() || $artists->isEmpty()) {
            $this->command->info('No clients or artists found. Skipping conversation seeding.');
            return;
        }

        // Create conversations for each client
        foreach ($clients as $client) {
            // Create 1-3 conversations per client
            $conversationCount = rand(1, 3);

            for ($i = 0; $i < $conversationCount; $i++) {
                // Randomly assign an artist or leave it null (new conversation)
                $artist = rand(0, 10) > 3 ? $artists->random() : null;
                $status = $artist ? fake()->randomElement(['active', 'completed', 'archived']) : 'new';

                $conversation = Conversation::factory()->create([
                    'client_id' => $client->id,
                    'artist_id' => $artist ? $artist->id : null,
                    'status' => $status,
                    'last_message_at' => now()->subDays(rand(0, 30)),
                ]);

                // Create messages for the conversation
                $messageCount = rand(1, 15);

                // First message is always from the client
                Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'client_id' => $client->id,
                    'user_id' => null,
                    'content' => fake()->paragraph(),
                    'created_at' => $conversation->created_at,
                    'updated_at' => $conversation->created_at,
                ]);

                // Create additional messages
                if ($artist && $messageCount > 1) {
                    for ($j = 1; $j < $messageCount; $j++) {
                        $isFromArtist = rand(0, 1) === 1;
                        $createdAt = fake()->dateTimeBetween($conversation->created_at, 'now');

                        Message::factory()->create([
                            'conversation_id' => $conversation->id,
                            'client_id' => $isFromArtist ? null : $client->id,
                            'user_id' => $isFromArtist ? $artist->id : null,
                            'content' => fake()->paragraph(),
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                            'read_at' => rand(0, 10) > 3 ? $createdAt : null,
                        ]);
                    }
                }
            }
        }
    }
}
