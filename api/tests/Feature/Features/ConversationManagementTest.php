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
     * Test that a client can create a new conversation.
     */
    public function test_client_can_create_conversation(): void
    {
        // Create a client
        $client = Client::factory()->create();

        // Simulate a request to create a conversation
        $response = $this->actingAs($client->user)
            ->postJson('/api/conversations', [
                'title' => 'Test Conversation',
                'message' => 'Initial message from client'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'status',
                'client_id',
                'user_id',
                'created_at',
                'updated_at'
            ]);

        // Check that the conversation was created in the database
        $this->assertDatabaseHas('conversations', [
            'title' => 'Test Conversation',
            'client_id' => $client->id,
            'status' => 'new'
        ]);

        // Check that the initial message was created
        $conversationId = $response->json('id');
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversationId,
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

        // Simulate a request to assign the artist to the conversation
        $response = $this->actingAs($artist)
            ->putJson("/api/conversations/{$conversation->id}/assign", [
                'user_id' => $artist->id
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'active',
                'user_id' => $artist->id
            ]);

        // Check that the conversation was updated in the database
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'user_id' => $artist->id,
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
            'user_id' => $artist->id,
            'status' => 'active'
        ]);

        // Simulate a request to send a message
        $response = $this->actingAs($artist)
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'content' => 'Test message from artist'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'conversation_id',
                'user_id',
                'client_id',
                'content',
                'is_read',
                'created_at',
                'updated_at'
            ]);

        // Check that the message was created in the database
        $this->assertDatabaseHas('messages', [
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

        // Simulate a request to send a message
        $response = $this->actingAs($client->user)
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'content' => 'Test message from client'
            ]);

        $response->assertStatus(201);

        // Check that the message was created in the database
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'client_id' => $client->id,
            'content' => 'Test message from client'
        ]);
    }

    /**
     * Test that a user can retrieve all messages from a conversation.
     */
    public function test_user_can_retrieve_conversation_messages(): void
    {
        // Create an artist
        $artist = User::factory()->create(['role' => 'artist']);

        // Create a client and a conversation with the artist assigned
        $client = Client::factory()->create();
        $conversation = Conversation::factory()->create([
            'client_id' => $client->id,
            'user_id' => $artist->id,
            'status' => 'active'
        ]);

        // Create some messages
        $messages = Message::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $artist->id
        ]);

        // Simulate a request to retrieve messages
        $response = $this->actingAs($artist)
            ->getJson("/api/conversations/{$conversation->id}/messages");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
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
            'user_id' => $artist->id,
            'status' => 'active'
        ]);

        // Simulate a request to mark the conversation as completed
        $response = $this->actingAs($artist)
            ->putJson("/api/conversations/{$conversation->id}/status", [
                'status' => 'completed'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'completed'
            ]);

        // Check that the conversation was updated in the database
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'completed'
        ]);
    }
}
