<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerCustomization extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'category_key',
        'selected_item_id',
        'unlocked_items',
        'new_unlocked_items',
    ];

    protected function casts(): array
    {
        return [
            'unlocked_items' => 'array',
            'new_unlocked_items' => 'array',
        ];
    }

    public function player()
    {
        return $this->belongsTo(PlayerProfile::class, 'player_id');
    }

    public function selectedItem()
    {
        return $this->belongsTo(CustomizationItem::class, 'selected_item_id');
    }

    public function isUnlocked(int $itemId): bool
    {
        return in_array($itemId, $this->unlocked_items ?? []);
    }

    public function unlockItem(int $itemId): void
    {
        $unlocked = $this->unlocked_items ?? [];
        
        if (!in_array($itemId, $unlocked)) {
            $unlocked[] = $itemId;
            $this->unlocked_items = $unlocked;
        }
    }

    public function addNewUnlockedItem(int $itemId): void
    {
        $newUnlocked = $this->new_unlocked_items ?? [];
        
        if (!in_array($itemId, $newUnlocked)) {
            $newUnlocked[] = $itemId;
            $this->new_unlocked_items = $newUnlocked;
        }
    }

    public function markAsViewed(array $itemIds): void
    {
        $newUnlocked = $this->new_unlocked_items ?? [];
        $this->new_unlocked_items = array_values(array_diff($newUnlocked, $itemIds));
    }
}

