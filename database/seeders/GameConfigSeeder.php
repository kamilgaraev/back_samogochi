<?php

namespace Database\Seeders;

use App\Models\GameConfig;
use Illuminate\Database\Seeder;

class GameConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'key' => 'game_balance',
                'value' => [
                    'daily_login_experience' => 10,
                    'max_energy' => 200,
                    'energy_regen_per_hour' => 1,
                    'stress_threshold_high' => 80,
                    'stress_threshold_low' => 20,
                    'situation_cooldown_hours' => 2
                ],
                'description' => 'Основные игровые константы для баланса',
                'is_active' => true,
            ],
            [
                'key' => 'level_requirements',
                'value' => [
                    ['level' => 1, 'experience' => 0],
                    ['level' => 2, 'experience' => 100],
                    ['level' => 3, 'experience' => 250],
                    ['level' => 4, 'experience' => 450],
                    ['level' => 5, 'experience' => 700],
                    ['level' => 6, 'experience' => 1000],
                    ['level' => 7, 'experience' => 1350],
                    ['level' => 8, 'experience' => 1750],
                    ['level' => 9, 'experience' => 2200],
                    ['level' => 10, 'experience' => 2700],
                ],
                'description' => 'Требования опыта для каждого уровня',
                'is_active' => true,
            ],
            [
                'key' => 'notifications',
                'value' => [
                    'daily_reminder' => [
                        'enabled' => true,
                        'time' => '19:00',
                        'message' => 'Время позаботиться о своем эмоциональном состоянии!'
                    ],
                    'high_stress_alert' => [
                        'enabled' => true,
                        'threshold' => 85,
                        'message' => 'Уровень стресса высокий. Попробуйте технику дыхания.'
                    ]
                ],
                'description' => 'Настройки системы уведомлений',
                'is_active' => true,
            ]
        ];

        foreach ($configs as $config) {
            GameConfig::updateOrCreate(
                ['key' => $config['key']],
                $config
            );
        }
    }
}
