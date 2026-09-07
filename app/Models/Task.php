<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory, Auditable;

    protected $fillable = [
        'title',
        'description',
        'related_to_type',
        'related_to_id',
        'type',
        'priority',
        'due_date',
        'assign_to_id',
        'creator_id',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function relatedTo(): MorphTo
    {
        return $this->morphTo('related_to');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_to_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
