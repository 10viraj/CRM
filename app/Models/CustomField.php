<?php

namespace App\Models;

use Database\Factories\CustomFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    /** @use HasFactory<CustomFieldFactory> */
    use HasFactory;

    protected $fillable = [
        'model_type',
        'name',
        'label',
        'field_type',
        'options',
        'is_required',
        'default_value',
        'order_index',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'order_index' => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
