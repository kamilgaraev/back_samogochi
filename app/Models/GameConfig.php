<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getValue($key, $default = null)
    {
        $config = self::where('key', $key)
            ->where('is_active', true)
            ->first();

        return $config ? $config->value : $default;
    }

    public static function getGameBalance()
    {
        return self::getValue('game_balance', [
            'daily_login_experience' => 10,
            'max_energy' => 200,
            'energy_regen_per_hour' => 1,
            'stress_threshold_high' => 80,
            'stress_threshold_low' => 20,
            'situation_cooldown_seconds' => 0,
            'micro_action_cooldown_minutes' => 30,
            'experience_per_level' => 100
        ]);
    }

    public static function getLevelRequirements()
    {
        return self::getValue('level_requirements', [
            ['level' => 1, 'experience' => 0],
            ['level' => 2, 'experience' => 100],
            ['level' => 3, 'experience' => 250],
        ]);
    }

    public static function getNotificationSettings()
    {
        return self::getValue('notifications', [
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
        ]);
    }
}
