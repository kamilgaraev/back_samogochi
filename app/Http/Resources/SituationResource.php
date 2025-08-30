<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SituationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => [
                'value' => $this->category->value,
                'label' => $this->category->getLabel(),
                'description' => $this->category->getDescription(),
                'icon' => $this->category->getIcon(),
            ],
            'difficulty' => [
                'level' => $this->difficulty_level->value,
                'label' => $this->difficulty_level->getLabel(),
                'description' => $this->difficulty_level->getDescription(),
                'color' => $this->difficulty_level->getColor(),
                'icon' => $this->difficulty_level->getIcon(),
            ],
            'min_level_required' => $this->min_level_required,
            'stress_impact' => $this->stress_impact,
            'experience_reward' => $this->experience_reward,
            'is_active' => $this->is_active,
            'options' => SituationOptionResource::collection($this->whenLoaded('options')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
