# API Endpoints - Игровые ситуации

Полностью реализована система стрессовых ситуаций согласно GDD с многослойной архитектурой.

## 📋 Реализованные эндпоинты:

### Получение ситуаций

#### `GET /api/situations`
Получение списка доступных ситуаций с пагинацией и фильтрацией.

**Авторизация:** Требуется JWT токен

**Параметры:**
- `per_page` (опционально): integer, 1-50, количество на страницу
- `category` (опционально): string, work|study|personal|health

**Ответ:**
```json
{
  "success": true,
  "message": "Список ситуаций успешно получен",
  "data": {
    "situations": [
      {
        "id": 1,
        "title": "Горящий дедлайн на работе", 
        "description": "Ваш руководитель просит срочно закончить проект...",
        "category": "work",
        "difficulty_level": 2,
        "stress_impact": 15,
        "experience_reward": 20,
        "options": [
          {
            "id": 1,
            "text": "Спокойно составить план действий...",
            "stress_change": -5,
            "experience_reward": 25,
            "energy_cost": 10
          }
        ]
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 3,
      "per_page": 15,
      "total": 8
    },
    "cooldown_info": {
      "on_cooldown": false,
      "cooldown_ends_at": null
    },
    "player_level": 2
  }
}
```

#### `GET /api/situations/{id}`
Получение детальной информации о конкретной ситуации.

**Ответ:**
```json
{
  "success": true,
  "data": {
    "situation": {
      "id": 1,
      "title": "Горящий дедлайн на работе",
      "description": "...",
      "category": "work", 
      "difficulty_level": 2,
      "stress_impact": 15,
      "experience_reward": 20
    },
    "options": [
      {
        "id": 1,
        "text": "Спокойно составить план действий...",
        "stress_change": -5,
        "experience_reward": 25,
        "energy_cost": 10,
        "min_level_required": 1
      }
    ],
    "player_info": {
      "current_stress": 45,
      "current_energy": 120,
      "level": 2
    },
    "can_start": true
  }
}
```

#### `GET /api/situations/random`
Получение случайной доступной ситуации.

**Параметры:**
- `category` (опционально): string, work|study|personal|health

**Ответ аналогичен GET /api/situations/{id}**

### Игровые действия

#### `POST /api/situations/{id}/complete`
Завершение ситуации с выбором варианта действия.

**Параметры:**
- `option_id`: integer, ID выбранного варианта действия

**Пример запроса:**
```json
{
  "option_id": 1
}
```

**Ответ:**
```json
{
  "success": true,
  "message": "Ситуация успешно завершена!",
  "data": {
    "situation": "Горящий дедлайн на работе",
    "selected_option": "Спокойно составить план действий...",
    "rewards": {
      "experience_gained": 25,
      "stress_change": -5,
      "energy_cost": 10
    },
    "player_changes": {
      "old_stress": 50,
      "new_stress": 45,
      "old_energy": 130,
      "new_energy": 120,
      "old_level": 1,
      "new_level": 2,
      "level_up": true
    },
    "cooldown_until": "2024-01-15T16:30:00Z"
  }
}
```

### Аналитика и история

#### `GET /api/situations/history`
История пройденных ситуаций игрока.

**Параметры:**
- `limit` (опционально): integer, 1-100, количество записей

**Ответ:**
```json
{
  "success": true,
  "data": {
    "history": [
      {
        "situation_title": "Горящий дедлайн на работе",
        "selected_option": "Спокойно составить план действий...",
        "completed_at": "2024-01-15T14:30:00Z",
        "experience_gained": 25,
        "stress_change": -5,
        "category": "work"
      }
    ],
    "total_completed": 5
  }
}
```

#### `GET /api/situations/recommendations`
Персонализированные рекомендации ситуаций.

**Ответ:**
```json
{
  "success": true,
  "data": {
    "recommendations": [
      {
        "id": 2,
        "title": "Важный экзамен через неделю",
        "category": "study",
        "difficulty_level": 1,
        "experience_reward": 15
      }
    ],
    "based_on": {
      "stress_level": 45,
      "player_level": 2,
      "completed_situations": 5
    }
  }
}
```

## 🎮 Игровая механика

### Система категорий
- **work** - рабочие ситуации (дедлайны, презентации, конфликты)
- **study** - учебные ситуации (экзамены, проекты)  
- **personal** - личные отношения (конфликты, семья)
- **health** - здоровье (стресс, сон, концентрация)

### Система сложности
- **Уровень 1-2**: Базовые ситуации для новичков
- **Уровень 3-4**: Средняя сложность  
- **Уровень 5+**: Сложные ситуации

### Система перезарядки (Cooldown)
- **2 часа** между завершением ситуаций
- Предотвращает спам и добавляет реализм
- Настраивается через `GAME_SITUATION_COOLDOWN_HOURS`

### Варианты действий
- **Конструктивные** - уменьшают стресс, дают больше опыта
- **Деструктивные** - увеличивают стресс, мало опыта  
- **Социальные** - низкая энергозатрата, средний эффект

### Алгоритм рекомендаций
- При **высоком стрессе** (>70) - рекомендуются ситуации, снижающие стресс
- При **низком стрессе** (<30) - можно брать сложные ситуации
- Исключаются уже пройденные ситуации
- Учитывается уровень игрока

## 🏗️ Архитектура

### Многослойная структура:
- **SituationController** - HTTP обработка, валидация запросов
- **SituationService** - Бизнес-логика, игровые правила, транзакции
- **SituationRepository** - Запросы к БД, аналитика, статистика

### База данных:
- **situations** - описания ситуаций
- **situation_options** - варианты действий
- **player_situations** - прогресс игроков

## 🛡️ Безопасность и валидация

- JWT авторизация на всех эндпоинтах
- Проверка уровня игрока для доступа к ситуациям
- Проверка энергии для выполнения действий
- Система cooldown против злоупотреблений
- Атомарные транзакции при завершении ситуаций
- Полное логирование в activity_logs

## 🧪 Тестирование

### Получение списка ситуаций
```bash
curl -X GET http://localhost:8000/api/situations \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Получение случайной ситуации
```bash
curl -X GET http://localhost:8000/api/situations/random?category=work \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Завершение ситуации
```bash
curl -X POST http://localhost:8000/api/situations/1/complete \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"option_id": 1}'
```

### История ситуаций
```bash
curl -X GET http://localhost:8000/api/situations/history \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 📊 Базовые ситуации (8 штук)

Созданы разнообразные ситуации во всех категориях:

1. **Горящий дедлайн на работе** (work, сложность 2)
2. **Важный экзамен через неделю** (study, сложность 1)  
3. **Конфликт с другом** (personal, сложность 2)
4. **Бессонная ночь** (health, сложность 1)
5. **Публичное выступление** (work, сложность 3, требует уровень 2)
6. **Семейные разногласия** (personal, сложность 3, требует уровень 2)
7. **Проблемы с концентрацией** (health, сложность 2)
8. **Сложный экзамен по математике** (study, сложность 3, требует уровень 2)

Каждая ситуация имеет 3 варианта действий с разными последствиями для игрового баланса.

**Система полностью функциональна и готова к тестированию!** 🚀
