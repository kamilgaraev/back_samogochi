<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Repositories\MicroActionRepository;
use App\Repositories\PlayerRepository;
use App\Services\PlayerService;
use App\Services\PlayerStateService;
use Illuminate\Support\Facades\DB;

class MicroActionService
{
    protected MicroActionRepository $microActionRepository;
    protected PlayerRepository $playerRepository;
    protected PlayerService $playerService;
    protected PlayerStateService $playerStateService;

    public function __construct(
        MicroActionRepository $microActionRepository,
        PlayerRepository $playerRepository,
        PlayerService $playerService,
        PlayerStateService $playerStateService
    ) {
        $this->microActionRepository = $microActionRepository;
        $this->playerRepository = $playerRepository;
        $this->playerService = $playerService;
        $this->playerStateService = $playerStateService;
    }

    public function getAvailableMicroActions(int $userId): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $microActions = $this->microActionRepository->getAvailableMicroActions($player->level);
        
        $microActionsWithCooldown = $microActions->map(function ($microAction) use ($player) {
            $canPerform = $this->microActionRepository->canPerform($player->id, $microAction->id);
            $cooldownEndTime = null;
            
            if (!$canPerform) {
                $cooldownEndTime = $this->microActionRepository->getCooldownEndTime($player->id, $microAction->id);
            }

            // Персонализация текста
            $personalized = $this->personalizeMicroAction($microAction, $player);

            return [
                'id' => $microAction->id,
                'name' => $personalized['name'],
                'description' => $personalized['description'],
                'category' => [
                    'value' => $microAction->category->value,
                    'label' => $microAction->category->getLabel(),
                    'icon' => $microAction->category->getIcon(),
                    'color' => $microAction->category->getColor(),
                ],
                'energy_reward' => $microAction->energy_reward,
                'experience_reward' => $microAction->experience_reward,
                'cooldown_minutes' => $microAction->cooldown_minutes,
                'unlock_level' => $microAction->unlock_level,
                'position' => $microAction->position,
                'can_perform' => $canPerform,
                'cooldown_ends_at' => $cooldownEndTime,
            ];
        });

