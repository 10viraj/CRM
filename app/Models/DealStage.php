<?php

namespace App\Models;

use Database\Factories\DealStageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealStage extends Model
{
    /** @use HasFactory<DealStageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'order_index',
        'probability',
        'is_won',
        'is_lost',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'probability' => 'integer',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
    ];

    public function getDisplayOrderAttribute()
    {
        return $this->order_index;
    }

    public function setDisplayOrderAttribute($value)
    {
        $this->attributes['order_index'] = $value;
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
