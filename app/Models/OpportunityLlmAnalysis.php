<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityLlmAnalysis extends Model
{
    protected $fillable = [
        'customer_id',
        'input_hash',
        'llm_used',
        'llm_error',
        'llm_duration_ms',
        'model',
        'produce_empanadas',
        'estimated_daily_empanadas',
        'intent',
        'confidence',
        'evidence',
        'next_best_action',
        'recommended_channel',
        'recommended_sla',
        'action_reason',
        'suggested_message',
        'stop_condition',
        'analyzed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'llm_used' => 'boolean',
            'llm_duration_ms' => 'integer',
            'estimated_daily_empanadas' => 'integer',
            'confidence' => 'decimal:4',
            'analyzed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
