<?php

namespace App\Repositories;

use App\Models\MicroAction;
use App\Models\PlayerMicroAction;
use App\Models\PlayerProfile;
use Illuminate\Database\Eloquent\Collection;

class MicroActionRepository
{
    public function getAvailableMicroActions(int $playerLevel): Collection
    {
        return MicroAction::where('is_active', true)
            ->where('unlock_level', '<=', $playerLevel)
            ->orderBy('unlock_level')
            ->orderBy('category')
            ->get();
    }

    public function getMicroActionsByCategory(string $category, int $playerLevel): Collection
    {
        return MicroAction::where('is_active', true)
            ->where('category', $category)
            ->where('unlock_level', '<=', $playerLevel)
            ->orderBy('unlock_level')
            ->get();
    }

    public function findMicroActionById(int $id): ?MicroAction
    {
        return MicroAction::where('is_active', true)->find($id);
    }

    public function getLastPerformed(int $playerId, int $microActionId): ?PlayerMicroAction
    {
        return PlayerMicroAction::where('player_id', $playerId)
            ->where('micro_action_id', $microActionId)
            ->latest('completed_at')
            ->first();
    }

    public function canPerform(int $playerId, int $microActionId): bool
    {
        return true;
        
        // $microAction = $this->findMicroActionById($microActionId);
        
        // if (!$microAction || $microAction->cooldown_minutes == 0) {
        //     return true;
        // }

        // $lastPerformed = $this->getLastPerformed($playerId, $microActionId);
        
        // if (!$lastPerformed) {
        //     return true;
        // }

        // return $lastPerformed->completed_at->addMinutes($microAction->cooldown_minutes) <= now();
    }

    public function getCooldownEndTime(int $playerId, int $microActionId): ?\Carbon\Carbon
    {
        $microAction = $this->findMicroActionById($microActionId);
        $lastPerformed = $this->getLastPerformed($playerId, $microActionId);
        
        if (!$microAction || !$lastPerformed || $microAction->cooldown_minutes == 0) {
            return null;
        }

        return $lastPerformed->completed_at->addMinutes($microAction->cooldown_minutes);
    }

    public function performMicroAction(int $playerId, int $microActionId): PlayerMicroAction
    {
        $microAction = $this->findMicroActionById($microActionId);
        
        return PlayerMicroAction::create([
            'player_id' => $playerId,
            'micro_action_id' => $microActionId,
            'completed_at' => now(),
            'energy_gained' => $microAction->energy_reward,
            'experience_gained' => $microAction->experience_reward,
        ]);
    }

    public function getPlayerMicroActionHistory(int $playerId, int $limit = 20): Collection
    {
        return PlayerMicroAction::where('player_id', $playerId)
            ->with(['microAction'])
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPerformedCount(int $playerId): int
    {
        return PlayerMicroAction::where('player_id', $playerId)->count();
    }

    public function getTodayCount(int $playerId): int
    {
        return PlayerMicroAction::where('player_id', $playerId)
            ->whereDate('completed_at', today())
            ->count();
    }

    public function getMicroActionStats(int $microActionId): array
    {
        $totalPerformed = PlayerMicroAction::where('micro_action_id', $microActionId)->count();
        $uniquePlayers = PlayerMicroAction::where('micro_action_id', $microActionId)
            ->distinct('player_id')
            ->count();

        $avgEnergyGained = PlayerMicroAction::where('micro_action_id', $microActionId)
            ->avg('energy_gained');

        $avgExperienceGained = PlayerMicroAction::where('micro_action_id', $microActionId)
            ->avg('experience_gained');

        return [
            'total_performed' => $totalPerformed,
            'unique_players' => $uniquePlayers,
            'avg_energy_gained' => round($avgEnergyGained, 1),
            'avg_experience_gained' => round($avgExperienceGained, 1),
        ];
    }

    public function getRecommendedMicroActions(int $playerId, int $limit = 5): Collection
    {
        $player = PlayerProfile::find($playerId);
        
        if (!$player) {
            return collect();
        }

        $recentlyPerformed = PlayerMicroAction::where('player_id', $playerId)
            ->where('completed_at', '>=', now()->subHours(24))
            ->pluck('micro_action_id')
            ->toArray();

        $query = MicroAction::where('is_active', true)
            ->where('unlock_level', '<=', $player->level)
            ->whereNotIn('id', $recentlyPerformed);

        if ($player->energy < 100) {
            $query->where('energy_reward', '>=', 10);
        }

        if ($player->stress > 70) {
            $query->whereIn('category', ['relaxation', 'creativity']);
        }

        return $query->orderBy('energy_reward', 'desc')
            ->limit($limit)
            ->get();
    }
}
