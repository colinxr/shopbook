<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a message belongs to a conversation.
     */
    public function test_message_belongs_to_conversation(): void
    {
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);

        $this->assertInstanceOf(Conversation::class, $message->conversation);
        $this->assertEquals($conversation->id, $message->conversation->id);
    }

    /**
     * Test that a message can belong to a user.
     */
    public function test_message_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $message = Message::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $message->user);
        $this->assertEquals($user->id, $message->user->id);
    }

    /**
     * Test that a message can belong to a client.
     */
    public function test_message_belongs_to_client(): void
    {
        $client = Client::factory()->create();
        $message = Message::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Client::class, $message->client);
        $this->assertEquals($client->id, $message->client->id);
    }

    /**
     * Test the isSentByUser helper method.
     */
    public function test_is_sent_by_user_method(): void
    {
        $user = User::factory()->create();
        $message = Message::factory()->create(['user_id' => $user->id, 'client_id' => null]);

        $this->assertTrue($message->isSentByUser());
        $this->assertFalse($message->isSentByClient());
    }

    /**
     * Test the isSentByClient helper method.
     */
    public function test_is_sent_by_client_method(): void
    {
        $client = Client::factory()->create();
        $message = Message::factory()->create(['client_id' => $client->id, 'user_id' => null]);

        $this->assertTrue($message->isSentByClient());
        $this->assertFalse($message->isSentByUser());
    }

    /**
     * Test the isRead and markAsRead methods.
     */
    public function test_read_status_methods(): void
    {
        $message = Message::factory()->create(['read_at' => null]);

        $this->assertFalse($message->isRead());

        $message->markAsRead();
        $this->assertTrue($message->isRead());
        $this->assertNotNull($message->read_at);
    }

    /**
     * Test the sender name accessor.
     */
    public function test_sender_name_accessor(): void
    {
        // Test user sender
        $user = User::factory()->create(['name' => 'Test Artist']);
        $userMessage = Message::factory()->create(['user_id' => $user->id, 'client_id' => null]);
        $this->assertEquals('Test Artist', $userMessage->sender_name);

        // Test client sender
        $client = Client::factory()->create(['name' => 'Test Client']);
        $clientMessage = Message::factory()->create(['client_id' => $client->id, 'user_id' => null]);
        $this->assertEquals('Test Client', $clientMessage->sender_name);
    }
}
