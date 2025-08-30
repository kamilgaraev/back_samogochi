# API Endpoints - Профиль игрока

Реализованы все эндпоинты для работы с профилем игрока согласно многослойной архитектуре GDD.

## 📋 Реализованные эндпоинты:

### Базовые операции с профилем

#### `GET /api/player/profile`
Получение полного профиля игрока со статистикой уровня.

**Авторизация:** Требуется JWT токен

**Ответ:**
```json
{
  "success": true,
  "message": "Профиль игрока успешно получен",
  "data": {
    "id": 1,
    "user_id": 1,
    "level": 3,
    "total_experience": 275,
    "experience_in_current_level": 75,
    "experience_to_next_level": 25,
    "level_progress_percentage": 75.0,
    "energy": 150,
    "max_energy": 200,
    "energy_percentage": 75.0,
    "stress": 45,
    "stress_status": "normal",
    "anxiety": 30,
    "consecutive_days": 5,
    "can_receive_daily_reward": true
  }
}
```

#### `PUT /api/player/profile`
Обновление профиля игрока (ограниченные поля для безопасности).

**Авторизация:** Требуется JWT токен

**Параметры:**
- `stress` (опционально): integer, 0-100
- `anxiety` (опционально): integer, 0-100

**Пример запроса:**
```json
{
  "stress": 35,
  "anxiety": 25
}
```

### Статистика и аналитика

#### `GET /api/player/stats`
Подробная статистика игрока с аналитикой активности.

**Ответ:**
```json
{
  "success": true,
  "data": {
    "total_situations_completed": 12,
    "total_micro_actions_performed": 25,
    "today_situations": 2,
    "today_micro_actions": 5,
    "average_stress_week": 42.3,
    "current_streak": 5,
    "level_progress": {
      "current_level": 3,
      "experience_in_level": 75,
      "experience_to_next": 25,
      "progress_percentage": 75.0
    },
    "energy_percentage": 75.0,
    "stress_status": "normal"
  }
}
```

#### `GET /api/player/progress`
Прогресс игрока с достижениями и недавней активностью.

**Ответ:**
```json
{
  "success": true,
  "data": {
    "current_level": 3,
    "total_experience": 275,
    "experience_to_next_level": 25,
    "level_progress_percentage": 75.0,
    "achievements": [
      {
        "id": "first_situation",
        "title": "Первый шаг",
        "description": "Прошли первую стрессовую ситуацию",
        "unlocked_at": "2024-01-15T10:30:00Z"
      }
    ],
    "recent_activity": [
      {
        "type": "situation_completed",
        "title": "Стрессовая ситуация на работе",
        "timestamp": "2024-01-15T14:20:00Z",
        "experience_gained": 15
      }
    ]
  }
}
```

### Игровая механика

#### `POST /api/player/daily-reward`
Получение ежедневной награды (если доступна).

**Ответ:**
```json
{
  "success": true,
  "message": "Ежедневная награда получена!",
  "data": {
    "experience_gained": 10,
    "bonus_experience": 5,
    "consecutive_days": 6
  }
}
```

#### `POST /api/player/add-experience`
Добавление опыта игроку (для системных операций).

**Параметры:**
- `amount`: integer, 1-1000 (обязательно)
- `reason`: string, описание причины (опционально)

**Пример ответа при повышении уровня:**
```json
{
  "success": true,
  "message": "Поздравляем! Вы достигли нового уровня!",
  "level_up": true,
  "data": {
    "experience_added": 50,
    "old_level": 2,
    "new_level": 3
  }
}
```

#### `POST /api/player/update-energy`
Изменение энергии игрока.

**Параметры:**
- `amount`: integer, -200 до +200

**Пример запроса:**
```json
{
  "amount": 15
}
```

#### `POST /api/player/update-stress`
Изменение уровня стресса игрока.

**Параметры:**
- `amount`: integer, -100 до +100

**Ответ:**
```json
{
  "success": true,
  "message": "Уровень стресса успешно обновлен",
  "data": {
    "old_stress": 50,
    "new_stress": 35,
    "change": -15,
    "stress_status": "normal"
  }
}
```

## 🏗️ Архитектура

Реализована строгая многослойная архитектура:

### Controllers (`PlayerController.php`)
- HTTP запросы и валидация
- Формирование API ответов
- Обработка ошибок

### Services (`PlayerService.php`)
- Бизнес-логика игры
- Расчет характеристик и прогресса
- Управление транзакциями

### Repositories (`PlayerRepository.php`)
- Работа с базой данных
- Сложные запросы и аналитика
- Кэширование данных

## 🔐 Безопасность

- Все эндпоинты требуют JWT авторизацию
- Валидация всех входных данных
- Ограничение значений характеристик (0-100 стресс, 0-200 энергия)
- Полное логирование действий в `activity_logs`

## 📊 Игровая логика

### Система уровней
- 1 уровень = 100 опыта
- Автоматический расчет прогресса
- События повышения уровня

### Статусы стресса
- `low`: 0-20
- `normal`: 21-50  
- `elevated`: 51-80
- `high`: 81-100

### Достижения
- Автоматическое отслеживание прогресса
- Различные категории достижений
- История разблокировки

## 🧪 Тестирование

Примеры curl запросов:

```bash
# Получение профиля
curl -X GET http://localhost:8000/api/player/profile \
  -H "Authorization: Bearer YOUR_TOKEN"

# Обновление стресса
curl -X POST http://localhost:8000/api/player/update-stress \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount": -10}'

# Получение ежедневной награды
curl -X POST http://localhost:8000/api/player/daily-reward \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Все эндпоинты реализованы полностью функционально согласно GDD без использования TODO.
