<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerSituation extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'situation_id',
        'selected_option_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }

    public function situation()
    {
        return $this->belongsTo(Situation::class);
    }

    public function situationOption()
    {
        return $this->belongsTo(SituationOption::class, 'selected_option_id');
    }
}
