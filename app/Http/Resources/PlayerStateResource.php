<?php

namespace App\Http\Resources;

use App\Enums\StressLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerStateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stressLevel = StressLevel::fromValue($this->stress);
        $maxEnergy = $this->getMaxEnergy();
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'level' => $this->level,
            'experience' => [
                'total' => $this->total_experience,
                'current_level' => $this->total_experience % 100,
                'to_next_level' => 100 - ($this->total_experience % 100),
                'progress_percentage' => round((($this->total_experience % 100) / 100) * 100, 1),
            ],
            'energy' => [
                'current' => $this->energy,
                'max' => $maxEnergy,
                'percentage' => round(($this->energy / $maxEnergy) * 100, 1),
                'status' => $this->getEnergyStatus($maxEnergy),
            ],
            'stress' => [
                'current' => $this->stress,
                'percentage' => round($this->stress, 1),
                'level' => [
                    'value' => $stressLevel->value,
                    'label' => $stressLevel->getLabel(),
                    'color' => $stressLevel->getColor(),
                    'recommended_actions' => $stressLevel->getRecommendedActions(),
                ],
            ],
            'anxiety' => [
                'current' => $this->anxiety,
                'percentage' => round($this->anxiety, 1),
            ],
            'activity' => [
                'last_login' => $this->last_login,
                'consecutive_days' => $this->consecutive_days,
                'can_claim_daily_reward' => true,
            ],
            'sleep' => $this->getSleepInfo(),
            'capabilities' => [
                'can_perform_micro_actions' => $this->canPerformMicroActions(),
                'can_start_situations' => $this->canStartSituations(),
            ],
            'customization' => [
                'new_customizables' => $this->getNewCustomizables(),
            ],
            'timestamps' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }

    private function getNewCustomizables(): array
    {
        $customizationService = app(\App\Services\CustomizationService::class);
        return $customizationService->getNewUnlockedItems($this->id);
    }

    private function getEnergyStatus(int $maxEnergy = 200): array
    {
        $percentage = ($this->energy / $maxEnergy) * 100;
        
        if ($percentage >= 75) {
            return [
                'level' => 'high',
                'label' => 'Высокий уровень энергии',
                'color' => '#4CAF50'
            ];
        } elseif ($percentage >= 50) {
            return [
                'level' => 'medium',
                'label' => 'Нормальный уровень энергии',
                'color' => '#2196F3'
            ];
        } elseif ($percentage >= 25) {
            return [
                'level' => 'low',
                'label' => 'Низкий уровень энергии',
                'color' => '#FF9800'
            ];
        } else {
            return [
                'level' => 'critical',
                'label' => 'Критически низкий уровень энергии',
                'color' => '#F44336'
            ];
        }
    }

    private function canPerformMicroActions(): bool
    {
        return $this->energy >= 10;
    }

    private function canStartSituations(): bool
    {
        return $this->energy >= 20 && $this->stress <= 80;
    }

    private function getMaxEnergy(): int
    {
        $gameBalance = \App\Models\GameConfig::getGameBalance();
        return $gameBalance['max_energy'] ?? 200;
    }
}
