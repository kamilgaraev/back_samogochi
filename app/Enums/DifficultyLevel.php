<?php

namespace App\Enums;

enum DifficultyLevel: int
{
    case EASY = 1;
    case MEDIUM = 2;
    case HARD = 3;
    case EXPERT = 4;
    case MASTER = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::EASY => 'Легкая',
            self::MEDIUM => 'Средняя', 
            self::HARD => 'Сложная',
            self::EXPERT => 'Экспертная',
            self::MASTER => 'Мастерская',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::EASY => 'Простые ситуации для новичков, минимальный стресс',
            self::MEDIUM => 'Обычные жизненные ситуации среднего уровня сложности',
            self::HARD => 'Сложные ситуации, требующие опыта и навыков',
            self::EXPERT => 'Очень сложные ситуации для опытных игроков',
            self::MASTER => 'Мастерский уровень, максимальная сложность и награды',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EASY => '#4CAF50',     // Зеленый
            self::MEDIUM => '#2196F3',   // Синий
            self::HARD => '#FF9800',     // Оранжевый
            self::EXPERT => '#F44336',   // Красный
            self::MASTER => '#9C27B0',   // Фиолетовый
        };
    }

    public function getMinLevel(): int
    {
        return match ($this) {
            self::EASY => 1,
            self::MEDIUM => 1,
            self::HARD => 2,
            self::EXPERT => 3,
            self::MASTER => 5,
        };
    }

    public function getTypicalStressImpact(): array
    {
        return match ($this) {
            self::EASY => ['min' => 5, 'max' => 10],
            self::MEDIUM => ['min' => 10, 'max' => 15],
            self::HARD => ['min' => 15, 'max' => 25],
            self::EXPERT => ['min' => 20, 'max' => 30],
            self::MASTER => ['min' => 25, 'max' => 40],
        };
    }

    public function getTypicalExperienceReward(): array
    {
        return match ($this) {
            self::EASY => ['min' => 10, 'max' => 20],
            self::MEDIUM => ['min' => 15, 'max' => 25],
            self::HARD => ['min' => 20, 'max' => 35],
            self::EXPERT => ['min' => 30, 'max' => 45],
            self::MASTER => ['min' => 40, 'max' => 60],
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::EASY => '⭐',
            self::MEDIUM => '⭐⭐',
            self::HARD => '⭐⭐⭐',
            self::EXPERT => '⭐⭐⭐⭐',
            self::MASTER => '⭐⭐⭐⭐⭐',
        };
    }

    public static function getForLevel(int $playerLevel): array
    {
        return array_filter(
            self::cases(),
            fn($difficulty) => $difficulty->getMinLevel() <= $playerLevel
        );
    }
}