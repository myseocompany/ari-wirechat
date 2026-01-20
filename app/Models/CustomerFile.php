<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerFile extends Model
{
    protected $fillable = [
        'customer_id',
        'url',
        'name',
        'creator_user_id',
        'uuid',
        'filename',
        'size',
        'mime_type',
    ];

    protected static function booted(): void
    {
        static::created(function (CustomerFile $file): void {
            $userId = Auth::id();

            if (! $userId) {
                return;
            }

            ActivityLog::create([
                'user_id' => $userId,
                'action' => 'customer_file.uploaded',
                'subject_type' => self::class,
                'subject_id' => $file->id,
                'meta' => [
                    'customer_id' => $file->customer_id,
                    'name' => $file->name,
                ],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id')
            ->withDefault(['name' => 'Sin usuario']);
    }

    /* ====== Helpers internos ====== */
    protected function disk()
    {
        return Storage::disk('spaces');
    }

    protected function isPrivate(): bool
    {
        // Si en config/filesystems.php dejaste 'visibility' => 'public' en el disco spaces,
        // esto devolverá false (público). Cambia la lógica si manejas otro flag.
        return config('filesystems.disks.spaces.visibility', 'public') !== 'public';
    }

    /* ====== Clave (prefijo/ruta) en el bucket ====== */
    public function getStorageKeyAttribute(): string
    {
        return "files/{$this->customer_id}/{$this->url}";
    }

    /* ====== Compatibilidad con tu código previo ====== */

    // Antes era una ruta física en el servidor. Ahora devolvemos la "key" del objeto en el bucket.
    public function getFullPathAttribute(): string
    {
        return $this->storage_key; // compat: ya no hay path local
    }

    // Antes era "/public/files/...". Ahora devuelve URL pública o la ruta de descarga firmada.
    public function getWebPathAttribute(): string
    {
        if ($this->isPrivate()) {
            // Asume que definiste la route customer_files.download (redirige a temporaryUrl)
            return route('customer_files.download', $this);
        }

        return $this->public_url ?? '#';
    }

    /* ====== Atributos calculados ====== */

    public function getPublicUrlAttribute(): ?string
    {
        try {
            return $this->disk()->url($this->storage_key); // respeta AWS_URL/CDN
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getStatusAttribute(): string
    {
        try {
            return $this->disk()->exists($this->storage_key) ? 'OK' : 'MISSING';
        } catch (\Throwable $e) {
            return 'MISSING';
        }
    }

    public function getExtAttribute(): string
    {
        return strtolower(pathinfo($this->url, PATHINFO_EXTENSION));
    }

    public function getSizeHumanAttribute(): ?string
    {
        try {
            $bytes = $this->disk()->size($this->storage_key); // Flysystem 3
        } catch (\Throwable $e) {
            return null;
        }
        if ($bytes === null) {
            return null;
        }

        $u = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($u) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$u[$i];
    }
}
