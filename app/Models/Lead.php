<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'company', 'email', 'phone',
        'lead_source_id', 'lead_status_id', 'score', 'owner_id', 'notes'
    ];

    protected $appends = ['name', 'company_name'];

    public function getNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getCompanyNameAttribute()
    {
        return $this->company ?: 'Acme Corp';
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'relatedTo');
    }
}
