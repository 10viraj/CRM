<?php

namespace Database\Factories;

use App\Models\Deal;
use App\Models\DealStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        return [
            'name' => fake()->catchPhrase() . ' Deal',
            'value' => fake()->randomFloat(2, 5000, 150000),
            'probability' => fake()->numberBetween(10, 100),
            'deal_stage_id' => DealStage::factory(),
            'status' => 'open',
            'lead_id' => null,
            'company_id' => null,
            'contact_id' => null,
            'owner_id' => User::factory(),
            'close_date' => fake()->dateTimeBetween('now', '+3 months'),
            'notes' => fake()->paragraph(),
        ];
    }
}
