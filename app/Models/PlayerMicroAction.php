<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerMicroAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'micro_action_id',
        'completed_at',
        'energy_gained',
        'experience_gained',
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

    public function microAction()
    {
        return $this->belongsTo(MicroAction::class);
    }
}
