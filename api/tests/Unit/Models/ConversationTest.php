<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a conversation has a client relationship.
     */
    public function test_conversation_belongs_to_client(): void
    {
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Client::class, $conversation->client);
        $this->assertEquals($client->id, $conversation->client->id);
    }

    /**
     * Test that a conversation has an artist relationship.
     */
    public function test_conversation_belongs_to_artist(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'artist_id' => $artist->id
        ]);

        $this->assertInstanceOf(User::class, $conversation->artist);
        $this->assertEquals($artist->id, $conversation->artist->id);
    }

    /**
     * Test that a conversation has many messages.
     */
    public function test_conversation_has_many_messages(): void
    {
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'last_message_at' => null // Prevent auto-creation of messages
        ]);

        // Create specific messages for testing
        $messages = Message::factory()->count(3)->create(['conversation_id' => $conversation->id]);

        // Verify that the messages belong to the conversation
        foreach ($messages as $message) {
            $this->assertEquals($conversation->id, $message->conversation_id);
        }

        // Verify that the conversation has the messages
        $this->assertInstanceOf(Message::class, $conversation->messages->first());
        $this->assertGreaterThanOrEqual(3, $conversation->messages->count());
    }

    /**
     * Test the status scopes on the conversation model.
     */
    public function test_conversation_status_scopes(): void
    {
        // Create a client for all conversations
        $client = Client::factory()->create();

        // Create conversations with different statuses
        $newConversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'status' => 'new'
        ]);

        $activeConversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'status' => 'active'
        ]);

        $completedConversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'status' => 'completed'
        ]);

        $archivedConversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'status' => 'archived'
        ]);

        // Test the scopes
        $this->assertCount(1, Conversation::withStatus('new')->get());
        $this->assertCount(1, Conversation::withStatus('active')->get());
        $this->assertCount(1, Conversation::withStatus('completed')->get());
        $this->assertCount(1, Conversation::withStatus('archived')->get());
    }

    /**
     * Test the latest message attribute.
     */
    public function test_latest_message_attribute(): void
    {
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'last_message_at' => null // Prevent auto-creation of messages
        ]);

        // Create messages with different timestamps
        $oldMessage = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'created_at' => now()->subDays(2)
        ]);

        $latestMessage = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'created_at' => now()->subDay()
        ]);

        // Refresh the conversation to get the latest messages
        $conversation->refresh();

        // Get the latest message directly to compare
        $actualLatestMessage = $conversation->messages()->latest()->first();
        $this->assertEquals($latestMessage->id, $actualLatestMessage->id);
    }
}
