<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MachineReport>
 */
class MachineReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $payload = [
            'batch_id' => fake()->uuid(),
            'reported_at' => now()->toIso8601String(),
            'reports' => [],
        ];

        return [
            'machine_id' => Machine::factory(),
            'batch_id' => $payload['batch_id'],
            'reported_at' => now(),
            'received_at' => now(),
            'payload_json' => $payload,
            'raw_body' => json_encode($payload, JSON_THROW_ON_ERROR),
            'signature' => null,
        ];
    }
}
