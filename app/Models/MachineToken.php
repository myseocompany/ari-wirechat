<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MachineToken extends Model
{
    /** @use HasFactory<\Database\Factories\MachineTokenFactory> */
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'token_hash',
        'last_used_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }

    public function markUsed(?Carbon $at = null): void
    {
        $this->forceFill([
            'last_used_at' => $at ?? now(),
        ])->save();
    }
}
