<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineProductionMinute extends Model
{
    /** @use HasFactory<\Database\Factories\MachineProductionMinuteFactory> */
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'minute_at',
        'tacometer_total',
        'units_in_minute',
        'is_backfill',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'minute_at' => 'datetime',
            'received_at' => 'datetime',
            'is_backfill' => 'boolean',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
