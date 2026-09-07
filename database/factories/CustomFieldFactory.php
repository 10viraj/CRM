<?php

namespace Database\Factories;

use App\Models\CustomField;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomField>
 */
class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();
        return [
            'model_type' => Lead::class,
            'name' => $name,
            'label' => ucfirst($name),
            'field_type' => 'text',
            'options' => null,
            'is_required' => false,
            'default_value' => null,
            'order_index' => fake()->numberBetween(1, 10),
        ];
    }
}
