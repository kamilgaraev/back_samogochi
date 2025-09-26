<?php

namespace App\Services;

use App\Models\GameConfig;

class GameConfigService
{
    public static function getSituationCooldownSeconds(): int
    {
        return GameConfig::getGameBalance()['situation_cooldown_seconds'] ?? config('game.situation_cooldown_seconds', 0);
    }

    public static function getMicroActionCooldownMinutes(): int
    {
        return GameConfig::getGameBalance()['micro_action_cooldown_minutes'] ?? config('game.micro_action_cooldown_minutes', 30);
    }

    public static function getDailyLoginExperience(): int
    {
        return GameConfig::getGameBalance()['daily_login_experience'] ?? config('game.daily_login_experience', 10);
    }

    public static function getMaxEnergy(): int
    {
        return GameConfig::getGameBalance()['max_energy'] ?? config('game.max_energy', 200);
    }

    public static function getEnergyRegenPerHour(): int
    {
        return GameConfig::getGameBalance()['energy_regen_per_hour'] ?? config('game.energy_regen_per_hour', 1);
    }

    public static function getStressThresholdHigh(): int
    {
        return GameConfig::getGameBalance()['stress_threshold_high'] ?? config('game.stress_threshold_high', 80);
    }

    public static function getStressThresholdLow(): int
    {
        return GameConfig::getGameBalance()['stress_threshold_low'] ?? config('game.stress_threshold_low', 20);
    }

    public static function getExperiencePerLevel(): int
    {
        return GameConfig::getGameBalance()['experience_per_level'] ?? config('game.experience_per_level', 100);
    }
}
