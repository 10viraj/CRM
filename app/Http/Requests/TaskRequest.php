<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        if ($user->hasRole(['Admin', 'Manager'])) return true;

        if ($this->isMethod('POST')) {
            return $user->hasPermissionTo('create-tasks');
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return $user->hasPermissionTo('edit-tasks');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'related_to_type' => 'nullable|string',
            'related_to_id' => 'nullable|integer',
            'type' => 'nullable|string|max:100',
            'priority' => 'nullable|string|in:Low,Medium,High,Urgent',
            'due_date' => 'nullable|date',
            'assign_to_id' => 'nullable|exists:users,id',
            'creator_id' => 'nullable|exists:users,id',
            'status' => 'nullable|string|in:Pending,In Progress,Completed,Cancelled',
            'completed_at' => 'nullable|date',
        ];
    }
}
