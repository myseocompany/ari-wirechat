<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MachineCustomerHistory>
 */
class MachineCustomerHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startAt = now()->subDays(fake()->numberBetween(1, 120));

        return [
            'machine_id' => Machine::factory(),
            'customer_id' => 1,
            'start_at' => $startAt,
            'end_at' => null,
        ];
    }
}
