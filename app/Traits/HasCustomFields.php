<?php

namespace App\Traits;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomFields
{
    /**
     * Get all custom field values for this model.
     */
    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'custom_fieldable');
    }

    /**
     * Get value of a specific custom field by name.
     */
    public function getCustomFieldValue(string $fieldName): mixed
    {
        $morphClass = $this->getMorphClass();
        $staticClass = static::class;

        $field = CustomField::where(function ($query) use ($morphClass, $staticClass) {
            $query->where('model_type', $staticClass)
                ->orWhere('model_type', $morphClass);
        })
            ->where('name', $fieldName)
            ->first();

        if (!$field) {
            return null;
        }

        $valueRecord = $this->customFieldValues()
            ->where('custom_field_id', $field->id)
            ->first();

        return $valueRecord ? $valueRecord->value : $field->default_value;
    }

    /**
     * Set value of a specific custom field by name.
     */
    public function setCustomFieldValue(string $fieldName, mixed $value): ?CustomFieldValue
    {
        $morphClass = $this->getMorphClass();
        $staticClass = static::class;

        $field = CustomField::where(function ($query) use ($morphClass, $staticClass) {
            $query->where('model_type', $staticClass)
                ->orWhere('model_type', $morphClass);
        })
            ->where('name', $fieldName)
            ->first();

        if (!$field) {
            return null;
        }

        return $this->customFieldValues()->updateOrCreate(
            ['custom_field_id' => $field->id],
            ['value' => is_array($value) ? json_encode($value) : (string) $value]
        );
    }
}
