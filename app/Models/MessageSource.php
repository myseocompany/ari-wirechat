<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Namu\WireChat\Traits\Chatable;

class MessageSource extends Model
{
    use Chatable;
    use HasFactory;

    protected $casts = [
        'settings' => 'array',
        'is_default' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'type',
        'phone_number',
        'api_url',
        'api_token',
        'is_default',
        'APIKEY',
        'settings',
    ];

    public function canCreateGroups(): bool
    {
        return true;
    }

    public function canCreateChats(): bool
    {
        return true;
    }

    public function getEndPoint(): ?string
    {
        return $this->settings['webhook_url'] ?? $this->api_url;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_message_sources')
            ->withPivot('is_active', 'is_default');
    }

    public function userMessageSources(): HasMany
    {
        return $this->hasMany(UserMessageSource::class, 'message_source_id');
    }

    public function messageSourceConversations(): HasMany
    {
        return $this->hasMany(MessageSourceConversation::class);
    }

    public static function getDefaultMessageSource(): ?self
    {
        return self::where('is_default', true)->first()
            ?? self::orderBy('id')->first(); // fallback por si no hay default explícito
    }

    public function isActive(): bool
    {
        return (bool) ($this->settings['active'] ?? true);
    }
}
