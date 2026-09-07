<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'related_to_type' => null,
            'related_to_id' => null,
            'type' => fake()->randomElement(['Call', 'Email', 'Meeting', 'Follow-up', 'Review', 'Other']),
            'priority' => fake()->randomElement(['Low', 'Medium', 'High', 'Urgent']),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'assign_to_id' => User::factory(),
            'creator_id' => User::factory(),
            'status' => fake()->randomElement(['Pending', 'In Progress', 'Completed']),
            'completed_at' => null,
        ];
    }
}
