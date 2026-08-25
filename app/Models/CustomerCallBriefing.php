<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCallBriefing extends Model
{
    protected $primaryKey = 'customer_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'customer_id',
        'status',
        'summary',
        'known_facts',
        'conflicting_facts',
        'avoid_asking',
        'suggested_opening',
        'next_question',
        'source_hash',
        'requested_by_user_id',
        'requested_at',
        'generated_at',
        'model',
        'prompt_version',
        'prompt_hash',
        'error_code',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'known_facts' => 'array',
            'conflicting_facts' => 'array',
            'avoid_asking' => 'array',
            'requested_at' => 'datetime',
            'generated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
