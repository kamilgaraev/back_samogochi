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

    public function addExperience($amount)
    {
        $oldLevel = $this->current_level;
        $this->increment('total_experience', $amount);
        $newLevel = $this->current_level;

        if ($newLevel > $oldLevel) {
            event('player.level_up', ['player' => $this, 'old_level' => $oldLevel, 'new_level' => $newLevel]);
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
}
