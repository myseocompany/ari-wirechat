<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MessageSource extends Model
{
    use HasFactory;

    protected $casts= ['settings'=>'array'];
    protected $fillable = ['type', 'is_default', 'APIKEY', 'settings'];


    public function getEndPoint(){
        return json_decode($this->settings)->webhook_url;
        
    }

    public function userMessageSources()
    {
        return $this->hasMany(UserMessageSource::class, 'message_source_id');
    }
    


    // Relación muchos a muchos con usuarios
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_message_sources')
                    ->withPivot('is_active', 'is_default');
    }
}
