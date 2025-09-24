<?php

namespace App\Repositories;

use App\Models\Situation;
use App\Models\PlayerSituation;
use App\Models\PlayerProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SituationRepository
{
    public function getAvailableSituations(int $playerLevel, int $perPage = 15): LengthAwarePaginator
    {
        return Situation::where('is_active', true)
            ->where('min_level_required', '<=', $playerLevel)
            ->with(['options' => function ($query) use ($playerLevel) {
                $query->where('min_level_required', '<=', $playerLevel)
                    ->orderBy('order');
            }])
            ->orderBy('difficulty_level')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getSituationsByCategory(string $category, int $playerLevel): Collection
    {
        return Situation::where('is_active', true)
            ->where('category', $category)
            ->where('min_level_required', '<=', $playerLevel)
            ->with(['options' => function ($query) use ($playerLevel) {
                $query->where('min_level_required', '<=', $playerLevel)
                    ->orderBy('order');
            }])
            ->orderBy('difficulty_level')
            ->get();
    }

    public function getRandomSituation(int $playerLevel, ?string $category = null): ?Situation
    {
        $query = Situation::where('is_active', true)
            ->where('min_level_required', '<=', $playerLevel)
            ->with(['options' => function ($query) use ($playerLevel) {
                $query->where('min_level_required', '<=', $playerLevel)
                    ->orderBy('order');
            }]);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->inRandomOrder()->first();
    }

    public function findSituationById(int $id): ?Situation
    {
        return Situation::with(['options' => function ($query) {
            $query->orderBy('order');
        }])
        ->where('is_active', true)
        ->find($id);
    }

    public function createPlayerSituation(int $playerId, int $situationId): PlayerSituation
    {
        return PlayerSituation::create([
            'player_id' => $playerId,
            'situation_id' => $situationId,
        ]);
    }

    public function completeSituation(int $playerSituationId, int $optionId): bool
    {
        return PlayerSituation::where('id', $playerSituationId)
            ->whereNull('completed_at')
            ->update([
                'selected_option_id' => $optionId,
                'completed_at' => now(),
            ]);
    }

    public function getPlayerSituation(int $playerId, int $situationId): ?PlayerSituation
    {
        return PlayerSituation::where('player_id', $playerId)
            ->where('situation_id', $situationId)
            ->whereNull('completed_at')
            ->with(['situation.options'])
            ->first();
    }

    public function getActivePlayerSituation(int $playerId, int $situationId): ?PlayerSituation
    {
        return PlayerSituation::where('player_id', $playerId)
            ->where('situation_id', $situationId)
            ->whereNull('completed_at')
            ->whereNull('selected_option_id')
            ->with(['situation.options'])
            ->first();
    }

    public function getCompletedSituationsCount(int $playerId): int
    {
        return PlayerSituation::where('player_id', $playerId)
            ->whereNotNull('completed_at')
            ->count();
    }

    public function getPlayerSituationHistory(int $playerId, int $limit = 20): Collection
    {
        return PlayerSituation::where('player_id', $playerId)
            ->whereNotNull('completed_at')
            ->with(['situation', 'situationOption'])
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function canStartNewSituation(int $playerId): bool
    {
        $activeSituation = PlayerSituation::where('player_id', $playerId)
            ->whereNull('completed_at')
            ->first();

        return !$activeSituation;
    }

    public function getLastCompletedSituation(int $playerId): ?PlayerSituation
    {
        return PlayerSituation::where('player_id', $playerId)
            ->whereNotNull('completed_at')
            ->with(['situation', 'situationOption'])
            ->latest('completed_at')
            ->first();
    }

    public function getSituationStats(int $situationId): array
    {
        $totalAttempts = PlayerSituation::where('situation_id', $situationId)->count();
        $completedAttempts = PlayerSituation::where('situation_id', $situationId)
            ->whereNotNull('completed_at')
            ->count();

        $optionStats = PlayerSituation::where('situation_id', $situationId)
            ->whereNotNull('selected_option_id')
            ->selectRaw('selected_option_id, COUNT(*) as count')
            ->groupBy('selected_option_id')
            ->get()
            ->pluck('count', 'selected_option_id');

        return [
            'total_attempts' => $totalAttempts,
            'completed_attempts' => $completedAttempts,
            'completion_rate' => $totalAttempts > 0 ? round(($completedAttempts / $totalAttempts) * 100, 1) : 0,
            'option_stats' => $optionStats->toArray(),
        ];
    }

    public function getCooldownEndTime(int $playerId): ?\Carbon\Carbon
    {
        $lastCompleted = $this->getLastCompletedSituation($playerId);
        
        if (!$lastCompleted) {
            return null;
        }

        $cooldownHours = config('game.situation_cooldown_hours', 2);
        return $lastCompleted->completed_at->addHours($cooldownHours);
    }

    public function isOnCooldown(int $playerId): bool
    {
        $cooldownEndTime = $this->getCooldownEndTime($playerId);
        
        if (!$cooldownEndTime) {
            return false;
        }

        return now() < $cooldownEndTime;
    }

    public function getRecommendedSituations(int $playerId, int $limit = 5): Collection
    {
        $player = PlayerProfile::find($playerId);
        
        if (!$player) {
            return collect();
        }

        $completedSituationIds = PlayerSituation::where('player_id', $playerId)
            ->whereNotNull('completed_at')
            ->pluck('situation_id')
            ->toArray();

        $stressLevel = $player->stress;
        $playerLevel = $player->level;

        $query = Situation::where('is_active', true)
            ->where('min_level_required', '<=', $playerLevel)
            ->whereNotIn('id', $completedSituationIds)
            ->with(['options' => function ($query) use ($playerLevel) {
                $query->where('min_level_required', '<=', $playerLevel)
                    ->orderBy('order');
            }]);

        if ($stressLevel > 70) {
            $query->where('stress_impact', '<=', 0);
        } elseif ($stressLevel < 30) {
            $query->where('stress_impact', '>=', 0);
        }

        return $query->orderBy('experience_reward', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRandomRecommendedSituation(int $playerId): ?Situation
    {
        $recommendedSituations = $this->getRecommendedSituations($playerId, 50);
        
        if ($recommendedSituations->isEmpty()) {
            return null;
        }

        return $recommendedSituations->random();
    }

    public function getActiveSituation(int $playerId): ?PlayerSituation
    {
        return PlayerSituation::where('player_id', $playerId)
            ->whereNull('completed_at')
            ->whereNull('selected_option_id')
            ->with(['situation.options' => function ($query) use ($playerId) {
                $player = PlayerProfile::find($playerId);
                if ($player) {
                    $query->where('min_level_required', '<=', $player->level)
                          ->orderBy('order');
                }
            }])
            ->first();
    }
}
