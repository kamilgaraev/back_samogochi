<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\PlayerProfile;
use App\Repositories\SituationRepository;
use App\Repositories\PlayerRepository;
use App\Services\PlayerService;
use Illuminate\Support\Facades\DB;

class SituationService
{
    protected SituationRepository $situationRepository;
    protected PlayerRepository $playerRepository;
    protected PlayerService $playerService;

    public function __construct(
        SituationRepository $situationRepository,
        PlayerRepository $playerRepository,
        PlayerService $playerService
    ) {
        $this->situationRepository = $situationRepository;
        $this->playerRepository = $playerRepository;
        $this->playerService = $playerService;
    }

    public function getAvailableSituations(int $userId, int $perPage = 15): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $situations = $this->situationRepository->getAvailableSituations($player->level, $perPage);
        
        $onCooldown = $this->situationRepository->isOnCooldown($player->id);
        $cooldownEndTime = null;
        
        if ($onCooldown) {
            $cooldownEndTime = $this->situationRepository->getCooldownEndTime($player->id);
        }

        return [
            'success' => true,
            'data' => [
                'situations' => $situations->items(),
                'pagination' => [
                    'current_page' => $situations->currentPage(),
                    'total_pages' => $situations->lastPage(),
                    'per_page' => $situations->perPage(),
                    'total' => $situations->total(),
                ],
                'cooldown_info' => [
                    'on_cooldown' => $onCooldown,
                    'cooldown_ends_at' => $cooldownEndTime,
                ],
                'player_level' => $player->level,
            ]
        ];
    }

    public function getSituationById(int $situationId, int $userId): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $situation = $this->situationRepository->findSituationById($situationId);
        
        if (!$situation) {
            return [
                'success' => false,
                'message' => 'Ситуация не найдена'
            ];
        }

        if ($situation->min_level_required > $player->level) {
            return [
                'success' => false,
                'message' => 'Недостаточный уровень для этой ситуации'
            ];
        }

        $availableOptions = $situation->options->filter(function ($option) use ($player) {
            return $option->min_level_required <= $player->level;
        });

        return [
            'success' => true,
            'data' => [
                'situation' => [
                    'id' => $situation->id,
                    'title' => $situation->title,
                    'description' => $situation->description,
                    'category' => $situation->category,
                    'difficulty_level' => $situation->difficulty_level,
                    'stress_impact' => $situation->stress_impact,
                    'experience_reward' => $situation->experience_reward,
                ],
                'options' => $availableOptions->values(),
                'player_info' => [
                    'current_stress' => $player->stress,
                    'current_energy' => $player->energy,
                    'level' => $player->level,
                ],
                'can_start' => $this->situationRepository->canStartNewSituation($player->id) && 
                               !$this->situationRepository->isOnCooldown($player->id)
            ]
        ];
    }

    public function getRandomSituation(int $userId, ?string $category = null): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        if ($this->situationRepository->isOnCooldown($player->id)) {
            $cooldownEndTime = $this->situationRepository->getCooldownEndTime($player->id);
            return [
                'success' => false,
                'message' => 'Вы еще не можете начать новую ситуацию',
                'cooldown_ends_at' => $cooldownEndTime
            ];
        }

        $situation = $this->situationRepository->getRandomSituation($player->level, $category);
        
        if (!$situation) {
            return [
                'success' => false,
                'message' => 'Нет доступных ситуаций для вашего уровня'
            ];
        }

        return [
            'success' => true,
            'data' => [
                'situation' => [
                    'id' => $situation->id,
                    'title' => $situation->title,
                    'description' => $situation->description,
                    'category' => $situation->category,
                    'difficulty_level' => $situation->difficulty_level,
                    'stress_impact' => $situation->stress_impact,
                    'experience_reward' => $situation->experience_reward,
                ],
                'options' => $situation->options->filter(function ($option) use ($player) {
                    return $option->min_level_required <= $player->level;
                })->values(),
                'player_info' => [
                    'current_stress' => $player->stress,
                    'current_energy' => $player->energy,
                    'level' => $player->level,
                ]
            ]
        ];
    }

    public function completeSituation(int $situationId, int $optionId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $player = $this->playerRepository->findByUserId($userId);
            
            if (!$player) {
                return [
                    'success' => false,
                    'message' => 'Профиль игрока не найден'
                ];
            }

            if ($this->situationRepository->isOnCooldown($player->id)) {
                return [
                    'success' => false,
                    'message' => 'Вы еще не можете завершить ситуацию. Подождите окончания перезарядки.'
                ];
            }

            $situation = $this->situationRepository->findSituationById($situationId);
            
            if (!$situation) {
                return [
                    'success' => false,
                    'message' => 'Ситуация не найдена'
                ];
            }

            $option = $situation->options()->find($optionId);
            
            if (!$option) {
                return [
                    'success' => false,
                    'message' => 'Вариант действия не найден'
                ];
            }

            if ($option->min_level_required > $player->level) {
                return [
                    'success' => false,
                    'message' => 'Недостаточный уровень для этого действия'
                ];
            }

            if ($option->energy_cost > $player->energy) {
                return [
                    'success' => false,
                    'message' => 'Недостаточно энергии для выполнения действия'
                ];
            }

            $playerSituation = $this->situationRepository->createPlayerSituation($player->id, $situationId);
            $this->situationRepository->completeSituation($playerSituation->id, $optionId);

            $oldStress = $player->stress;
            $oldEnergy = $player->energy;
            $oldLevel = $player->level;

            $player->updateStress($option->stress_change);
            $player->updateEnergy(-$option->energy_cost);
            $player->addExperience($option->experience_reward);

            $newLevel = $player->fresh()->level;
            $levelUp = $newLevel > $oldLevel;

            ActivityLog::logSituationComplete($situationId, $optionId, $userId);

            if ($levelUp) {
                ActivityLog::logEvent('player.level_up', [
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel,
                    'trigger' => 'situation_completion'
                ], $userId);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => $levelUp ? 'Поздравляем! Вы достигли нового уровня!' : 'Ситуация успешно завершена!',
                'data' => [
                    'situation' => $situation->title,
                    'selected_option' => $option->text,
                    'rewards' => [
                        'experience_gained' => $option->experience_reward,
                        'stress_change' => $option->stress_change,
                        'energy_cost' => $option->energy_cost,
                    ],
                    'player_changes' => [
                        'old_stress' => $oldStress,
                        'new_stress' => $player->fresh()->stress,
                        'old_energy' => $oldEnergy,
                        'new_energy' => $player->fresh()->energy,
                        'old_level' => $oldLevel,
                        'new_level' => $newLevel,
                        'level_up' => $levelUp,
                    ],
                    'cooldown_until' => now()->addHours(config('game.situation_cooldown_hours', 2)),
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при завершении ситуации: ' . $e->getMessage()
            ];
        }
    }

    public function getSituationsByCategory(string $category, int $userId): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $validCategories = \App\Enums\SituationCategory::getAll();
        
        if (!in_array($category, $validCategories)) {
            return [
                'success' => false,
                'message' => 'Недопустимая категория'
            ];
        }

        $situations = $this->situationRepository->getSituationsByCategory($category, $player->level);

        return [
            'success' => true,
            'data' => [
                'category' => $category,
                'situations' => $situations,
                'count' => $situations->count()
            ]
        ];
    }

    public function getPlayerSituationHistory(int $userId, int $limit = 20): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $history = $this->situationRepository->getPlayerSituationHistory($player->id, $limit);

        return [
            'success' => true,
            'data' => [
                'history' => $history->map(function ($playerSituation) {
                    return [
                        'situation_title' => $playerSituation->situation->title,
                        'selected_option' => $playerSituation->situationOption->text ?? 'Неизвестно',
                        'completed_at' => $playerSituation->completed_at,
                        'experience_gained' => $playerSituation->situationOption->experience_reward ?? 0,
                        'stress_change' => $playerSituation->situationOption->stress_change ?? 0,
                        'category' => $playerSituation->situation->category,
                    ];
                }),
                'total_completed' => $this->situationRepository->getCompletedSituationsCount($player->id)
            ]
        ];
    }

    public function getRecommendedSituations(int $userId): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $recommendations = $this->situationRepository->getRecommendedSituations($player->id);

        return [
            'success' => true,
            'data' => [
                'recommendations' => $recommendations,
                'based_on' => [
                    'stress_level' => $player->stress,
                    'player_level' => $player->level,
                    'completed_situations' => $this->situationRepository->getCompletedSituationsCount($player->id)
                ]
            ]
        ];
    }
}
