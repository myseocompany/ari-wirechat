<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelsWebhookLog extends Model
{
    protected $fillable = [
        'payload',
        'phone',
        'agent_id',
        'payload_raw',
        'headers',
        'ip',
        'method',
        'route',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
        ];
    }
}
