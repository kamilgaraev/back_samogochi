<?php

namespace App\Services;

use App\Models\GameConfig;
use App\Models\Situation;
use App\Models\SituationOption;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

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
            
            // Обработка JSON значения
            $value = $data['value'];
            if (is_string($value)) {
                try {
                    $value = json_decode($value, true);
                    
                    // Приводим к правильным типам для игрового баланса
                    if ($key === 'game_balance' && is_array($value)) {
                        $value = $this->normalizeGameBalanceTypes($value);
                    }
                } catch (\Exception $e) {
                    // Если не JSON, оставляем как есть
                }
            }

            // Логирование для отладки
            \Log::info('AdminService: updateConfig', [
                'key' => $key,
                'original_value' => $data['value'],
                'parsed_value' => $value,
                'value_type' => gettype($value)
            ]);

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

            // Очищаем кэш конфигураций
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Конфигурация успешно обновлена',
                'data' => $config->fresh()
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
            'situation_cooldown_hours' => 'int'
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
}
