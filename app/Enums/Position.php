<?php

namespace App\Enums;

enum Position: string
{
    case DESKTOP = 'desktop';
    case PHONE = 'phone';
    case TABLET = 'tablet';
    case TV = 'tv';
    case SPEAKER = 'speaker';
    case BOOKSHELF = 'bookshelf';
    case KITCHEN = 'kitchen';
    case TABLE = 'table';
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
            self::DESKTOP => 'Компьютер',
            self::PHONE => 'Телефон',
            self::TABLET => 'Планшет',
            self::TV => 'Телевизор',
            self::SPEAKER => 'Колонка',
            self::BOOKSHELF => 'Книжная полка',
            self::KITCHEN => 'Кухня',
            self::TABLE => 'Стол',
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
            self::DESKTOP => '💻',
            self::PHONE => '📱',
            self::TABLET => '📊',
            self::TV => '📺',
            self::SPEAKER => '🔊',
            self::BOOKSHELF => '📚',
            self::KITCHEN => '🍳',
            self::TABLE => '🪑',
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
            self::DESKTOP => 'Отображение на компьютере',
            self::PHONE => 'Отображение на мобильном устройстве',
            self::TABLET => 'Отображение на планшете',
            self::TV => 'Отображение на большом экране',
            self::SPEAKER => 'Голосовое взаимодействие через колонку',
            self::BOOKSHELF => 'Действие связанное с чтением',
            self::KITCHEN => 'Действие на кухне',
            self::TABLE => 'Действие за столом',
            self::WALL_CLOCK => 'Действие связанное со временем',
            self::LAPTOP => 'Работа на ноутбуке',
            self::FRIDGE => 'Действие с холодильником',
            self::TRASH_CAN => 'Уборка и очистка',
            self::BED => 'Отдых и сон',
            self::MIRROR => 'Уход за собой',
        };
    }
}

