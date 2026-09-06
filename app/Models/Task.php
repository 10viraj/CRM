<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'related_to_type',
        'related_to_id',
        'type',
        'priority',
        'due_date',
        'assign_to_id',
        'status',
    ];

    public function relatedTo()
    {
        return $this->morphTo();
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assign_to_id');
    }
}
