<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MachineToken>
 */
class MachineTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plainToken = fake()->sha256();

        return [
            'machine_id' => Machine::factory(),
            'token_hash' => hash('sha256', $plainToken),
            'last_used_at' => null,
            'revoked_at' => null,
        ];
    }
}
