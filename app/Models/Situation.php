<?php

namespace App\Models;

use App\Enums\SituationCategory;
use App\Enums\DifficultyLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Situation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'difficulty_level',
        'min_level_required',
        'stress_impact',
        'experience_reward',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'category' => SituationCategory::class,
            'difficulty_level' => DifficultyLevel::class,
        ];
    }

    public function options()
    {
        return $this->hasMany(SituationOption::class);
    }

    public function playerSituations()
    {
        return $this->hasMany(PlayerSituation::class);
    }
}
