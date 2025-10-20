<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level',
        'total_experience',
        'energy',
        'stress',
        'anxiety',
        'last_login',
        'last_daily_reward',
        'consecutive_days',
        'completed_situations_since_sleep',
        'sleeping_until',
        'favorite_song',
        'favorite_movie',
        'favorite_book',
        'favorite_dish',
        'best_friend_name',
    ];

    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'last_daily_reward' => 'datetime',
            'sleeping_until' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function playerSituations()
    {
        return $this->hasMany(PlayerSituation::class, 'player_id');
    }

    public function playerMicroActions()
    {
        return $this->hasMany(PlayerMicroAction::class, 'player_id');
    }

    public function getCurrentLevelAttribute()
    {
        return floor($this->total_experience / 100) + 1;
    }

    public function getExperienceToNextLevelAttribute()
    {
        return 100 - ($this->total_experience % 100);
    }

    public function updateLastLogin()
    {
        $this->update(['last_login' => now()]);
    }

    public function canReceiveDailyReward()
    {
        if (!$this->last_daily_reward) {
            return true;
        }

        return $this->last_daily_reward->startOfDay() < now()->startOfDay();
    }

    public function recalculateLevel(): bool
    {
        $calculatedLevel = floor($this->total_experience / 100) + 1;
        
        if ($calculatedLevel !== $this->level) {
            $oldLevel = $this->level;
            $this->update(['level' => $calculatedLevel]);
            
            if ($calculatedLevel > $oldLevel) {
                $customizationService = app(\App\Services\CustomizationService::class);
                $customizationService->unlockItemsForLevel($this->id, $calculatedLevel);
            }
            
            return true;
        }
        
        return false;
    }

    public function addExperience($amount)
    {
        $oldLevel = $this->level;
        $newTotalExperience = $this->total_experience + $amount;
        $newLevel = floor($newTotalExperience / 100) + 1;

        $this->update([
            'total_experience' => $newTotalExperience,
            'level' => $newLevel
        ]);

        $this->refresh();

        if ($newLevel > $oldLevel) {
            event('player.level_up', ['player' => $this, 'old_level' => $oldLevel, 'new_level' => $newLevel]);
            
            $customizationService = app(\App\Services\CustomizationService::class);
            $customizationService->unlockItemsForLevel($this->id, $newLevel);
        }

        return $this;
    }

    public function updateStress($amount)
    {
        $newStress = max(0, min(100, $this->stress + $amount));
        $this->update(['stress' => $newStress]);
        return $this;
    }

    public function updateEnergy($amount)
    {
        $newEnergy = max(0, min(200, $this->energy + $amount));
        $this->update(['energy' => $newEnergy]);
        return $this;
    }

    public function isSleeping(): bool
    {
        if (!$this->sleeping_until) {
            return false;
        }

        if (now()->greaterThanOrEqualTo($this->sleeping_until)) {
            $this->wakeUp();
            return false;
        }

        return true;
    }

    public function putToSleep(): void
    {
        $sleepConfig = GameConfig::getGameBalance();
        $sleepDurationHours = $sleepConfig['sleep_duration_hours'] ?? 8;

        $this->update([
            'sleeping_until' => now()->addHours($sleepDurationHours),
            'completed_situations_since_sleep' => 0,
        ]);
    }

    public function wakeUp(): void
    {
        $this->update([
            'sleeping_until' => null,
        ]);
    }

    public function incrementSituationsCounter(): void
    {
        $this->increment('completed_situations_since_sleep');

        $sleepConfig = GameConfig::getGameBalance();
        $situationsBeforeSleep = $sleepConfig['situations_before_sleep'] ?? 10;

        if ($this->completed_situations_since_sleep >= $situationsBeforeSleep) {
            $this->putToSleep();
        }
    }

    public function getSleepInfo(): array
    {
        if (!$this->isSleeping()) {
            $sleepConfig = GameConfig::getGameBalance();
            $situationsBeforeSleep = $sleepConfig['situations_before_sleep'] ?? 10;
            
            return [
                'is_sleeping' => false,
                'sleeping_until' => null,
                'situations_until_sleep' => $situationsBeforeSleep - $this->completed_situations_since_sleep,
                'completed_situations' => $this->completed_situations_since_sleep,
                'situations_limit' => $situationsBeforeSleep,
            ];
        }

        return [
            'is_sleeping' => true,
            'sleeping_until' => $this->sleeping_until,
            'time_remaining_seconds' => (int) now()->diffInSeconds($this->sleeping_until, false),
            'time_remaining_minutes' => (int) now()->diffInMinutes($this->sleeping_until, false),
        ];
    }
}
