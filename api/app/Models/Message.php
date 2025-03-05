<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Message Model
 * 
 * Represents a message within a conversation.
 * Messages can be sent by either a user (artist/admin) or a client.
 */
class Message extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'user_id',
        'client_id',
        'content',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the conversation that owns the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user that sent the message (if any).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client that sent the message (if any).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Determine if the message was sent by a user.
     *
     * @return bool
     */
    public function isSentByUser(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Determine if the message was sent by a client.
     *
     * @return bool
     */
    public function isSentByClient(): bool
    {
        return $this->client_id !== null;
    }

    /**
     * Determine if the message has been read.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark the message as read.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if (!$this->isRead()) {
            $this->read_at = now();
            return $this->save();
        }

        return true;
    }

    /**
     * Get the sender name (either user or client).
     *
     * @return string
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->isSentByUser() && $this->user) {
            return $this->user->name;
        }

        if ($this->isSentByClient() && $this->client) {
            return $this->client->name;
        }

        return 'System';
    }
}
