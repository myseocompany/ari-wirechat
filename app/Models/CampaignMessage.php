<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CampaignMessage extends Model{
	protected $table = 'campaign_messages';

    protected $fillable = [
        'campaign_id',
        'text',
        'template_name',
        'template_language',
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
