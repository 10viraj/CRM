<?php

namespace Database\Factories;

use App\Models\DealStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DealStage>
 */
class DealStageFactory extends Factory
{
    protected $model = DealStage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'color' => fake()->safeColorName(),
            'order_index' => fake()->numberBetween(1, 10),
        ];
    }
}
