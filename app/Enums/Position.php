<?php

namespace App\Enums;

enum Position: string
{
    case PHONE = 'phone';
    case TABLE = 'table';
    case TV = 'tv';
    case WALL_CLOCK = 'wallClock';
    case LAPTOP = 'lapTop';
    case FRIDGE = 'fridge';
    case TRASH_CAN = 'trashCan';
    case BED = 'bed';
    case MIRROR = 'mirror';

    public static function getForValidation(): string
    {
        return implode(',', array_column(self::cases(), 'value'));
    }

    public function getLabel(): string
    {
        return match($this) {
            self::PHONE => 'Телефон',
            self::TABLE => 'Стол',
            self::TV => 'Телевизор',
            self::WALL_CLOCK => 'Настенные часы',
            self::LAPTOP => 'Ноутбук',
            self::FRIDGE => 'Холодильник',
            self::TRASH_CAN => 'Мусорная корзина',
            self::BED => 'Кровать',
            self::MIRROR => 'Зеркало',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::PHONE => '📱',
            self::TABLE => '🪑',
            self::TV => '📺',
            self::WALL_CLOCK => '🕐',
            self::LAPTOP => '💻',
            self::FRIDGE => '🧊',
            self::TRASH_CAN => '🗑️',
            self::BED => '🛏️',
            self::MIRROR => '🪞',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::PHONE => 'Отображение на мобильном устройстве',
            self::TABLE => 'Действие за столом',
            self::TV => 'Отображение на большом экране',
            self::WALL_CLOCK => 'Действие связанное со временем',
            self::LAPTOP => 'Работа на ноутбуке',
            self::FRIDGE => 'Действие с холодильником',
            self::TRASH_CAN => 'Уборка и очистка',
            self::BED => 'Отдых и сон',
            self::MIRROR => 'Уход за собой',
        };
    }
}

