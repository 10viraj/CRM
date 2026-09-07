<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        if ($user->hasRole(['Admin', 'Manager'])) return true;

        if ($this->isMethod('POST')) {
            return $user->hasPermissionTo('create-calendar');
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return $user->hasPermissionTo('edit-calendar');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'is_all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'event_type' => 'nullable|string|in:meeting,call,demo,task,webinar,other',
            'status' => 'nullable|string|in:scheduled,completed,cancelled',
            'user_id' => 'nullable|exists:users,id',
            'eventable_type' => 'nullable|string',
            'eventable_id' => 'nullable|integer',
        ];
    }
}
