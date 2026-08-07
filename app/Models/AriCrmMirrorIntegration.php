<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AriCrmMirrorIntegration extends Model
{
    /** @use HasFactory<\Database\Factories\AriCrmMirrorIntegrationFactory> */
    use HasFactory;

    protected $fillable = ['aricrm_tenant_id', 'message_source_id', 'secret_current', 'secret_previous', 'previous_secret_expires_at', 'is_active'];

    protected function casts(): array
    {
        return ['secret_current' => 'encrypted', 'secret_previous' => 'encrypted', 'previous_secret_expires_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function messageSource(): BelongsTo
    {
        return $this->belongsTo(MessageSource::class);
    }
}
