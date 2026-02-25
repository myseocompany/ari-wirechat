<?php

use App\Models\Config;

if (! function_exists('app_config')) {
    /**
     * Get an application config value stored in configs table.
     */
    function app_config(string $key, $default = null)
    {
        return Config::getValue($key, $default);
    }
}

if (! function_exists('app_feature_enabled')) {
    /**
     * Resolve a boolean feature flag stored in configs table.
     */
    function app_feature_enabled(string $key, bool $default = true): bool
    {
        static $resolved = [];

        if (array_key_exists($key, $resolved)) {
            return $resolved[$key];
        }

        try {
            $rawValue = app_config($key);
        } catch (\Throwable) {
            $rawValue = null;
        }

        if ($rawValue === null) {
            $resolved[$key] = $default;

            return $default;
        }

        if (is_bool($rawValue)) {
            $resolved[$key] = $rawValue;

            return $rawValue;
        }

        if (is_int($rawValue) || is_float($rawValue)) {
            $enabled = ((int) $rawValue) === 1;
            $resolved[$key] = $enabled;

            return $enabled;
        }

        $normalized = strtolower(trim((string) $rawValue));

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            $resolved[$key] = true;

            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            $resolved[$key] = false;

            return false;
        }

        $resolved[$key] = $default;

        return $default;
    }
}
