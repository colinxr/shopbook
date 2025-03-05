<?php

namespace Tests\Feature\Features;

use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConversationManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that a conversation can be created with a client.
     */
    public function test_conversation_can_be_created_with_client(): void
    {
        // Create a client
        $client = Client::factory()->create();

        // Create a conversation
        $conversation = Conversation::create([
            'client_id' => $client->id,
            'title' => 'Test Conversation',
            'status' => 'new'
        ]);

        // Create an initial message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'content' => 'Initial message from client'
        ]);

        // Check that the conversation was created in the database
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'title' => 'Test Conversation',
            'client_id' => $client->id,
            'status' => 'new'
        ]);

        // Check that the initial message was created
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'content' => 'Initial message from client'
        ]);
    }

    /**
     * Test that an artist can be assigned to a conversation.
     */
    public function test_artist_can_be_assigned_to_conversation(): void
    {
        // Create a client and a conversation
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'status' => 'new'
        ]);

        // Create an artist
        $artist = User::factory()->create(['role' => 'artist']);

        // Assign the artist to the conversation
        $conversation->artist_id = $artist->id;
        $conversation->status = 'active';
        $conversation->save();

        // Check that the conversation was updated in the database
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'artist_id' => $artist->id,
            'status' => 'active'
        ]);
    }

    /**
     * Test that a user can send a message to a conversation.
     */
    public function test_user_can_send_message_to_conversation(): void
    {
        // Create an artist
        $artist = User::factory()->create(['role' => 'artist']);

        // Create a client and a conversation with the artist assigned
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'artist_id' => $artist->id,
            'status' => 'active'
        ]);

        // Create a message from the artist
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $artist->id,
            'content' => 'Test message from artist'
        ]);

        // Update the conversation's last_message_at timestamp
        $conversation->last_message_at = now();
        $conversation->save();

        // Check that the message was created in the database
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'user_id' => $artist->id,
            'content' => 'Test message from artist'
        ]);

        // Check that the conversation's last_message_at was updated
        $conversation->refresh();
        $this->assertNotNull($conversation->last_message_at);
    }

    /**
     * Test that a client can send a message to a conversation.
     */
    public function test_client_can_send_message_to_conversation(): void
    {
        // Create a client and a conversation
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'status' => 'new'
        ]);

        // Create a message from the client
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'content' => 'Test message from client'
        ]);

        // Check that the message was created in the database
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'content' => 'Test message from client'
        ]);
    }

    /**
     * Test that messages can be retrieved for a conversation.
     */
    public function test_messages_can_be_retrieved_for_conversation(): void
    {
        // Create an artist
        $artist = User::factory()->create(['role' => 'artist']);

        // Create a client and a conversation with the artist assigned
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'artist_id' => $artist->id,
            'status' => 'active',
            'last_message_at' => null // Prevent auto-creation of messages
        ]);

        // Create some messages
        $messages = [];
        for ($i = 0; $i < 5; $i++) {
            $messages[] = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $artist->id,
                'content' => "Test message {$i} from artist"
            ]);
        }

        // Retrieve the messages
        $retrievedMessages = $conversation->messages;

        // Check that all messages were retrieved
        $this->assertCount(5, $retrievedMessages);

        // Check that the specific messages we created are in the retrieved messages
        foreach ($messages as $message) {
            $this->assertTrue($retrievedMessages->contains('id', $message->id));
        }
    }

    /**
     * Test that a conversation can be marked as completed.
     */
    public function test_conversation_can_be_marked_as_completed(): void
    {
        // Create an artist
        $artist = User::factory()->create(['role' => 'artist']);

        // Create a client and a conversation with the artist assigned
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'artist_id' => $artist->id,
            'status' => 'active'
        ]);

        // Mark the conversation as completed
        $conversation->status = 'completed';
        $conversation->save();

        // Check that the conversation was updated in the database
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'completed'
        ]);
    }
}
