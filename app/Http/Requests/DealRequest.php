<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DealRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        if ($user->hasRole(['Admin', 'Manager'])) return true;

        if ($this->isMethod('POST')) {
            return $user->hasPermissionTo('create-deals');
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return $user->hasPermissionTo('edit-deals');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'deal_stage_id' => 'nullable|exists:deal_stages,id',
            'status' => 'nullable|string|in:open,won,lost',
            'lead_id' => 'nullable|exists:leads,id',
            'company_id' => 'nullable|exists:companies,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'owner_id' => 'nullable|exists:users,id',
            'close_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ];
    }
}
