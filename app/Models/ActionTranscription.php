<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionTranscription extends Model
{
    protected $fillable = [
        'action_id',
        'customer_file_id',
        'requested_by',
        'status',
        'model',
        'language',
        'duration_seconds',
        'transcript_text',
        'error_message',
    ];

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    public function customerFile(): BelongsTo
    {
        return $this->belongsTo(CustomerFile::class);
    }
}
