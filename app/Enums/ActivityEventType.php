<?php

namespace App\Enums;

enum ActivityEventType: string
{
    // Пользовательские события
    case USER_LOGIN = 'user.login';
    case USER_LOGOUT = 'user.logout';
    case USER_REGISTRATION = 'user.registration';
    case USER_LOGIN_FAILED = 'user.login_failed';
    case USER_PASSWORD_RESET_REQUESTED = 'user.password_reset_requested';
    case USER_PASSWORD_RESET = 'user.password_reset';

    // События игрока
    case PLAYER_PROFILE_UPDATED = 'player.profile_updated';
    case PLAYER_DAILY_REWARD_CLAIMED = 'player.daily_reward_claimed';
    case PLAYER_EXPERIENCE_ADDED = 'player.experience_added';
    case PLAYER_ENERGY_UPDATED = 'player.energy_updated';
    case PLAYER_STRESS_UPDATED = 'player.stress_updated';
    case PLAYER_LEVEL_UP = 'player.level_up';

    // События ситуаций
    case SITUATION_COMPLETED = 'situation.completed';
    case SITUATION_STARTED = 'situation.started';

    // События микродействий
    case MICRO_ACTION_PERFORMED = 'micro_action.performed';

    // Административные события
    case ADMIN_CONFIG_UPDATED = 'admin.config_updated';
    case ADMIN_SITUATION_CREATED = 'admin.situation_created';
    case ADMIN_SITUATION_UPDATED = 'admin.situation_updated';
    case ADMIN_SITUATION_DELETED = 'admin.situation_deleted';

    public function getLabel(): string
    {
        return match ($this) {
            self::USER_LOGIN => 'Вход пользователя',
            self::USER_LOGOUT => 'Выход пользователя',
            self::USER_REGISTRATION => 'Регистрация пользователя',
            self::USER_LOGIN_FAILED => 'Неудачная попытка входа',
            self::USER_PASSWORD_RESET_REQUESTED => 'Запрос сброса пароля',
            self::USER_PASSWORD_RESET => 'Сброс пароля',
            
            self::PLAYER_PROFILE_UPDATED => 'Обновление профиля игрока',
            self::PLAYER_DAILY_REWARD_CLAIMED => 'Получение ежедневной награды',
            self::PLAYER_EXPERIENCE_ADDED => 'Добавление опыта',
            self::PLAYER_ENERGY_UPDATED => 'Изменение энергии',
            self::PLAYER_STRESS_UPDATED => 'Изменение уровня стресса',
            self::PLAYER_LEVEL_UP => 'Повышение уровня',
            
            self::SITUATION_COMPLETED => 'Завершение ситуации',
            self::SITUATION_STARTED => 'Начало ситуации',
            
            self::MICRO_ACTION_PERFORMED => 'Выполнение микродействия',
            
            self::ADMIN_CONFIG_UPDATED => 'Обновление конфигурации',
            self::ADMIN_SITUATION_CREATED => 'Создание ситуации',
            self::ADMIN_SITUATION_UPDATED => 'Обновление ситуации',
            self::ADMIN_SITUATION_DELETED => 'Удаление ситуации',
        };
    }

    public function getCategory(): string
    {
        return match ($this) {
            self::USER_LOGIN, 
            self::USER_LOGOUT, 
            self::USER_REGISTRATION, 
            self::USER_LOGIN_FAILED,
            self::USER_PASSWORD_RESET_REQUESTED,
            self::USER_PASSWORD_RESET => 'auth',
            
            self::PLAYER_PROFILE_UPDATED,
            self::PLAYER_DAILY_REWARD_CLAIMED,
            self::PLAYER_EXPERIENCE_ADDED,
            self::PLAYER_ENERGY_UPDATED,
            self::PLAYER_STRESS_UPDATED,
            self::PLAYER_LEVEL_UP => 'player',
            
            self::SITUATION_COMPLETED,
            self::SITUATION_STARTED => 'situation',
            
            self::MICRO_ACTION_PERFORMED => 'micro_action',
            
            self::ADMIN_CONFIG_UPDATED,
            self::ADMIN_SITUATION_CREATED,
            self::ADMIN_SITUATION_UPDATED,
            self::ADMIN_SITUATION_DELETED => 'admin',
        };
    }

    public function getImportance(): string
    {
        return match ($this) {
            self::USER_REGISTRATION,
            self::PLAYER_LEVEL_UP,
            self::USER_PASSWORD_RESET => 'high',
            
            self::USER_LOGIN_FAILED,
            self::ADMIN_SITUATION_DELETED => 'medium',
            
            default => 'low',
        };
    }

    public static function getUserEvents(): array
    {
        return [
            self::USER_LOGIN,
            self::USER_LOGOUT,
            self::USER_REGISTRATION,
            self::USER_LOGIN_FAILED,
            self::USER_PASSWORD_RESET_REQUESTED,
            self::USER_PASSWORD_RESET,
        ];
    }

    public static function getPlayerEvents(): array
    {
        return [
            self::PLAYER_PROFILE_UPDATED,
            self::PLAYER_DAILY_REWARD_CLAIMED,
            self::PLAYER_EXPERIENCE_ADDED,
            self::PLAYER_ENERGY_UPDATED,
            self::PLAYER_STRESS_UPDATED,
            self::PLAYER_LEVEL_UP,
        ];
    }

    public static function getGameplayEvents(): array
    {
        return [
            self::SITUATION_COMPLETED,
            self::SITUATION_STARTED,
            self::MICRO_ACTION_PERFORMED,
        ];
    }

    public static function getAdminEvents(): array
    {
        return [
            self::ADMIN_CONFIG_UPDATED,
            self::ADMIN_SITUATION_CREATED,
            self::ADMIN_SITUATION_UPDATED,
            self::ADMIN_SITUATION_DELETED,
        ];
    }
}
