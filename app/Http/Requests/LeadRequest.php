<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        if ($user->hasRole(['Admin', 'Manager'])) return true;

        if ($this->isMethod('POST')) {
            return $user->hasPermissionTo('create-leads');
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return $user->hasPermissionTo('edit-leads');
        }

        return true;
    }

    public function rules(): array
    {
        $leadId = $this->route('lead') ? $this->route('lead')->id : null;

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:leads,email,' . $leadId,
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'lead_status_id' => 'nullable|exists:lead_statuses,id',
            'owner_id' => 'nullable|exists:users,id',
            'score' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ];
    }
}
