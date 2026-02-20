<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Namu\WireChat\Models\Conversation;

class MessageSourceConversation extends Model
{
    protected $fillable = [
        'message_source_id',
        'customer_id',
        'conversation_id',
    ];

    public function messageSource(): BelongsTo
    {
        return $this->belongsTo(MessageSource::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
