<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'value' => (float) $this->value,
            'probability' => $this->probability,
            'status' => $this->status,
            'close_date' => $this->close_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'deal_stage_id' => $this->deal_stage_id,
            'stage' => $this->whenLoaded('stage', function () {
                return $this->stage ? [
                    'id' => $this->stage->id,
                    'name' => $this->stage->name,
                    'color' => $this->stage->color,
                    'order_index' => $this->stage->order_index,
                ] : null;
            }),
            'lead_id' => $this->lead_id,
            'lead' => $this->whenLoaded('lead', function () {
                return $this->lead ? [
                    'id' => $this->lead->id,
                    'name' => $this->lead->name,
                    'email' => $this->lead->email,
                ] : null;
            }),
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', function () {
                return $this->company ? [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ] : null;
            }),
            'contact_id' => $this->contact_id,
            'contact' => $this->whenLoaded('contact', function () {
                return $this->contact ? [
                    'id' => $this->contact->id,
                    'name' => $this->contact->name,
                    'email' => $this->contact->email,
                ] : null;
            }),
            'owner_id' => $this->owner_id,
            'owner' => $this->whenLoaded('owner', function () {
                return $this->owner ? [
                    'id' => $this->owner->id,
                    'name' => $this->owner->name,
                    'email' => $this->owner->email,
                ] : null;
            }),
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
            'tasks' => $this->whenLoaded('tasks', function () {
                return $this->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'priority' => $task->priority,
                        'status' => $task->status,
                        'due_date' => $task->due_date?->toDateString(),
                    ];
                });
            }),
            'activities' => $this->whenLoaded('activities', function () {
                return $this->activities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'type' => $activity->type,
                        'title' => $activity->title,
                        'subject' => $activity->title ?? $activity->type,
                        'description' => $activity->description,
                        'user_id' => $activity->user_id,
                        'user_name' => $activity->user?->name,
                        'created_at' => $activity->created_at?->toISOString(),
                    ];
                });
            }),
            'calendar_events' => $this->whenLoaded('calendarEvents', function () {
                return $this->calendarEvents->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start_time' => $event->start_time?->toISOString(),
                        'end_time' => $event->end_time?->toISOString(),
                        'location' => $event->location,
                    ];
                });
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
