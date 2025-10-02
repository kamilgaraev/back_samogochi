<?php

namespace App\Services;

use App\Models\PlayerProfile;
use App\Models\ActivityLog;
use App\Repositories\PlayerRepository;
use App\Services\PlayerStateService;
use Illuminate\Support\Facades\DB;

class PlayerService
{
    protected PlayerRepository $playerRepository;
    protected PlayerStateService $playerStateService;

    public function __construct(PlayerRepository $playerRepository, PlayerStateService $playerStateService)
    {
        $this->playerRepository = $playerRepository;
        $this->playerStateService = $playerStateService;
    }

    public function getPlayerProfile(int $userId): ?array
    {
        $playerProfile = $this->playerRepository->findByUserId($userId);
        
        if (!$playerProfile) {
            return null;
        }

        $levelProgress = $this->calculateLevelProgress($playerProfile);
        $canReceiveDailyReward = $this->playerRepository->canReceiveDailyReward($playerProfile->id);

        return [
            'id' => $playerProfile->id,
            'user_id' => $playerProfile->user_id,
            'level' => $playerProfile->level,
            'total_experience' => $playerProfile->total_experience,
            'experience_in_current_level' => $levelProgress['experience_in_level'],
            'experience_to_next_level' => $levelProgress['experience_to_next'],
            'level_progress_percentage' => $levelProgress['progress_percentage'],
            'energy' => $playerProfile->energy,
            'max_energy' => $this->getMaxEnergy(),
            'energy_percentage' => round(($playerProfile->energy / $this->getMaxEnergy()) * 100, 1),
            'stress' => $playerProfile->stress,
            'stress_status' => $this->getStressStatus($playerProfile->stress),
            'anxiety' => $playerProfile->anxiety,
            'last_login' => $playerProfile->last_login,
            'consecutive_days' => $playerProfile->consecutive_days,
            'can_receive_daily_reward' => $canReceiveDailyReward,
            'personal_info' => [
                'favorite_song' => $playerProfile->favorite_song,
                'favorite_movie' => $playerProfile->favorite_movie,
                'favorite_book' => $playerProfile->favorite_book,
                'favorite_dish' => $playerProfile->favorite_dish,
                'best_friend_name' => $playerProfile->best_friend_name,
            ],
            'created_at' => $playerProfile->created_at,
            'updated_at' => $playerProfile->updated_at,
        ];
    }

    public function updatePlayerProfile(int $userId, array $data): array
    {
        try {
            DB::beginTransaction();

            $allowedFields = ['stress', 'anxiety'];
            $updateData = array_intersect_key($data, array_flip($allowedFields));

            if (isset($updateData['stress'])) {
                $updateData['stress'] = max(0, min(100, $updateData['stress']));
            }

            if (isset($updateData['anxiety'])) {
                $updateData['anxiety'] = max(0, min(100, $updateData['anxiety']));
            }

            if (!empty($updateData)) {
                $this->playerRepository->updateProfile($userId, $updateData);

                ActivityLog::logEvent('player.profile_updated', [
                    'updated_fields' => array_keys($updateData),
                    'values' => $updateData
                ]);
            }

            DB::commit();

            $updatedPlayer = $this->playerRepository->findByUserId($userId);

            return [
                'success' => true,
                'message' => 'Профиль успешно обновлен',
                'data' => [
                    'updated_fields' => array_keys($updateData)
                ],
                'player_state' => $this->playerStateService->getPlayerStateByProfile($updatedPlayer)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении профиля: ' . $e->getMessage()
            ];
        }
    }

    public function getPlayerStats(int $userId): array
    {
        $playerProfile = $this->playerRepository->findByUserId($userId);
        
        if (!$playerProfile) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $stats = $this->playerRepository->getPlayerStats($playerProfile->id);

        return [
            'success' => true,
            'data' => $stats,
            'player_state' => $this->playerStateService->getPlayerStateByProfile($playerProfile)
        ];
    }

    public function getPlayerProgress(int $userId): array
    {
        $playerProfile = $this->playerRepository->findByUserId($userId);
        
        if (!$playerProfile) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $progress = $this->playerRepository->getPlayerProgress($playerProfile->id);

        return [
            'success' => true,
            'data' => $progress,
            'player_state' => $this->playerStateService->getPlayerStateByProfile($playerProfile->fresh())
        ];
    }

