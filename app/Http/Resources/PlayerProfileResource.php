<?php

namespace App\Http\Resources;

use App\Enums\StressLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stressLevel = StressLevel::fromValue($this->stress);
        $maxEnergy = \App\Services\GameConfigService::getMaxEnergy();
        
        $experienceInCurrentLevel = \App\Services\GameConfigService::getExperienceInCurrentLevel($this->total_experience, $this->level);
        $experienceToNextLevel = \App\Services\GameConfigService::getExperienceToNextLevel($this->total_experience, $this->level);
        
        $nextLevelExp = \App\Services\GameConfigService::getExperienceForLevel($this->level + 1);
        $currentLevelExp = \App\Services\GameConfigService::getExperienceForLevel($this->level);
        $expForThisLevel = $nextLevelExp - $currentLevelExp;
        
        $progressPercentage = $expForThisLevel > 0 
            ? ($experienceInCurrentLevel / $expForThisLevel) * 100 
            : 0;
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'level' => $this->level,
            'total_experience' => $this->total_experience,
            'experience_progress' => [
                'current_level_experience' => $experienceInCurrentLevel,
                'experience_to_next_level' => $experienceToNextLevel,
                'progress_percentage' => (int) min(100, max(0, round($progressPercentage))),
            ],
            'energy' => [
                'current' => $this->energy,
                'max' => $maxEnergy,
                'percentage' => ($this->energy / $maxEnergy) * 100,
                'status' => $this->getEnergyStatus($maxEnergy),
            ],
            'stress' => [
                'current' => $this->stress,
                'level' => [
                    'value' => $stressLevel->value,
                    'label' => $stressLevel->getLabel(),
                    'description' => $stressLevel->getDescription(),
                    'color' => $stressLevel->getColor(),
                    'recommended_actions' => $stressLevel->getRecommendedActions(),
                ],
                'min_range' => $stressLevel->getMinValue(),
                'max_range' => $stressLevel->getMaxValue(),
            ],
            'anxiety' => $this->anxiety,
            'last_login' => $this->last_login,
            'consecutive_days' => $this->consecutive_days,
            'can_receive_daily_reward' => $this->canReceiveDailyReward(),
            'personal_info' => [
                'favorite_song' => $this->favorite_song,
                'favorite_movie' => $this->favorite_movie,
                'favorite_book' => $this->favorite_book,
                'favorite_dish' => $this->favorite_dish,
                'best_friend_name' => $this->best_friend_name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getEnergyStatus(int $maxEnergy): array
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
}
