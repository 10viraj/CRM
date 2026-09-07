<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'company' => $this->company,
            'company_name' => $this->company_name,
            'company_id' => $this->company_id,
            'company_model' => $this->whenLoaded('companyModel', function () {
                return $this->companyModel ? [
                    'id' => $this->companyModel->id,
                    'name' => $this->companyModel->name,
                ] : null;
            }),
            'email' => $this->email,
            'phone' => $this->phone,
            'lead_source_id' => $this->lead_source_id,
            'source' => $this->whenLoaded('source', function () {
                return $this->source ? [
                    'id' => $this->source->id,
                    'name' => $this->source->name,
                ] : null;
            }),
            'lead_status_id' => $this->lead_status_id,
            'status' => $this->whenLoaded('status', function () {
                return $this->status ? [
                    'id' => $this->status->id,
                    'name' => $this->status->name,
                    'color' => $this->status->color,
                ] : null;
            }),
            'score' => $this->score,
            'owner_id' => $this->owner_id,
            'owner' => $this->whenLoaded('owner', function () {
                return $this->owner ? [
                    'id' => $this->owner->id,
                    'name' => $this->owner->name,
                    'email' => $this->owner->email,
                ] : null;
            }),
            'notes' => $this->notes,
            'custom_field_values' => $this->whenLoaded('customFieldValues', function () {
                return $this->customFieldValues->map(function ($val) {
                    return [
                        'id' => $val->id,
                        'field_id' => $val->custom_field_id,
                        'name' => $val->field ? $val->field->name : null,
                        'label' => $val->field ? $val->field->label : null,
                        'value' => $val->value,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
