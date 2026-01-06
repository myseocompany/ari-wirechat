<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement([
                'customer.deleted',
                'customer.created',
                'customer.updated',
                'order.created',
                'message.sent',
            ]),
            'subject_type' => Customer::class,
            'subject_id' => fake()->numberBetween(1, 100000),
            'meta' => [
                'note' => fake()->sentence(),
            ],
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
