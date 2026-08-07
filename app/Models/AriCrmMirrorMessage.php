<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AriCrmMirrorMessage extends Model
{
    protected $fillable = ['integration_id', 'aricrm_message_id', 'wire_message_id', 'wa_message_id', 'origin', 'author_type', 'author_id', 'author_name', 'sent_at', 'occurred_at', 'media_status', 'metadata'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime', 'occurred_at' => 'datetime', 'metadata' => 'array'];
    }
}
