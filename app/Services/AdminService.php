<?php

namespace App\Services;

use App\Models\GameConfig;
use App\Models\Situation;
use App\Models\SituationOption;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AdminService
{
    public function getConfigs(): array
    {
        $configs = GameConfig::orderBy('key')->get();

        return [
            'success' => true,
            'data' => [
                'configs' => $configs->map(function ($config) {
                    return [
                        'id' => $config->id,
                        'key' => $config->key,
                        'value' => $config->value,
                        'description' => $config->description,
                        'is_active' => $config->is_active,
                        'created_at' => $config->created_at,
                        'updated_at' => $config->updated_at,
                    ];
                }),
                'total' => $configs->count()
            ]
        ];
    }

    public function updateConfig(string $key, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $config = GameConfig::where('key', $key)->first();
            
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Конфигурация не найдена'
                ];
            }

            $oldValue = $config->value;
            
            // Обработка JSON значения (может быть двойной JSON!)
            $value = $data['value'];
            
            // Если строка - пытаемся распарсить JSON (возможно несколько раз)
            if (is_string($value)) {
                // Первый парсинг
                try {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                        
                        // Если результат все еще строка - парсим еще раз (двойной JSON)
                        if (is_string($value)) {
                            $doubleDecoded = json_decode($value, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $value = $doubleDecoded;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Если не JSON, оставляем как есть
                }
            }
            
            // Приводим к правильным типам для игрового баланса
            if ($key === 'game_balance' && is_array($value)) {
                $value = $this->normalizeGameBalanceTypes($value);
            }

            // Отладочные логи убраны после успешного тестирования

            $config->update([
                'value' => $value,
                'description' => $data['description'] ?? $config->description,
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : $config->is_active,
                'created_by' => $userId,
            ]);

            ActivityLog::logEvent(\App\Enums\ActivityEventType::ADMIN_CONFIG_UPDATED->value, [
                'config_key' => $key,
                'old_value' => $oldValue,
                'new_value' => $value,
            ], $userId);

            DB::commit();

            // Очищаем все кэши после коммита
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');

            // Принудительно перезагружаем модель из базы
            $config = $config->fresh();

            // Обновляем энергию игроков при изменении max_energy
            if ($key === 'game_balance' && is_array($value) && isset($value['max_energy'])) {
                $this->updatePlayersMaxEnergy($value['max_energy']);
            }

            return [
                'success' => true,
                'message' => 'Конфигурация успешно обновлена',
                'data' => $config
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении конфигурации: ' . $e->getMessage()
            ];
        }
    }

    public function getSituations(array $filters = []): array
    {
        $query = Situation::with(['options']);

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['difficulty_level'])) {
            $query->where('difficulty_level', $filters['difficulty_level']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $situations = $query->orderBy('created_at', 'desc')->paginate(15);

        return [
            'success' => true,
            'data' => [
                'situations' => $situations->items(),
                'pagination' => [
                    'current_page' => $situations->currentPage(),
                    'total_pages' => $situations->lastPage(),
                    'per_page' => $situations->perPage(),
                    'total' => $situations->total(),
                ]
            ]
        ];
    }

    public function createSituation(array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $situation = Situation::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category'],
                'difficulty_level' => $data['difficulty_level'],
                'min_level_required' => $data['min_level_required'] ?? 1,
                'stress_impact' => $data['stress_impact'],
                'experience_reward' => $data['experience_reward'],
                'is_active' => $data['is_active'] ?? true,
                'position' => $data['position'] ?? 'desktop',
            ]);

            if (isset($data['options'])) {
                foreach ($data['options'] as $index => $optionData) {
                    SituationOption::create([
                        'situation_id' => $situation->id,
                        'text' => $optionData['text'],
                        'stress_change' => $optionData['stress_change'],
                        'experience_reward' => $optionData['experience_reward'],
                        'energy_cost' => $optionData['energy_cost'] ?? 0,
                        'min_level_required' => $optionData['min_level_required'] ?? 1,
                        'order' => $index + 1,
                    ]);
                }
            }

            ActivityLog::logEvent(\App\Enums\ActivityEventType::ADMIN_SITUATION_CREATED->value, [
                'situation_id' => $situation->id,
                'title' => $situation->title,
                'category' => $situation->category,
            ], $userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Ситуация успешно создана',
                'data' => $situation->load('options')
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при создании ситуации: ' . $e->getMessage()
            ];
        }
    }

    public function updateSituation(int $id, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $situation = Situation::with('options')->find($id);
            
            if (!$situation) {
                return [
                    'success' => false,
                    'message' => 'Ситуация не найдена'
                ];
            }

            $situation->update([
                'title' => $data['title'] ?? $situation->title,
                'description' => $data['description'] ?? $situation->description,
                'category' => $data['category'] ?? $situation->category,
                'difficulty_level' => $data['difficulty_level'] ?? $situation->difficulty_level,
                'min_level_required' => $data['min_level_required'] ?? $situation->min_level_required,
                'stress_impact' => $data['stress_impact'] ?? $situation->stress_impact,
                'experience_reward' => $data['experience_reward'] ?? $situation->experience_reward,
                'is_active' => $data['is_active'] ?? $situation->is_active,
                'position' => $data['position'] ?? $situation->position,
            ]);

            if (isset($data['options'])) {
                $situation->options()->delete();
                
                foreach ($data['options'] as $index => $optionData) {
                    SituationOption::create([
                        'situation_id' => $situation->id,
                        'text' => $optionData['text'],
                        'stress_change' => $optionData['stress_change'],
                        'experience_reward' => $optionData['experience_reward'],
                        'energy_cost' => $optionData['energy_cost'] ?? 0,
                        'min_level_required' => $optionData['min_level_required'] ?? 1,
                        'order' => $index + 1,
                    ]);
                }
            }

            ActivityLog::logEvent(\App\Enums\ActivityEventType::ADMIN_SITUATION_UPDATED->value, [
                'situation_id' => $situation->id,
                'title' => $situation->title,
            ], $userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Ситуация успешно обновлена',
                'data' => $situation->fresh()->load('options')
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении ситуации: ' . $e->getMessage()
            ];
        }
    }

    public function deleteSituation(int $id, int $userId): array
    {
        try {
            DB::beginTransaction();

            $situation = Situation::find($id);
            
            if (!$situation) {
                return [
                    'success' => false,
                    'message' => 'Ситуация не найдена'
                ];
            }

            $situationTitle = $situation->title;
            
            $situation->delete();

            ActivityLog::logEvent(\App\Enums\ActivityEventType::ADMIN_SITUATION_DELETED->value, [
                'situation_id' => $id,
                'title' => $situationTitle,
            ], $userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Ситуация успешно удалена'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при удалении ситуации: ' . $e->getMessage()
            ];
        }
    }

    private function normalizeGameBalanceTypes(array $data): array
    {
        $typeMap = [
            'daily_login_experience' => 'int',
            'max_energy' => 'int',
            'energy_regen_per_hour' => 'int',
            'stress_threshold_high' => 'int',
            'stress_threshold_low' => 'int',
            'situation_cooldown_seconds' => 'int',
            'micro_action_cooldown_minutes' => 'int',
            'experience_per_level' => 'int'
        ];

        foreach ($data as $key => $value) {
            if (isset($typeMap[$key])) {
                switch ($typeMap[$key]) {
                    case 'int':
                        $data[$key] = (int) $value;
                        break;
                    case 'float':
                        $data[$key] = (float) $value;
                        break;
                    case 'bool':
                        $data[$key] = (bool) $value;
                        break;
                }
            }
        }

        return $data;
    }

    private function updatePlayersMaxEnergy(int $maxEnergy): void
    {
        try {
            // Обновляем энергию игроков при изменении max_energy
            // Для тестирования: устанавливаем всем полную энергию
            // Для продакшена: ограничиваем превышение ->where('energy', '>', $maxEnergy)->update(['energy' => $maxEnergy])
            \App\Models\PlayerProfile::query()->update(['energy' => $maxEnergy]);
            
        } catch (\Exception $e) {
            \Log::error("Failed to update players energy: " . $e->getMessage());
        }
    }

    // === МИКРО-ДЕЙСТВИЯ ===

    public function getMicroActions(array $filters = []): array
    {
        $query = \App\Models\MicroAction::query();

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['unlock_level'])) {
            $query->where('unlock_level', '<=', $filters['unlock_level']);
        }

        $microActions = $query->orderBy('category')->orderBy('name')->paginate(15);

        return [
            'success' => true,
            'data' => [
                'micro_actions' => $microActions->items(),
                'pagination' => [
                    'current_page' => $microActions->currentPage(),
                    'total_pages' => $microActions->lastPage(),
                    'per_page' => $microActions->perPage(),
                    'total' => $microActions->total(),
                ]
            ]
        ];
    }

    public function createMicroAction(array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $microAction = \App\Models\MicroAction::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'energy_reward' => $data['energy_reward'] ?? 0,
                'experience_reward' => $data['experience_reward'] ?? 0,
                'cooldown_minutes' => $data['cooldown_minutes'] ?? 60,
                'unlock_level' => $data['unlock_level'] ?? 1,
                'category' => $data['category'],
                'position' => $data['position'] ?? 'desktop',
                'is_active' => $data['is_active'] ?? true,
            ]);

            ActivityLog::logEvent(\App\Enums\ActivityEventType::ADMIN_SITUATION_CREATED->value, [
                'micro_action_id' => $microAction->id,
                'name' => $microAction->name,
                'category' => $microAction->category,
            ], $userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Микро-действие успешно создано',
                'data' => $microAction
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при создании микро-действия: ' . $e->getMessage()
            ];
        }
    }

    public function updateMicroAction(int $id, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $microAction = \App\Models\MicroAction::find($id);
            
            if (!$microAction) {
                return [
                    'success' => false,
                    'message' => 'Микро-действие не найдено'
                ];
            }

            $microAction->update([
                'name' => $data['name'] ?? $microAction->name,
                'description' => $data['description'] ?? $microAction->description,
                'energy_reward' => $data['energy_reward'] ?? $microAction->energy_reward,
                'experience_reward' => $data['experience_reward'] ?? $microAction->experience_reward,
                'cooldown_minutes' => $data['cooldown_minutes'] ?? $microAction->cooldown_minutes,
                'unlock_level' => $data['unlock_level'] ?? $microAction->unlock_level,
                'category' => $data['category'] ?? $microAction->category,
                'position' => $data['position'] ?? $microAction->position,
                'is_active' => $data['is_active'] ?? $microAction->is_active,
            ]);

            ActivityLog::logEvent(\App\Enums\ActivityEventType::ADMIN_SITUATION_UPDATED->value, [
                'micro_action_id' => $microAction->id,
                'name' => $microAction->name,
            ], $userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Микро-действие успешно обновлено',
                'data' => $microAction->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении микро-действия: ' . $e->getMessage()
            ];
        }
    }

    public function deleteMicroAction(int $id, int $userId): array
    {
        try {
            DB::beginTransaction();

            $microAction = \App\Models\MicroAction::find($id);
            
            if (!$microAction) {
                return [
                    'success' => false,
                    'message' => 'Микро-действие не найдено'
                ];
            }

            $microActionName = $microAction->name;
            
            $microAction->delete();

            ActivityLog::logEvent(\App\Enums\ActivityEventType::ADMIN_SITUATION_DELETED->value, [
                'micro_action_id' => $id,
                'name' => $microActionName,
            ], $userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Микро-действие успешно удалено'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Ошибка при удалении микро-действия: ' . $e->getMessage()
            ];
        }
    }
}
