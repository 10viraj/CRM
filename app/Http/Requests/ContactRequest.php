<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        if ($user->hasRole(['Admin', 'Manager'])) return true;

        if ($this->isMethod('POST')) {
            return $user->hasPermissionTo('create-contacts');
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return $user->hasPermissionTo('edit-contacts');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'owner_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ];
    }
}
