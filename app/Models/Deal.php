<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'value', 'deal_stage_id', 'lead_id', 'owner_id', 'close_date'];

    protected $appends = ['title', 'company_name'];

    protected $casts = [
        'close_date' => 'date',
    ];

    public function getTitleAttribute()
    {
        return $this->name;
    }

    public function getCompanyNameAttribute()
    {
        return $this->lead ? ($this->lead->company ?: $this->lead->company_name) : null;
    }

    public function stage()
    {
        return $this->belongsTo(DealStage::class, 'deal_stage_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
