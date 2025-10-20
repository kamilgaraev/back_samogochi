<?php

namespace App\Services;

use App\Models\GameConfig;
use Illuminate\Support\Facades\Log;

class GameConfigService
{
    public static function getSituationCooldownSeconds(): int
    {
        $value = GameConfig::getGameBalance()['situation_cooldown_seconds'] ?? config('game.situation_cooldown_seconds', 0);
        Log::info('GameConfigService::getSituationCooldownSeconds', ['value' => $value]);
        return $value;
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

    public static function getSituationsBeforeSleep(): int
    {
        return GameConfig::getGameBalance()['situations_before_sleep'] ?? config('game.situations_before_sleep', 10);
    }

    public static function getSleepDurationMinutes(): int
    {
        return GameConfig::getGameBalance()['sleep_duration_minutes'] ?? config('game.sleep_duration_minutes', 480);
    }

    public static function isMicroActionsDisabledDuringSleep(): bool
    {
        return GameConfig::getGameBalance()['disable_micro_actions_during_sleep'] ?? config('game.disable_micro_actions_during_sleep', true);
    }

    public static function getLevelRequirements(): array
    {
        return GameConfig::getLevelRequirements();
    }

    public static function calculateLevelFromExperience(int $totalExperience): int
    {
        $requirements = self::getLevelRequirements();
        
        if (empty($requirements)) {
            $expPerLevel = self::getExperiencePerLevel();
            return floor($totalExperience / $expPerLevel) + 1;
        }

        $level = 1;
        foreach ($requirements as $req) {
            if ($totalExperience >= $req['experience']) {
                $level = $req['level'];
            } else {
                break;
            }
        }
        
        return $level;
    }

    public static function getExperienceForLevel(int $level): int
    {
        $requirements = self::getLevelRequirements();
        
        if (empty($requirements)) {
            return ($level - 1) * self::getExperiencePerLevel();
        }

        foreach ($requirements as $req) {
            if ($req['level'] == $level) {
                return $req['experience'];
            }
        }

        $lastReq = end($requirements);
        if ($level > $lastReq['level']) {
            $expPerLevel = self::getExperiencePerLevel();
            return $lastReq['experience'] + (($level - $lastReq['level']) * $expPerLevel);
        }
        
        return 0;
    }

    public static function getExperienceToNextLevel(int $totalExperience, int $currentLevel): int
    {
        $nextLevelExp = self::getExperienceForLevel($currentLevel + 1);
        return max(0, $nextLevelExp - $totalExperience);
    }

    public static function getExperienceInCurrentLevel(int $totalExperience, int $currentLevel): int
    {
        $currentLevelExp = self::getExperienceForLevel($currentLevel);
        return max(0, $totalExperience - $currentLevelExp);
    }
}
