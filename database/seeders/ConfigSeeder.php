<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    public function run(): void
    {
        Config::updateOrCreate(
            ['key' => 'followup_default_minutes'],
            [
                'value' => '15',
                'type' => 'integer',
            ]
        );
    }
}
