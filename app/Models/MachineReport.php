<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineReport extends Model
{
    /** @use HasFactory<\Database\Factories\MachineReportFactory> */
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'batch_id',
        'reported_at',
        'received_at',
        'payload_json',
        'raw_body',
        'signature',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'received_at' => 'datetime',
            'payload_json' => 'array',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
