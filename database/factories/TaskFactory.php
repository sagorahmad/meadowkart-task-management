<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),

            'type' => 'report_generation',

            'title' => fake()->sentence(),

            'payload' => [
                'month' => '2026-08'
            ],

            'status' => 'pending',

            'priority' => 'normal',

            'attempts' => 0,

            'started_at' => null,

            'completed_at' => null,

            'failed_at' => null,

            'error_message' => null,
        ];
    }
}