        return [
            'success' => true,
            'data' => [
                'micro_actions' => $microActionsWithCooldown->values(),
                'player_info' => [
                    'current_energy' => $player->energy,
                    'level' => $player->level,
                ],
                'player_state' => $this->playerStateService->getPlayerStateByProfile($player),
                'stats' => [
                    'total_performed' => $this->microActionRepository->getPerformedCount($player->id),
                    'today_performed' => $this->microActionRepository->getTodayCount($player->id),
                ]
            ]
        ];
    }

    public function performMicroAction(int $microActionId, int $userId): array
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

            if (GameConfigService::isMicroActionsDisabledDuringSleep() && $player->isSleeping()) {
                $sleepInfo = $player->getSleepInfo();
                return [
                    'success' => false,
                    'message' => 'Персонаж спит. Микродействия недоступны.',
                    'sleep_info' => $sleepInfo
                ];
            }

            $microAction = $this->microActionRepository->findMicroActionById($microActionId);
            
            if (!$microAction) {
                return [
                    'success' => false,
                    'message' => 'Микродействие не найдено'
                ];
            }

            if ($microAction->unlock_level > $player->level) {
                return [
                    'success' => false,
                    'message' => 'Недостаточный уровень для этого действия'
                ];
            }

            if (!$this->microActionRepository->canPerform($player->id, $microActionId)) {
                $cooldownEndTime = $this->microActionRepository->getCooldownEndTime($player->id, $microActionId);
                return [
                    'success' => false,
                    'message' => 'Действие еще не доступно',
                    'cooldown_ends_at' => $cooldownEndTime
                ];
            }

            $oldLevel = $player->level;
            $oldEnergy = $player->energy;

            $playerMicroAction = $this->microActionRepository->performMicroAction($player->id, $microActionId);

            $player->updateEnergy($microAction->energy_reward);
            $player->addExperience($microAction->experience_reward);

            $newLevel = $player->fresh()->level;
            $newEnergy = $player->fresh()->energy;
            $levelUp = $newLevel > $oldLevel;

            ActivityLog::logMicroActionPerform($microActionId, $userId);

            if ($levelUp) {
                ActivityLog::logEvent('player.level_up', [
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel,
                    'trigger' => 'micro_action_completion'
                ], $userId);
            }

            DB::commit();

            $updatedPlayer = $this->playerRepository->findByUserId($userId);
            $personalized = $this->personalizeMicroAction($microAction, $updatedPlayer);

            return [
                'success' => true,
                'message' => $levelUp ? 'Поздравляем! Вы достигли нового уровня!' : 'Микродействие успешно выполнено!',
                'data' => [
                    'micro_action' => [
                        'id' => $microAction->id,
                        'name' => $personalized['name'],
                        'category' => [
                            'value' => $microAction->category->value,
                            'label' => $microAction->category->getLabel(),
                            'icon' => $microAction->category->getIcon(),
                        ],
                        'position' => $microAction->position,
                    ],
                    'rewards' => [
                        'energy_gained' => $microAction->energy_reward,
                        'experience_gained' => $microAction->experience_reward,
                    ],
                    'player_changes' => [
                        'old_energy' => $oldEnergy,
                        'new_energy' => $newEnergy,
                        'old_level' => $oldLevel,
                        'new_level' => $newLevel,
                        'level_up' => $levelUp,
                    ],
                    'player_state' => $this->playerStateService->getPlayerStateByProfile($updatedPlayer),
                    'cooldown_until' => $microAction->cooldown_minutes > 0 
                        ? now()->addMinutes($microAction->cooldown_minutes)
                        : null,
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при выполнении микродействия: ' . $e->getMessage()
            ];
        }
    }

    public function getMicroActionHistory(int $userId, int $limit = 20): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $history = $this->microActionRepository->getPlayerMicroActionHistory($player->id, $limit);

        return [
            'success' => true,
            'data' => [
                'history' => $history->map(function ($playerMicroAction) {
                    return [
                        'micro_action_name' => $playerMicroAction->microAction->name,
                        'category' => [
                            'value' => $playerMicroAction->microAction->category->value,
                            'label' => $playerMicroAction->microAction->category->getLabel(),
                            'icon' => $playerMicroAction->microAction->category->getIcon(),
                        ],
                        'completed_at' => $playerMicroAction->completed_at,
                        'energy_gained' => $playerMicroAction->energy_gained,
                        'experience_gained' => $playerMicroAction->experience_gained,
                    ];
                }),
                'stats' => [
                    'total_performed' => $this->microActionRepository->getPerformedCount($player->id),
                    'today_performed' => $this->microActionRepository->getTodayCount($player->id),
                ]
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
        ];
    }

    public function getRecommendedMicroActions(int $userId): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $recommendations = $this->microActionRepository->getRecommendedMicroActions($player->id);

        return [
            'success' => true,
            'data' => [
                'recommendations' => $recommendations->map(function ($microAction) use ($player) {
                    $personalized = $this->personalizeMicroAction($microAction, $player);
                    return [
                        'id' => $microAction->id,
                        'name' => $personalized['name'],
                        'description' => $personalized['description'],
                        'category' => [
                            'value' => $microAction->category->value,
                            'label' => $microAction->category->getLabel(),
                            'icon' => $microAction->category->getIcon(),
                        ],
                        'energy_reward' => $microAction->energy_reward,
                        'experience_reward' => $microAction->experience_reward,
                        'cooldown_minutes' => $microAction->cooldown_minutes,
                        'unlock_level' => $microAction->unlock_level,
                        'position' => $microAction->position,
                        'can_perform' => $this->microActionRepository->canPerform($player->id, $microAction->id),
                    ];
                }),
                'based_on' => [
                    'energy_level' => $player->energy,
                    'stress_level' => $player->stress,
                    'player_level' => $player->level,
                ]
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
        ];
    }

    public function getRandomRecommendedMicroAction(int $userId): array
    {
        $player = $this->playerRepository->findByUserId($userId);
        
        if (!$player) {
            return [
                'success' => false,
                'message' => 'Профиль игрока не найден'
            ];
        }

        $recommendations = $this->microActionRepository->getRecommendedMicroActions($player->id);

        if ($recommendations->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Нет доступных рекомендаций'
            ];
        }

        $randomMicroAction = $recommendations->random();
        $personalized = $this->personalizeMicroAction($randomMicroAction, $player);

        return [
            'success' => true,
            'data' => [
                'micro_action' => [
                    'id' => $randomMicroAction->id,
                    'name' => $personalized['name'],
                    'description' => $personalized['description'],
                    'category' => [
                        'value' => $randomMicroAction->category->value,
                        'label' => $randomMicroAction->category->getLabel(),
                        'icon' => $randomMicroAction->category->getIcon(),
                        'color' => $randomMicroAction->category->getColor(),
                    ],
                    'energy_reward' => $randomMicroAction->energy_reward,
                    'experience_reward' => $randomMicroAction->experience_reward,
                    'cooldown_minutes' => $randomMicroAction->cooldown_minutes,
                    'unlock_level' => $randomMicroAction->unlock_level,
                    'position' => $randomMicroAction->position,
                    'can_perform' => $this->microActionRepository->canPerform($player->id, $randomMicroAction->id),
                    'cooldown_ends_at' => $this->microActionRepository->canPerform($player->id, $randomMicroAction->id) 
                        ? null 
                        : $this->microActionRepository->getCooldownEndTime($player->id, $randomMicroAction->id),
                ],
                'based_on' => [
                    'energy_level' => $player->energy,
                    'stress_level' => $player->stress,
                    'player_level' => $player->level,
                ]
            ],
            'player_state' => $this->playerStateService->getPlayerStateByProfile($player)
        ];
    }

    /**
     * Персонализирует название и описание микродействия на основе персональных данных пользователя
     */
    private function personalizeMicroAction($microAction, $player): array
    {
        $name = $microAction->name;
        $description = $microAction->description;

        // Если нет ключа персонализации, возвращаем как есть
        if (!$microAction->personalization_key) {
            return [
                'name' => $name,
                'description' => $description,
            ];
        }

        // Маппинг ключей персонализации на поля профиля
        $personalizationMap = [
            'favorite_book' => $player->favorite_book,
            'favorite_movie' => $player->favorite_movie,
            'favorite_song' => $player->favorite_song,
            'favorite_dish' => $player->favorite_dish,
            'best_friend_name' => $player->best_friend_name,
        ];

        $personalValue = $personalizationMap[$microAction->personalization_key] ?? null;

        // Если значение заполнено - заменяем плейсхолдеры
        if ($personalValue) {
            // Заменяем все плейсхолдеры для данного ключа
            $placeholder = '{{' . $microAction->personalization_key . '}}';
            $name = str_replace($placeholder, $personalValue, $name);
            $description = str_replace($placeholder, $personalValue, $description);
        } else {
            // Если значение не заполнено, заменяем на общие формулировки
            $defaultReplacements = [
                'favorite_book' => 'любимую книгу',
                'favorite_movie' => 'любимый фильм',
                'favorite_song' => 'любимую песню',
                'favorite_dish' => 'любимое блюдо',
                'best_friend_name' => 'лучшего друга',
            ];

            $replacement = $defaultReplacements[$microAction->personalization_key] ?? '';
            if ($replacement) {
                $placeholder = '{{' . $microAction->personalization_key . '}}';
                $name = str_replace($placeholder, $replacement, $name);
                $description = str_replace($placeholder, $replacement, $description);
            }
        }

        return [
            'name' => $name,
            'description' => $description,
        ];
    }
}
