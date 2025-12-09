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
            'boolean' => boolval($value),
            'json'    => $value !== null ? json_decode($value, true) : null,
            default   => $value,
        };
    }
}
