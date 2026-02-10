<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineFaultEvent extends Model
{
    /** @use HasFactory<\Database\Factories\MachineFaultEventFactory> */
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'fault_code',
        'severity',
        'reported_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
