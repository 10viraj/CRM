<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'priority' => $this->priority,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'completed_at' => $this->completed_at?->toISOString(),
            'assign_to_id' => $this->assign_to_id,
            'assignee' => $this->whenLoaded('assignee', function () {
                return $this->assignee ? [
                    'id' => $this->assignee->id,
                    'name' => $this->assignee->name,
                    'email' => $this->assignee->email,
                ] : null;
            }),
            'creator_id' => $this->creator_id,
            'creator' => $this->whenLoaded('creator', function () {
                return $this->creator ? [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ] : null;
            }),
            'related_to_type' => $this->related_to_type,
            'related_to_id' => $this->related_to_id,
            'related_to' => $this->whenLoaded('relatedTo', function () {
                if (!$this->relatedTo) return null;
                return [
                    'id' => $this->relatedTo->id,
                    'type' => class_basename($this->relatedTo),
                    'name' => $this->relatedTo->name ?? $this->relatedTo->title ?? ('Record #' . $this->relatedTo->id),
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
