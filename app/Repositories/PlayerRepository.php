<?php

namespace App\Repositories;

use App\Models\PlayerProfile;
use App\Models\PlayerSituation;
use App\Models\PlayerMicroAction;
use Illuminate\Database\Eloquent\Collection;

class PlayerRepository
{
    public function findByUserId(int $userId): ?PlayerProfile
    {
        return PlayerProfile::where('user_id', $userId)->first();
    }

    public function findById(int $id): ?PlayerProfile
    {
        return PlayerProfile::find($id);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        return PlayerProfile::where('user_id', $userId)->update($data);
    }

    public function getPlayerStats(int $playerId): array
    {
        $player = $this->findById($playerId);
        if (!$player) {
            return [];
        }

        $totalSituationsCompleted = PlayerSituation::where('player_id', $playerId)
            ->whereNotNull('completed_at')
            ->count();

        $totalMicroActionsPerformed = PlayerMicroAction::where('player_id', $playerId)
            ->count();

        $todaySituations = PlayerSituation::where('player_id', $playerId)
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', today())
            ->count();

        $todayMicroActions = PlayerMicroAction::where('player_id', $playerId)
            ->whereDate('completed_at', today())
            ->count();

        $averageStressThisWeek = PlayerProfile::where('id', $playerId)
            ->where('updated_at', '>=', now()->subWeek())
            ->avg('stress') ?? $player->stress;

        $levelProgress = $this->getLevelProgress($player);

        return [
            'total_situations_completed' => $totalSituationsCompleted,
            'total_micro_actions_performed' => $totalMicroActionsPerformed,
            'today_situations' => $todaySituations,
            'today_micro_actions' => $todayMicroActions,
            'average_stress_week' => round($averageStressThisWeek, 1),
            'current_streak' => $player->consecutive_days,
            'level_progress' => $levelProgress,
            'energy_percentage' => round(($player->energy / 200) * 100, 1),
            'stress_status' => $this->getStressStatus($player->stress),
        ];
    }

    public function getPlayerProgress(int $playerId): array
    {
        $player = $this->findById($playerId);
        if (!$player) {
            return [];
        }

        $levelProgress = $this->getLevelProgress($player);
        $achievements = $this->getPlayerAchievements($playerId);
        $recentActivity = $this->getRecentActivity($playerId);

        return [
            'current_level' => $player->level,
            'total_experience' => $player->total_experience,
            'experience_to_next_level' => $levelProgress['experience_to_next'],
            'level_progress_percentage' => $levelProgress['progress_percentage'],
            'achievements' => $achievements,
            'recent_activity' => $recentActivity,
        ];
    }

    public function getPlayerAchievements(int $playerId): array
    {
        $achievements = [];

        $situationsCount = PlayerSituation::where('player_id', $playerId)
            ->whereNotNull('completed_at')
            ->count();

        $microActionsCount = PlayerMicroAction::where('player_id', $playerId)
            ->count();

        $player = $this->findById($playerId);

        if ($situationsCount >= 1) {
            $achievements[] = [
                'id' => 'first_situation',
                'title' => 'Первый шаг',
                'description' => 'Прошли первую стрессовую ситуацию',
                'unlocked_at' => PlayerSituation::where('player_id', $playerId)->first()?->completed_at
            ];
        }

        if ($situationsCount >= 10) {
            $achievements[] = [
                'id' => 'situation_master',
                'title' => 'Мастер ситуаций',
                'description' => 'Прошли 10 стрессовых ситуаций',
                'unlocked_at' => null
            ];
        }

        if ($microActionsCount >= 5) {
            $achievements[] = [
                'id' => 'active_player',
                'title' => 'Активный игрок',
                'description' => 'Выполнили 5 микродействий',
                'unlocked_at' => null
            ];
        }

        if ($player && $player->consecutive_days >= 7) {
            $achievements[] = [
                'id' => 'week_streak',
                'title' => 'Недельная серия',
                'description' => '7 дней подряд в игре',
                'unlocked_at' => null
            ];
        }

        if ($player && $player->level >= 5) {
            $achievements[] = [
                'id' => 'level_five',
                'title' => 'Пятый уровень',
                'description' => 'Достигли 5-го уровня',
                'unlocked_at' => null
            ];
        }

        return $achievements;
    }

    public function getRecentActivity(int $playerId, int $limit = 10): array
    {
        $recentSituations = PlayerSituation::where('player_id', $playerId)
            ->whereNotNull('completed_at')
            ->with(['situation'])
            ->orderBy('completed_at', 'desc')
            ->limit($limit / 2)
            ->get()
            ->map(function ($playerSituation) {
                return [
                    'type' => 'situation_completed',
                    'title' => $playerSituation->situation->title ?? 'Неизвестная ситуация',
                    'timestamp' => $playerSituation->completed_at,
                    'experience_gained' => $playerSituation->situationOption->experience_reward ?? 0
                ];
            });

        $recentMicroActions = PlayerMicroAction::where('player_id', $playerId)
            ->with(['microAction'])
            ->orderBy('completed_at', 'desc')
            ->limit($limit / 2)
            ->get()
            ->map(function ($playerMicroAction) {
                return [
                    'type' => 'micro_action_performed',
                    'title' => $playerMicroAction->microAction->name ?? 'Неизвестное действие',
                    'timestamp' => $playerMicroAction->completed_at,
                    'energy_gained' => $playerMicroAction->energy_gained
                ];
            });

        return $recentSituations->concat($recentMicroActions)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->toArray();
    }

    private function getLevelProgress(PlayerProfile $player): array
    {
        $currentLevel = $player->level;
        $totalExperience = $player->total_experience;
        
        $experienceForCurrentLevel = ($currentLevel - 1) * 100;
        $experienceForNextLevel = $currentLevel * 100;
        
        $experienceInCurrentLevel = $totalExperience - $experienceForCurrentLevel;
        $experienceNeededForNextLevel = $experienceForNextLevel - $totalExperience;
        
        $progressPercentage = $experienceInCurrentLevel > 0 
            ? ($experienceInCurrentLevel / 100) * 100 
            : 0;

        return [
            'current_level' => $currentLevel,
            'experience_in_level' => $experienceInCurrentLevel,
            'experience_to_next' => $experienceNeededForNextLevel,
            'progress_percentage' => round($progressPercentage, 1)
        ];
    }

    private function getStressStatus(int $stress): string
    {
        return \App\Enums\StressLevel::fromValue($stress)->value;
    }

    public function updateLastLogin(int $playerId): bool
    {
        return PlayerProfile::where('id', $playerId)
            ->update(['last_login' => now()]);
    }

    public function canReceiveDailyReward(int $playerId): bool
    {
        $player = $this->findById($playerId);
        
        if (!$player) {
            return false;
        }

        return $player->canReceiveDailyReward();
    }

    public function giveDailyReward(int $playerId): array
    {
        $player = $this->findById($playerId);
        
        if (!$player || !$player->canReceiveDailyReward()) {
            return ['success' => false, 'message' => 'Награда уже получена сегодня'];
        }

        $rewardExperience = 10;
        $bonusExperience = 0;

        if ($player->consecutive_days >= 7) {
            $bonusExperience = 5;
        }

        $totalExperience = $rewardExperience + $bonusExperience;

        $player->addExperience($totalExperience);
        $player->update([
            'last_daily_reward' => now(),
            'consecutive_days' => $player->consecutive_days + 1
        ]);

        return [
            'success' => true,
            'experience_gained' => $totalExperience,
            'bonus_experience' => $bonusExperience,
            'consecutive_days' => $player->consecutive_days + 1
        ];
    }
}
