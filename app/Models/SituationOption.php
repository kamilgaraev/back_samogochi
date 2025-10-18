<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SituationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'situation_id',
        'text',
        'stress_change',
        'experience_reward',
        'energy_cost',
        'min_level_required',
        'is_available',
        'order',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function situation()
    {
        return $this->belongsTo(Situation::class);
    }

    public function playerSituations()
    {
        return $this->hasMany(PlayerSituation::class, 'selected_option_id');
    }
}
