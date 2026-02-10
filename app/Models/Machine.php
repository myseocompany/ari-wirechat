<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    /** @use HasFactory<\Database\Factories\MachineFactory> */
    use HasFactory;

    protected $fillable = [
        'serial',
        'current_customer_id',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function currentCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'current_customer_id');
    }

    public function customerHistories(): HasMany
    {
        return $this->hasMany(MachineCustomerHistory::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(MachineToken::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MachineReport::class);
    }

    public function productionMinutes(): HasMany
    {
        return $this->hasMany(MachineProductionMinute::class);
    }

    public function faultEvents(): HasMany
    {
        return $this->hasMany(MachineFaultEvent::class);
    }
}
