<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AriCrmMirrorDelivery extends Model
{
    /** @use HasFactory<\Database\Factories\AriCrmMirrorDeliveryFactory> */
    use HasFactory;

    protected $fillable = ['integration_id', 'delivery_id', 'event', 'aricrm_message_id', 'status', 'attempts', 'last_error', 'payload', 'wire_message_id', 'processed_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'processed_at' => 'datetime'];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(AriCrmMirrorIntegration::class);
    }
}
