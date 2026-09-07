<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => $this->start_time?->toISOString(),
            'end_time' => $this->end_time?->toISOString(),
            'is_all_day' => (bool) $this->is_all_day,
            'location' => $this->location,
            'event_type' => $this->event_type,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ] : null;
            }),
            'eventable_type' => $this->eventable_type,
            'eventable_id' => $this->eventable_id,
            'eventable' => $this->whenLoaded('eventable', function () {
                if (!$this->eventable) return null;
                return [
                    'id' => $this->eventable->id,
                    'type' => class_basename($this->eventable),
                    'name' => $this->eventable->name ?? $this->eventable->title ?? ('Record #' . $this->eventable->id),
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
