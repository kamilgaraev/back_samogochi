<?php

return [
    'situation_cooldown_hours' => (int) env('GAME_SITUATION_COOLDOWN_HOURS', 2),
    'micro_action_cooldown_minutes' => (int) env('GAME_MICRO_ACTION_COOLDOWN_MINUTES', 30),
    'daily_login_experience' => (int) env('GAME_DAILY_LOGIN_EXPERIENCE', 10),
    'max_energy' => (int) env('GAME_MAX_ENERGY', 200),
    'energy_regen_per_hour' => (int) env('GAME_ENERGY_REGEN_PER_HOUR', 1),
    'stress_threshold_high' => (int) env('GAME_STRESS_THRESHOLD_HIGH', 80),
    'stress_threshold_low' => (int) env('GAME_STRESS_THRESHOLD_LOW', 20),
    'experience_per_level' => (int) env('GAME_EXPERIENCE_PER_LEVEL', 100),
];
