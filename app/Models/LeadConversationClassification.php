<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Namu\WireChat\Models\Conversation;

class LeadConversationClassification extends Model
{
    public const STATUS_CALIFICADO = 'calificado';

    public const STATUS_NURTURING = 'nurturing';

    public const STATUS_NO_CALIFICADO = 'no_calificado';

    protected $fillable = [
        'conversation_id',
        'customer_id',
        'status',
        'score',
        'confidence',
        'signals_json',
        'reasons_json',
        'suggested_tag_id',
        'applied_tag_id',
        'applied_tag_at',
        'last_customer_message_at',
        'classified_at',
        'classifier_version',
        'prompt_version',
        'model',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'confidence' => 'decimal:4',
            'signals_json' => 'array',
            'reasons_json' => 'array',
            'applied_tag_at' => 'datetime',
            'last_customer_message_at' => 'datetime',
            'classified_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function suggestedTag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'suggested_tag_id');
    }

    public function appliedTag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'applied_tag_id');
    }
}
