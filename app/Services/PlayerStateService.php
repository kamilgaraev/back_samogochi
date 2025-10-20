<?php

namespace App\Services;

use App\Http\Resources\PlayerStateResource;
use App\Models\PlayerProfile;
use App\Repositories\PlayerRepository;

class PlayerStateService
{
    protected PlayerRepository $playerRepository;

    public function __construct(PlayerRepository $playerRepository)
    {
        $this->playerRepository = $playerRepository;
    }

    public function getPlayerState(int $userId): ?array
    {
        $playerProfile = $this->playerRepository->findByUserId($userId);
        
        if (!$playerProfile) {
            return null;
        }

        return (new PlayerStateResource($playerProfile))->toArray(request());
    }

    public function getPlayerStateByProfile(PlayerProfile $playerProfile): array
    {
        return (new PlayerStateResource($playerProfile))->toArray(request());
    }

    public function getPlayerStateChanges(array $oldData, array $newData): array
    {
        $changes = [];

        if (isset($oldData['energy']) && isset($newData['energy']) && $oldData['energy'] !== $newData['energy']) {
            $changes['energy'] = [
                'old_value' => $oldData['energy'],
                'new_value' => $newData['energy'],
                'change' => $newData['energy'] - $oldData['energy'],
                'reason' => 'action_reward'
            ];
        }

        if (isset($oldData['stress']) && isset($newData['stress']) && $oldData['stress'] !== $newData['stress']) {
            $changes['stress'] = [
                'old_value' => $oldData['stress'],
                'new_value' => $newData['stress'],
                'change' => $newData['stress'] - $oldData['stress'],
                'reason' => 'action_effect'
            ];
        }

        if (isset($oldData['total_experience']) && isset($newData['total_experience']) && $oldData['total_experience'] !== $newData['total_experience']) {
            $expPerLevel = \App\Services\GameConfigService::getExperiencePerLevel();
            $oldLevel = floor($oldData['total_experience'] / $expPerLevel) + 1;
            $newLevel = floor($newData['total_experience'] / $expPerLevel) + 1;
            
            $changes['experience'] = [
                'old_total' => $oldData['total_experience'],
                'new_total' => $newData['total_experience'],
                'gained' => $newData['total_experience'] - $oldData['total_experience'],
                'level_up' => $newLevel > $oldLevel
            ];

            if ($oldLevel !== $newLevel) {
                $changes['level'] = [
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel,
                    'level_up' => true
                ];
            }
        }

        return $changes;
    }
}
