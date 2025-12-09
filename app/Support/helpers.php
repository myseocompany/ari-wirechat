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
