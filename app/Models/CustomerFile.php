<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class CustomerFile extends Model
{
    // Si prefieres que estos atributos aparezcan al serializar:
    // protected $appends = ['status', 'web_path', 'ext', 'size_human'];

    /** Campos que se pueden asignar en masa (create / update). */
    protected $fillable = [
        'customer_id',
        'url',
        'creator_user_id',
        'uuid',
        'filename',
        'size',
        'mime_type',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id')
                    ->withDefault(['name' => 'Sin usuario']);
    }

    // ----- Helpers de ruta -----
    public function getFullPathAttribute(): string
    {
        // Ajusta si tu carpeta real cambia.
        // Si tus archivos están en /public/public/files/{customer_id}/...
        return public_path("public/files/{$this->customer_id}/{$this->url}");
        // Si usas storage: return storage_path("app/public/files/{$this->customer_id}/{$this->url}");
    }

    public function getWebPathAttribute(): string
    {
        return "/public/files/{$this->customer_id}/{$this->url}";
        // Si usas storage + symlink (storage:link): return "/storage/files/{$this->customer_id}/{$this->url}";
    }

    // ----- Atributos calculados -----
    public function getStatusAttribute(): string
    {
        return File::exists($this->full_path) ? 'OK' : 'MISSING';
    }

    public function getExtAttribute(): string
    {
        return strtolower(pathinfo($this->url, PATHINFO_EXTENSION));
    }

    public function getSizeHumanAttribute(): ?string
    {
        if (!File::exists($this->full_path)) return null;
        $bytes = File::size($this->full_path);
        $u = ['B','KB','MB','GB','TB']; $i = 0;
        while ($bytes >= 1024 && $i < count($u) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 2).' '.$u[$i];
    }
}
