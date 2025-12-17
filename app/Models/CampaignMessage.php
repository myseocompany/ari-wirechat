<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CampaignMessage extends Model{
	protected $table = 'campaign_messages';

    protected $fillable = [
        'campaign_id',
        'text',
        'component',
        'sequence',
        'source',
        'fallback',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
