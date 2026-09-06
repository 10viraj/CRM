<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    protected $fillable = ['name', 'color'];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'lead_status_id');
    }
}
