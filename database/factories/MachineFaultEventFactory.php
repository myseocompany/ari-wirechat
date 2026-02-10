<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MachineFaultEvent>
 */
class MachineFaultEventFactory extends Factory
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
            'fault_code' => strtoupper(fake()->bothify('F-###')),
            'severity' => fake()->randomElement(['info', 'low', 'medium', 'high']),
            'reported_at' => now(),
            'metadata' => [
                'note' => fake()->sentence(),
            ],
        ];
    }
}
