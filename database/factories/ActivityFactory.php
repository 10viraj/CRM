<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['call', 'email', 'meeting', 'note', 'task', 'status_change', 'sms']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'activity_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60]),
            'status' => fake()->randomElement(['completed', 'pending']),
            'subject_type' => null,
            'subject_id' => null,
        ];
    }
}
