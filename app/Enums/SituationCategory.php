<?php

namespace App\Enums;

enum SituationCategory: string
{
    case WORK = 'work';
    case STUDY = 'study';
    case PERSONAL = 'personal';
    case HEALTH = 'health';

    public function getLabel(): string
    {
        return match ($this) {
            self::WORK => 'Работа',
            self::STUDY => 'Учеба',
            self::PERSONAL => 'Личная жизнь',
            self::HEALTH => 'Здоровье',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::WORK => 'Рабочие ситуации: дедлайны, презентации, конфликты с коллегами',
            self::STUDY => 'Учебные ситуации: экзамены, проекты, публичные выступления',
            self::PERSONAL => 'Личные отношения: конфликты с друзьями, семейные проблемы',
            self::HEALTH => 'Здоровье и самочувствие: стресс, сон, концентрация, тревожность',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::WORK => '💼',
            self::STUDY => '📚',
            self::PERSONAL => '👥',
            self::HEALTH => '❤️',
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