    public function claimDailyReward(int $userId): array
    {
        $playerProfile = $this->playerRepository->findByUserId($userId);
        
        if (!$playerProfile) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        try {
            DB::beginTransaction();

            $reward = $this->playerRepository->giveDailyReward($playerProfile->id);
            
            if ($reward['success']) {
                ActivityLog::logEvent('player.daily_reward_claimed', [
                    'experience_gained' => $reward['experience_gained'],
                    'bonus_experience' => $reward['bonus_experience'],
                    'consecutive_days' => $reward['consecutive_days']
                ]);
            }

            DB::commit();

            $updatedPlayer = $playerProfile->fresh();
            
            if ($reward['success']) {
                $reward['player_state'] = $this->playerStateService->getPlayerStateByProfile($updatedPlayer);
            }
            
            return $reward;

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при получении награды: ' . $e->getMessage()
            ];
        }
    }

    public function addExperience(int $userId, int $amount, string $reason = 'manual'): array
    {
        $playerProfile = $this->playerRepository->findByUserId($userId);
        
        if (!$playerProfile) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        try {
            DB::beginTransaction();

            $oldLevel = $playerProfile->level;
            $playerProfile->addExperience($amount);
            $newLevel = $playerProfile->fresh()->level;

            $levelUp = $newLevel > $oldLevel;

            ActivityLog::logEvent('player.experience_added', [
                'amount' => $amount,
                'reason' => $reason,
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'level_up' => $levelUp
            ]);

            DB::commit();

            $updatedPlayer = $playerProfile->fresh();

            return [
                'success' => true,
                'data' => [
                    'experience_added' => $amount,
                    'level_up' => $levelUp,
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel
                ],
                'player_state' => $this->playerStateService->getPlayerStateByProfile($updatedPlayer)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при добавлении опыта: ' . $e->getMessage()
            ];
        }
    }

    public function updateEnergy(int $userId, int $amount): array
    {
        $playerProfile = $this->playerRepository->findByUserId($userId);
        
        if (!$playerProfile) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $oldEnergy = $playerProfile->energy;
        $playerProfile->updateEnergy($amount);
        $newEnergy = $playerProfile->fresh()->energy;

        ActivityLog::logEvent('player.energy_updated', [
            'amount' => $amount,
            'old_energy' => $oldEnergy,
            'new_energy' => $newEnergy
        ]);

        $updatedPlayer = $playerProfile->fresh();

        return [
            'success' => true,
            'data' => [
                'old_energy' => $oldEnergy,
                'new_energy' => $newEnergy,
                'change' => $amount
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($updatedPlayer)
        ];
    }

    public function updateStress(int $userId, int $amount): array
    {
        $playerProfile = $this->playerRepository->findByUserId($userId);
        
        if (!$playerProfile) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $oldStress = $playerProfile->stress;
        $playerProfile->updateStress($amount);
        $newStress = $playerProfile->fresh()->stress;

        ActivityLog::logEvent('player.stress_updated', [
            'amount' => $amount,
            'old_stress' => $oldStress,
            'new_stress' => $newStress,
            'stress_status' => $this->getStressStatus($newStress)
        ]);

        $updatedPlayer = $playerProfile->fresh();

        return [
            'success' => true,
            'data' => [
                'old_stress' => $oldStress,
                'new_stress' => $newStress,
                'change' => $amount,
                'stress_status' => $this->getStressStatus($newStress)
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($updatedPlayer)
        ];
    }

    private function calculateLevelProgress(PlayerProfile $player): array
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
            'experience_in_level' => max(0, $experienceInCurrentLevel),
            'experience_to_next' => max(0, $experienceNeededForNextLevel),
            'progress_percentage' => round(min(100, max(0, $progressPercentage)), 1)
        ];
    }

    private function getStressStatus(int $stress): string
    {
        return \App\Enums\StressLevel::fromValue($stress)->value;
    }

    private function getMaxEnergy(): int
    {
        $gameBalance = \App\Models\GameConfig::getGameBalance();
        return $gameBalance['max_energy'] ?? 200;
    }

    public function updatePersonalInfo(int $userId, array $data): array
    {
        try {
            DB::beginTransaction();

            $allowedFields = ['favorite_song', 'favorite_movie', 'favorite_book', 'favorite_dish', 'best_friend_name'];
            $updateData = array_intersect_key($data, array_flip($allowedFields));

            if (empty($updateData)) {
                return [
                    'success' => false,
                    'message' => 'Нет данных для обновления'
                ];
            }

            $this->playerRepository->updateProfile($userId, $updateData);

            ActivityLog::logEvent('player.personal_info_updated', [
                'updated_fields' => array_keys($updateData)
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Персональная информация успешно обновлена',
                'updated_fields' => array_keys($updateData)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении персональной информации: ' . $e->getMessage()
            ];
        }
    }
}
