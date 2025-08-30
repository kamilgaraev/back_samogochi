<?php

namespace App\Models;

use App\Enums\MicroActionCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MicroAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'energy_reward',
        'experience_reward',
        'cooldown_minutes',
        'unlock_level',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'category' => MicroActionCategory::class,
        ];
    }

    public function playerMicroActions()
    {
        return $this->hasMany(PlayerMicroAction::class);
    }
}
