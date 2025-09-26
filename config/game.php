<?php

use App\Models\GameConfig;

$gameBalance = GameConfig::getGameBalance();

return [
    'situation_cooldown_seconds' => $gameBalance['situation_cooldown_seconds'] ?? 0,
    'micro_action_cooldown_minutes' => $gameBalance['micro_action_cooldown_minutes'] ?? 30,
    'daily_login_experience' => $gameBalance['daily_login_experience'] ?? 10,
    'max_energy' => $gameBalance['max_energy'] ?? 200,
    'energy_regen_per_hour' => $gameBalance['energy_regen_per_hour'] ?? 1,
    'stress_threshold_high' => $gameBalance['stress_threshold_high'] ?? 80,
    'stress_threshold_low' => $gameBalance['stress_threshold_low'] ?? 20,
    'experience_per_level' => $gameBalance['experience_per_level'] ?? 100,
];
