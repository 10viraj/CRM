<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealStage extends Model
{
    protected $fillable = ['name', 'color', 'order_index'];

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
