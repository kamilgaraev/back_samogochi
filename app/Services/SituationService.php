<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\PlayerProfile;
use App\Repositories\SituationRepository;
use App\Repositories\PlayerRepository;
use App\Services\PlayerService;
use App\Services\PlayerStateService;
use Illuminate\Support\Facades\DB;

class SituationService
{
    protected SituationRepository $situationRepository;
    protected PlayerRepository $playerRepository;
    protected PlayerService $playerService;
    protected PlayerStateService $playerStateService;

    public function __construct(
        SituationRepository $situationRepository,
        PlayerRepository $playerRepository,
        PlayerService $playerService,
        PlayerStateService $playerStateService
    ) {
        $this->situationRepository = $situationRepository;
        $this->playerRepository = $playerRepository;
        $this->playerService = $playerService;
        $this->playerStateService = $playerStateService;
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

        $situations = $this->situationRepository->getAvailableSituations($player->level, $player->id, $perPage);
        
        $situationsWithOptions = collect($situations->items())->map(function ($situation) use ($player) {
            $allOptions = $situation->options->map(function ($option) use ($player) {
                $isAvailable = $option->min_level_required <= $player->level && $option->is_available;
                return array_merge($option->toArray(), ['is_available' => $isAvailable]);
            });
            
            $situationArray = $situation->toArray();
            $situationArray['options'] = $allOptions->values();
            return $situationArray;
        });
        
        $onCooldown = $this->situationRepository->isOnCooldown($player->id);
        $cooldownEndTime = null;
        
        if ($onCooldown) {
            $cooldownEndTime = $this->situationRepository->getCooldownEndTime($player->id);
        }

        return [
            'success' => true,
            'data' => [
                'situations' => $situationsWithOptions,
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
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
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
            return $option->min_level_required <= $player->level && $option->is_available;
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
                    'position' => $situation->position,
                ],
                'options' => $availableOptions->map(function ($option) use ($player) {
                    $isAvailable = $option->min_level_required <= $player->level && $option->is_available;
                    return array_merge($option->toArray(), ['is_available' => $isAvailable]);
                })->values(),
                'player_info' => [
                    'current_stress' => $player->stress,
                    'current_energy' => $player->energy,
                    'level' => $player->level,
                ],
                'can_start' => $this->situationRepository->canStartNewSituation($player->id) && 
                               !$this->situationRepository->isOnCooldown($player->id)
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
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

        $situation = $this->situationRepository->getRandomSituation($player->level, $player->id, $category);
        
        if (!$situation) {
            return [
                'success' => false,
                'message' => 'Нет доступных ситуаций для вашего уровня'
            ];
        }

        $allOptions = $situation->options->map(function ($option) use ($player) {
            $isAvailable = $option->min_level_required <= $player->level && $option->is_available;
            return array_merge($option->toArray(), ['is_available' => $isAvailable]);
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
                    'position' => $situation->position,
                ],
                'options' => $allOptions->values(),
                'player_info' => [
                    'current_stress' => $player->stress,
                    'current_energy' => $player->energy,
                    'level' => $player->level,
                ]
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
        ];
    }

    public function startSituation(int $situationId, int $userId): array
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

            if ($player->isSleeping()) {
                $sleepInfo = $player->getSleepInfo();
                return [
                    'success' => false,
                    'message' => 'Персонаж спит. Подождите пока он проснется.',
                    'sleep_info' => $sleepInfo
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

            $existingPlayerSituation = $this->situationRepository->getActivePlayerSituation($player->id, $situationId);
            if ($existingPlayerSituation) {
                return [
                    'success' => false,
                    'message' => 'Данная ситуация уже начата'
                ];
            }

            $oldStress = $player->stress;

            $player->updateStress($situation->stress_impact);

            $playerSituation = $this->situationRepository->createPlayerSituation($player->id, $situationId);

            ActivityLog::logEvent('situation.started', [
                'situation_id' => $situationId,
                'situation_title' => $situation->title,
                'stress_impact' => $situation->stress_impact,
                'old_stress' => $oldStress,
                'new_stress' => $player->fresh()->stress
            ], $userId);

            DB::commit();

            $allOptions = $situation->options->map(function ($option) use ($player) {
                $isAvailable = $option->min_level_required <= $player->level && $option->is_available;
                return array_merge($option->toArray(), ['is_available' => $isAvailable]);
            });

            return [
                'success' => true,
                'message' => 'Ситуация успешно начата!',
                'data' => [
                    'situation' => [
                        'id' => $situation->id,
                        'title' => $situation->title,
                        'description' => $situation->description,
                        'category' => $situation->category,
                        'stress_impact' => $situation->stress_impact,
                        'position' => $situation->position
                    ],
                    'options' => $allOptions->values(),
                    'player_changes' => [
                        'old_stress' => $oldStress,
                        'new_stress' => $player->fresh()->stress,
                        'stress_change' => $situation->stress_impact,
                        'current_energy' => $player->energy,
                        'current_level' => $player->level
                    ],
                    'player_situation_id' => $playerSituation->id
                ],
                'player_state' => $this->playerStateService->getPlayerStateByProfile($player->fresh())
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при начале ситуации: ' . $e->getMessage()
            ];
        }
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

            $situation = $this->situationRepository->findSituationById($situationId);
            
            if (!$situation) {
                return [
                    'success' => false,
                    'message' => 'Ситуация не найдена'
                ];
            }

            $playerSituation = $this->situationRepository->getActivePlayerSituation($player->id, $situationId);
            if (!$playerSituation) {
                return [
                    'success' => false,
                    'message' => 'Ситуация не была начата. Сначала инициируйте ситуацию.'
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

            if (!$option->is_available) {
                return [
                    'success' => false,
                    'message' => 'Это действие сейчас недоступно'
                ];
            }

            if ($option->energy_cost > $player->energy) {
                return [
                    'success' => false,
                    'message' => 'Недостаточно энергии для выполнения действия'
                ];
            }

            $this->situationRepository->completeSituation($playerSituation->id, $optionId);

            $oldStress = $player->stress;
            $oldEnergy = $player->energy;
            $oldLevel = $player->level;

            $player->updateStress($option->stress_change);
            $player->updateEnergy(-$option->energy_cost);
            $player->addExperience($option->experience_reward);
            $player->incrementSituationsCounter();

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
                    'cooldown_until' => now()->addSeconds(\App\Services\GameConfigService::getSituationCooldownSeconds()),
                ],
                'player_state' => $this->playerStateService->getPlayerStateByProfile($player->fresh())
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

        $situations = $this->situationRepository->getSituationsByCategory($category, $player->level, $player->id);

        $situationsWithOptions = $situations->map(function ($situation) use ($player) {
            $allOptions = $situation->options->map(function ($option) use ($player) {
                $isAvailable = $option->min_level_required <= $player->level && $option->is_available;
                return array_merge($option->toArray(), ['is_available' => $isAvailable]);
            });
            
            $situationArray = $situation->toArray();
            $situationArray['options'] = $allOptions->values();
            return $situationArray;
        });

        return [
            'success' => true,
            'data' => [
                'category' => $category,
                'situations' => $situationsWithOptions,
                'count' => $situationsWithOptions->count()
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
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
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
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

        $recommendationsWithOptions = $recommendations->map(function ($situation) use ($player) {
            $allOptions = $situation->options->map(function ($option) use ($player) {
                $isAvailable = $option->min_level_required <= $player->level && $option->is_available;
                return array_merge($option->toArray(), ['is_available' => $isAvailable]);
            });
            
            $situationArray = $situation->toArray();
            $situationArray['options'] = $allOptions->values();
            return $situationArray;
        });

        return [
            'success' => true,
            'data' => [
                'recommendations' => $recommendationsWithOptions,
                'based_on' => [
                    'stress_level' => $player->stress,
                    'player_level' => $player->level,
                    'completed_situations' => $this->situationRepository->getCompletedSituationsCount($player->id)
                ]
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
        ];
    }

    public function getRandomRecommendedSituation(int $userId): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $situation = $this->situationRepository->getRandomRecommendedSituation($player->id);
        
        if (!$situation) {
            return [
                'success' => false,
                'message' => 'Нет доступных рекомендованных ситуаций для вашего уровня'
            ];
        }

        $allOptions = $situation->options->map(function ($option) use ($player) {
            $isAvailable = $option->min_level_required <= $player->level && $option->is_available;
            return array_merge($option->toArray(), ['is_available' => $isAvailable]);
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
                    'position' => $situation->position,
                ],
                'options' => $allOptions->values(),
                'player_info' => [
                    'current_stress' => $player->stress,
                    'current_energy' => $player->energy,
                    'level' => $player->level,
                ],
                'is_recommended' => true,
                'cooldown_info' => [
                    'on_cooldown' => $this->situationRepository->isOnCooldown($player->id),
                    'cooldown_ends_at' => $this->situationRepository->getCooldownEndTime($player->id)
                ]
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
        ];
    }

    public function getActiveSituation(int $userId): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $activeSituations = $this->situationRepository->getAllActiveSituations($player->id);
        
        $isOnCooldown = $this->situationRepository->isOnCooldown($player->id);
        $cooldownEndTime = $this->situationRepository->getCooldownEndTime($player->id);
        
        return [
            'success' => true,
            'data' => [
                'situations' => $activeSituations->map(function ($activeSituation) use ($player) {
                    $allOptions = $activeSituation->situation->options->map(function ($option) use ($player) {
                        $isAvailable = $option->min_level_required <= $player->level && $option->is_available;
                        return array_merge($option->toArray(), ['is_available' => $isAvailable]);
                    });

                    return [
                        'player_situation_id' => $activeSituation->id,
                        'situation' => [
                            'id' => $activeSituation->situation->id,
                            'title' => $activeSituation->situation->title,
                            'description' => $activeSituation->situation->description,
                            'category' => $activeSituation->situation->category,
                            'difficulty_level' => $activeSituation->situation->difficulty_level,
                            'stress_impact' => $activeSituation->situation->stress_impact,
                            'experience_reward' => $activeSituation->situation->experience_reward,
                            'position' => $activeSituation->situation->position,
                        ],
                        'options' => $allOptions->values(),
                        'started_at' => $activeSituation->created_at,
                    ];
                })->values(),
                'count' => $activeSituations->count(),
                'player_info' => [
                    'current_stress' => $player->stress,
                    'current_energy' => $player->energy,
                    'level' => $player->level,
                ],
                'cooldown_info' => [
                    'on_cooldown' => $isOnCooldown,
                    'cooldown_ends_at' => $cooldownEndTime,
                    'can_start_new' => !$isOnCooldown
                ]
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
        ];
    }

}
