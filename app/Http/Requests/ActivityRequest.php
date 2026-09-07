<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:call,email,meeting,note,task,status_change,sms',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:completed,pending,scheduled',
            'subject_type' => 'nullable|string',
            'subject_id' => 'nullable|integer',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}
