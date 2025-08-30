<?php

namespace App\Enums;

enum MicroActionCategory: string
{
    case RELAXATION = 'relaxation';
    case EXERCISE = 'exercise';
    case CREATIVITY = 'creativity';
    case SOCIAL = 'social';

    public function getLabel(): string
    {
        return match ($this) {
            self::RELAXATION => 'Релаксация',
            self::EXERCISE => 'Физические упражнения',
            self::CREATIVITY => 'Творчество',
            self::SOCIAL => 'Социальные активности',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::RELAXATION => 'Техники снятия напряжения и успокоения',
            self::EXERCISE => 'Физическая активность для снятия стресса',
            self::CREATIVITY => 'Творческие занятия для переключения внимания',
            self::SOCIAL => 'Взаимодействие с людьми для эмоциональной поддержки',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::RELAXATION => '🧘',
            self::EXERCISE => '💪',
            self::CREATIVITY => '🎨',
            self::SOCIAL => '👫',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::RELAXATION => '#9C27B0', // Фиолетовый
            self::EXERCISE => '#4CAF50',   // Зеленый
            self::CREATIVITY => '#FF9800', // Оранжевый
            self::SOCIAL => '#2196F3',     // Синий
        };
    }

    public function getTypicalEnergyReward(): int
    {
        return match ($this) {
            self::RELAXATION => 15,
            self::EXERCISE => 20,
            self::CREATIVITY => 10,
            self::SOCIAL => 12,
        };
    }

    public function getTypicalDuration(): int
    {
        return match ($this) {
            self::RELAXATION => 10,  // минут
            self::EXERCISE => 30,    // минут
            self::CREATIVITY => 45,  // минут
            self::SOCIAL => 60,      // минут
        };
    }

    public static function getAll(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function getForValidation(): string
    {
        return implode(',', self::getAll());
    }
}
