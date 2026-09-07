<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->company(),
            'company_id' => null,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'lead_source_id' => null,
            'lead_status_id' => null,
            'score' => fake()->numberBetween(10, 100),
            'owner_id' => User::factory(),
            'notes' => fake()->paragraph(),
        ];
    }
}
