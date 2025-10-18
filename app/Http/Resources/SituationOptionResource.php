<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SituationOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'stress_change' => $this->stress_change,
            'experience_reward' => $this->experience_reward,
            'energy_cost' => $this->energy_cost,
            'min_level_required' => $this->min_level_required,
            'is_available' => $this->is_available,
            'order' => $this->order,
            'effects' => [
                'stress_effect' => $this->getStressEffect(),
                'energy_effect' => $this->getEnergyEffect(),
                'experience_effect' => $this->getExperienceEffect(),
            ],
        ];
    }

    private function getStressEffect(): array
    {
        $change = $this->stress_change;
        
        if ($change < 0) {
            return [
                'type' => 'decrease',
                'label' => 'Снижает стресс',
                'value' => abs($change),
                'color' => '#4CAF50'
            ];
        } elseif ($change > 0) {
            return [
                'type' => 'increase', 
                'label' => 'Повышает стресс',
                'value' => $change,
                'color' => '#F44336'
            ];
        } else {
            return [
                'type' => 'neutral',
                'label' => 'Не влияет на стресс',
                'value' => 0,
                'color' => '#9E9E9E'
            ];
        }
    }

    private function getEnergyEffect(): array
    {
        $cost = $this->energy_cost;
        
        if ($cost > 0) {
            return [
                'type' => 'cost',
                'label' => 'Требует энергии',
                'value' => $cost,
                'color' => '#FF9800'
            ];
        } else {
            return [
                'type' => 'free',
                'label' => 'Не требует энергии',
                'value' => 0,
                'color' => '#4CAF50'
            ];
        }
    }

    private function getExperienceEffect(): array
    {
        $reward = $this->experience_reward;
        
        if ($reward >= 25) {
            return [
                'type' => 'high',
                'label' => 'Много опыта',
                'value' => $reward,
                'color' => '#4CAF50'
            ];
        } elseif ($reward >= 15) {
            return [
                'type' => 'medium',
                'label' => 'Средний опыт',
                'value' => $reward,
                'color' => '#2196F3'
            ];
        } else {
            return [
                'type' => 'low',
                'label' => 'Мало опыта',
                'value' => $reward,
                'color' => '#9E9E9E'
            ];
        }
    }
}
