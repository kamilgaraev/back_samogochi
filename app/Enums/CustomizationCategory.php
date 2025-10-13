<?php

namespace App\Enums;

enum CustomizationCategory: string
{
    case WARDROBE = 'wardrobe';
    case FURNITURE = 'furniture';

    public function getLabel(): string
    {
        return match($this) {
            self::WARDROBE => 'Гардероб',
            self::FURNITURE => 'Мебель',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::WARDROBE => '👔',
            self::FURNITURE => '🛋️',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::WARDROBE => 'Элементы одежды и аксессуары',
            self::FURNITURE => 'Предметы интерьера и мебель',
        };
    }

    public static function getForValidation(): string
    {
        return implode(',', array_map(fn($case) => $case->value, self::cases()));
    }
}

