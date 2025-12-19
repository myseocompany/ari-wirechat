<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Namu\WireChat\Models\Message;

class WhatsAppMessageMap extends Model
{
    protected $table = 'whatsapp_messages_map';

    protected $fillable = [
        'external_message_id',
        'wire_message_id',
        'wa_id',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function wireMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'wire_message_id');
    }
}
