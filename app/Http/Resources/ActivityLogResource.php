<?php

namespace App\Http\Resources;

use App\Enums\ActivityEventType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $eventType = ActivityEventType::tryFrom($this->event_type);
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'event' => [
                'type' => $this->event_type,
                'label' => $eventType?->getLabel() ?? $this->event_type,
                'category' => $eventType?->getCategory() ?? 'unknown',
                'importance' => $eventType?->getImportance() ?? 'low',
            ],
            'event_data' => $this->event_data,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}
