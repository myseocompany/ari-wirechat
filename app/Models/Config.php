<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    /**
     * Get a config value by key, interpreted by its type.
     */
    public static function getValue(string $key, $default = null)
    {
        $config = static::query()->where('key', $key)->first();

        if (! $config) {
            return $default;
        }

        $value = $config->value;

        return match ($config->type) {
            'integer' => intval($value),
            'boolean' => self::toBoolean($value, $default),
            'json' => $value !== null ? json_decode($value, true) : null,
            default => $value,
        };
    }

    private static function toBoolean(mixed $value, mixed $default = null): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 1;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
            return false;
        }

        return (bool) $default;
    }
}
