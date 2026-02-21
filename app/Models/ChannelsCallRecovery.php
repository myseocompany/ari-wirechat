<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelsCallRecovery extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_RECOVERED = 'recovered';

    public const STATUS_NO_RECORDING = 'no_recording';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'call_id',
        'call_created_at',
        'msisdn',
        'agent_id',
        'recording_exists',
        'recording_url',
        'status',
        'queued_at',
        'processed_at',
        'recovered_at',
        'local_file_path',
        'local_file_size',
        'error',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'call_created_at' => 'datetime',
            'recording_exists' => 'boolean',
            'queued_at' => 'datetime',
            'processed_at' => 'datetime',
            'recovered_at' => 'datetime',
            'local_file_size' => 'integer',
            'payload' => 'array',
        ];
    }
}
