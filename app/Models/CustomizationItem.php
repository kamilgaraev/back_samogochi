<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomizationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_key',
        'category',
        'name',
        'description',
        'unlock_level',
        'order',
        'is_default',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unlock_level' => 'integer',
            'order' => 'integer',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function playerCustomizations()
    {
        return $this->hasMany(PlayerCustomization::class, 'selected_item_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $categoryKey)
    {
        return $query->where('category_key', $categoryKey);
    }

    public function scopeUnlockedAtLevel($query, int $level)
    {
        return $query->where('unlock_level', '<=', $level);
    }
}

