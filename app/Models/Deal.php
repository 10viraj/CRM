<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCustomFields;
use Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Deal extends Model
{
    /** @use HasFactory<DealFactory> */
    use HasFactory, HasCustomFields, Auditable;

    protected $fillable = [
        'name',
        'value',
        'probability',
        'deal_stage_id',
        'status',
        'lead_id',
        'company_id',
        'contact_id',
        'owner_id',
        'close_date',
        'notes',
    ];

    protected $appends = ['title', 'company_name'];

    protected $casts = [
        'close_date' => 'date',
        'value' => 'decimal:2',
        'probability' => 'integer',
    ];

    public function getTitleAttribute(): string
    {
        return $this->name;
    }

    public function getCompanyNameAttribute(): ?string
    {
        if ($this->company) {
            return $this->company->name;
        }
        return $this->lead ? ($this->lead->company ?: $this->lead->company_name) : null;
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(DealStage::class, 'deal_stage_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'related_to');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function calendarEvents(): MorphMany
    {
        return $this->morphMany(CalendarEvent::class, 'eventable');
    }
}
