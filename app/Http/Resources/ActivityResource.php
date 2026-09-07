<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'activity_date' => $this->activity_date?->toISOString(),
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ] : null;
            }),
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', function () {
                if (!$this->subject) return null;
                return [
                    'id' => $this->subject->id,
                    'type' => class_basename($this->subject),
                    'name' => $this->subject->name ?? $this->subject->title ?? ('Record #' . $this->subject->id),
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
