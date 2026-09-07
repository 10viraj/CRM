<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+1 hour');

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_time' => $start,
            'end_time' => $end,
            'is_all_day' => false,
            'location' => fake()->randomElement(['Zoom Meeting Room', 'Headquarters Room A', 'Phone Call', 'Client Office']),
            'event_type' => fake()->randomElement(['meeting', 'call', 'demo', 'task', 'webinar']),
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled']),
            'eventable_type' => null,
            'eventable_id' => null,
        ];
    }
}
