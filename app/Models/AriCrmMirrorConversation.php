<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Namu\WireChat\Models\Conversation;

class AriCrmMirrorConversation extends Model
{
    protected $fillable = ['integration_id', 'aricrm_conversation_id', 'conversation_id'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
