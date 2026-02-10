<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MachineProductionMinute>
 */
class MachineProductionMinuteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'machine_id' => Machine::factory(),
            'minute_at' => now()->startOfMinute(),
            'tacometer_total' => fake()->numberBetween(1000, 100000),
            'units_in_minute' => fake()->numberBetween(0, 500),
            'is_backfill' => false,
            'received_at' => now(),
        ];
    }
}
