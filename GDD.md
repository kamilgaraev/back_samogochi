# Game Design Document - Антистресс Тамагочи (Backend API)

## 1. Обзор проекта

### 1.1 Концепция
REST API для игры-тамагочи, направленной на обучение управлению стрессом и развитие эмоционального интеллекта. Серверная часть управляет игровой логикой, прогрессом игроков, стрессовыми ситуациями и системой характеристик персонажа.

### 1.2 Основные цели
- Предоставление REST API для управления игровой логикой
- Обработка прогресса игроков и характеристик персонажа
- Управление базой данных стрессовых ситуаций и микродействий
- Сбор и анализ игровых метрик
- Обеспечение безопасности и масштабируемости системы

### 1.3 Интеграция
- REST API для веб и мобильных клиентов
- Административная панель для управления контентом
- Система аналитики для анализа поведения игроков
- Интеграция с внешними сервисами уведомлений

## 2. Техническая архитектура

### 2.1 Backend Stack
- **Framework**: Laravel 10+
- **Язык**: PHP 8.2+
- **База данных**: PostgreSQL 15+
- **Кэширование**: Redis 7+
- **Очереди**: Redis + Horizon
- **Авторизация**: tymon/jwt-auth
- **Файловое хранилище**: Laravel Storage (local/S3)

### 2.2 Мониторинг и метрики
- **Метрики**: Prometheus
- **Визуализация**: Grafana
- **APM**: Laravel Telescope + custom metrics

### 2.3 Deployment
- **Контейнеризация**: Docker + Docker Compose
- **Веб-сервер**: Nginx
- **Process Manager**: Supervisor
- **CI/CD**: GitHub Actions

### 2.4 Архитектура приложения

#### Многослойная архитектура
```
┌─────────────────┐
│   Controllers   │ ← HTTP запросы, валидация, ответы
├─────────────────┤
│    Services     │ ← Бизнес-логика, игровые алгоритмы
├─────────────────┤
│  Repositories   │ ← Работа с данными, запросы к БД
├─────────────────┤
│     Models      │ ← Eloquent модели, отношения
└─────────────────┘
```

#### Разделение ответственности
- **Controllers**: Обработка HTTP запросов, валидация входных данных, формирование ответов
- **Services**: Бизнес-логика игры, расчет характеристик, игровые алгоритмы
- **Repositories**: Абстракция работы с базой данных, сложные запросы
- **Models**: Eloquent модели, отношения между таблицами, аксессоры

#### Примеры классов
```php
// Controllers
AuthController, PlayerController, SituationController

// Services  
GameLogicService, AuthService, PlayerService, MetricsService

// Repositories
PlayerRepository, SituationRepository, AnalyticsRepository
```

### 2.5 Система уведомлений
- **Email**: Laravel Mail + Mailgun/SendGrid
- **API уведомлений**: REST endpoints для клиентов
- **Очереди уведомлений**: Redis Queue для отложенной отправки

## 3. Структура базы данных

### 3.1 Основные таблицы

#### Users (Пользователи)
```sql
- id: bigint PK
- email: varchar unique
- email_verified_at: timestamp nullable
- password: varchar
- name: varchar
- avatar: varchar nullable
- is_admin: boolean default false
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp nullable
```

#### Player_Profiles (Профили игроков)
```sql
- id: bigint PK
- user_id: bigint FK (users.id)
- level: integer default 1
- total_experience: integer default 0
- energy: integer default 100
- stress: integer default 50 (0-100)
- anxiety: integer default 30 (0-100)
- last_login: timestamp
- last_daily_reward: timestamp nullable
- consecutive_days: integer default 0
- created_at: timestamp
- updated_at: timestamp
```

#### Game_Configs (Конфигурации игры)
```sql
- id: bigint PK
- key: varchar unique
- value: json
- description: text nullable
- is_active: boolean default true
- created_by: bigint FK (users.id) nullable
- created_at: timestamp
- updated_at: timestamp
```

#### Situations (Стрессовые ситуации)
```sql
- id: bigint PK
- title: varchar
- description: text
- category: enum (work, study, personal, health)
- difficulty_level: integer (1-5)
- min_level_required: integer default 1
- stress_impact: integer
- experience_reward: integer
- is_active: boolean default true
- created_at: timestamp
- updated_at: timestamp
```

#### Situation_Options (Варианты действий)
```sql
- id: bigint PK
- situation_id: bigint FK (situations.id)
- text: text
- stress_change: integer
- experience_reward: integer
- energy_cost: integer default 0
- min_level_required: integer default 1
- order: integer default 0
- created_at: timestamp
- updated_at: timestamp
```

#### Player_Situations (Прогресс по ситуациям)
```sql
- id: bigint PK
- player_id: bigint FK (player_profiles.id)
- situation_id: bigint FK (situations.id)
- selected_option_id: bigint FK (situation_options.id) nullable
- completed_at: timestamp nullable
- created_at: timestamp
- updated_at: timestamp
```

#### Micro_Actions (Микродействия)
```sql
- id: bigint PK
- name: varchar
- description: text
- energy_reward: integer
- experience_reward: integer
- cooldown_minutes: integer default 0
- unlock_level: integer default 1
- category: enum (relaxation, exercise, creativity, social)
- is_active: boolean default true
- created_at: timestamp
- updated_at: timestamp
```

#### Player_Micro_Actions (Выполненные микродействия)
```sql
- id: bigint PK
- player_id: bigint FK (player_profiles.id)
- micro_action_id: bigint FK (micro_actions.id)
- completed_at: timestamp
- energy_gained: integer
- experience_gained: integer
- created_at: timestamp
```

#### Activity_Logs (Логи активности)
```sql
- id: bigint PK
- user_id: bigint FK (users.id) nullable
- event_type: varchar
- event_data: json
- ip_address: inet nullable
- user_agent: text nullable
- created_at: timestamp
```

### 3.2 Индексы
```sql
-- Performance indexes
CREATE INDEX idx_player_profiles_user_id ON player_profiles(user_id);
CREATE INDEX idx_player_situations_player_id ON player_situations(player_id);
CREATE INDEX idx_activity_logs_user_id_created_at ON activity_logs(user_id, created_at);
CREATE INDEX idx_situations_difficulty_level ON situations(difficulty_level);
CREATE INDEX idx_micro_actions_unlock_level ON micro_actions(unlock_level);
```

## 4. Структура проекта

### 4.1 Организация файлов
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── PlayerController.php
│   │   │   ├── SituationController.php
│   │   │   ├── MicroActionController.php
│   │   │   └── AdminController.php
│   │   └── Controller.php
│   ├── Requests/
│   │   ├── Auth/
│   │   ├── Player/
│   │   └── Situation/
│   └── Resources/
├── Services/
│   ├── AuthService.php
│   ├── GameLogicService.php
│   ├── PlayerService.php
│   ├── SituationService.php
│   ├── MetricsService.php
│   └── CooldownService.php
├── Repositories/
│   ├── PlayerRepository.php
│   ├── SituationRepository.php
│   ├── MicroActionRepository.php
│   └── AnalyticsRepository.php
├── Models/
│   ├── User.php
│   ├── PlayerProfile.php
│   ├── Situation.php
│   ├── SituationOption.php
│   ├── MicroAction.php
│   └── ActivityLog.php
├── Jobs/
│   ├── EnergyRegenJob.php
│   └── DailyRewardJob.php
└── Providers/
    ├── AppServiceProvider.php
    ├── RepositoryServiceProvider.php
    └── MetricsServiceProvider.php
```

### 4.2 Dependency Injection
```php
// app/Providers/RepositoryServiceProvider.php
class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PlayerRepository::class, PlayerRepository::class);
        $this->app->bind(SituationRepository::class, SituationRepository::class);
        $this->app->bind(PlayerService::class, PlayerService::class);
        $this->app->bind(GameLogicService::class, GameLogicService::class);
    }
}
```

## 5. API Endpoints

### 5.1 Аутентификация
```
POST /api/auth/register - Регистрация
POST /api/auth/login - Вход
POST /api/auth/logout - Выход
POST /api/auth/refresh - Обновление токена
POST /api/auth/verify-email - Подтверждение email
POST /api/auth/forgot-password - Запрос сброса пароля
POST /api/auth/reset-password - Сброс пароля
```

### 5.2 Профиль игрока
```
GET /api/player/profile - Получение профиля
PUT /api/player/profile - Обновление профиля
GET /api/player/stats - Статистика игрока
GET /api/player/progress - Прогресс по уровням
```

### 5.3 Игровые ситуации
```
GET /api/situations - Список доступных ситуаций
GET /api/situations/{id} - Детали ситуации
POST /api/situations/{id}/complete - Завершение ситуации
GET /api/situations/random - Случайная ситуация
```

### 5.4 Микродействия
```
GET /api/micro-actions - Список доступных микродействий
POST /api/micro-actions/{id}/perform - Выполнение микродействия
GET /api/micro-actions/history - История выполненных действий
```

### 5.5 Конфигурации (Admin)
```
GET /api/admin/configs - Список конфигураций
PUT /api/admin/configs/{key} - Обновление конфигурации
GET /api/admin/situations - Управление ситуациями
POST /api/admin/situations - Создание ситуации
PUT /api/admin/situations/{id} - Обновление ситуации
DELETE /api/admin/situations/{id} - Удаление ситуации
```

### 5.6 Аналитика
```
GET /api/analytics/dashboard - Дашборд метрик
GET /api/analytics/player-behavior - Поведение игроков
GET /api/analytics/situation-stats - Статистика ситуаций
```

## 6. Серверная игровая логика

### 6.1 Система характеристик

#### Стресс (0-100)
- Увеличивается при появлении стрессовых ситуаций
- Уменьшается при выборе правильных действий
- При >80 - ограничения на некоторые действия
- При <20 - бонусы к получению опыта

#### Опыт
- Начисляется за ежедневный вход (+10)
- За прохождение ситуаций (10-50)
- За выполнение микродействий (5-20)
- Конвертируется в уровни (100 опыта = 1 уровень)

#### Энергия (0-200)
- Восстанавливается микродействиями (+5-15)
- Тратится на эффективные решения ситуаций (-10-30)
- Естественное восстановление (+1 каждый час)

#### Тревога (0-100)
- Увеличивается автоматически при стрессовых ситуациях
- Влияет на доступность определенных действий
- Уменьшается техниками релаксации
- Передается клиентам для визуальных эффектов

### 6.2 Система уровней
```
Уровень 1-5: Базовые микродействия
Уровень 6-10: Дыхательные техники
Уровень 11-15: Медитативные практики
Уровень 16-20: Физические упражнения
Уровень 21+: Продвинутые техники
```

### 6.3 Категории ситуаций
- **Рабочие**: дедлайны, конфликты с коллегами, презентации
- **Учебные**: экзамены, публичные выступления, групповые проекты
- **Личные**: отношения, семейные проблемы, самооценка
- **Здоровье**: бессонница, переедание, тревожность

### 6.4 Обработка игровых событий

#### Алгоритм расчета характеристик
```php
class GameLogicService
{
    public function completeSituation($playerId, $situationId, $optionId)
    {
        // Получить текущие характеристики игрока
        $player = PlayerProfile::find($playerId);
        $option = SituationOption::find($optionId);
        
        // Применить изменения
        $player->stress += $option->stress_change;
        $player->total_experience += $option->experience_reward;
        $player->energy -= $option->energy_cost;
        
        // Проверить границы значений
        $player->stress = max(0, min(100, $player->stress));
        $player->energy = max(0, min(200, $player->energy));
        
        // Обновить уровень при необходимости
        $this->updatePlayerLevel($player);
        
        return $player->save();
    }
}
```

#### Система cooldown и ограничений
```php
class CooldownService
{
    public function canPerformAction($playerId, $actionType, $actionId)
    {
        $lastAction = PlayerMicroAction::where('player_id', $playerId)
            ->where('micro_action_id', $actionId)
            ->latest()
            ->first();
            
        if (!$lastAction) return true;
        
        $cooldown = MicroAction::find($actionId)->cooldown_minutes;
        return $lastAction->completed_at->addMinutes($cooldown) <= now();
    }
}
```

## 7. Система метрик и KPI

### 7.1 Ключевые метрики
```
- Конверсия посетитель -> регистрация
- Retention D1, D7, D30
- Переход игра -> образовательные материалы
- Средняя сессия
- Количество завершенных ситуаций
- Использование микродействий
```

### 7.2 Prometheus метрики
```php
// Custom metrics
- game_sessions_total: Счетчик сессий
- situations_completed_total: Завершенные ситуации
- stress_level_histogram: Распределение уровня стресса
- user_retention_gauge: Показатели удержания
- micro_actions_usage: Использование микродействий
```

### 7.3 Grafana дашборды
```
1. Player Activity Dashboard
   - Активные игроки
   - Средняя длительность сессии
   - Популярные ситуации

2. Game Balance Dashboard
   - Распределение характеристик
   - Прогресс по уровням
   - Эффективность ситуаций

3. API Performance Dashboard
   - Отклик API endpoints
   - Нагрузка на базу данных
   - Ошибки и статусы ответов
```

## 8. Система безопасности

### 8.1 Аутентификация и авторизация
```php
- JWT токены с коротким временем жизни (15 минут)
- Refresh токены (30 дней)
- Rate limiting на API endpoints
- CSRF защита
- XSS защита через Content Security Policy
```

### 8.2 Валидация данных
```php
- Строгая валидация всех входных данных
- Sanitization пользовательского контента
- Ограничения на размер загружаемых файлов
- Проверка MIME типов
```

### 8.3 Логирование безопасности
```php
- Неудачные попытки входа
- Подозрительная активность API
- Изменения критических данных
- Административные действия
```

## 9. Конфигурация системы

### 9.1 Игровые константы
```json
{
  "game_balance": {
    "daily_login_experience": 10,
    "max_energy": 200,
    "energy_regen_per_hour": 1,
    "stress_threshold_high": 80,
    "stress_threshold_low": 20,
    "situation_cooldown_seconds": 0
  },
  "level_requirements": [
    {"level": 1, "experience": 0},
    {"level": 2, "experience": 100},
    {"level": 3, "experience": 250}
  ]
}
```

### 9.2 Фоновые процессы
```php
// Естественное восстановление энергии
class EnergyRegenJob implements ShouldQueue
{
    public function handle()
    {
        PlayerProfile::chunk(1000, function ($players) {
            foreach ($players as $player) {
                $hoursSinceUpdate = $player->updated_at->diffInHours(now());
                $energyToAdd = min(1 * $hoursSinceUpdate, 200 - $player->energy);
                
                if ($energyToAdd > 0) {
                    $player->increment('energy', $energyToAdd);
                }
            }
        });
    }
}

// Ежедневные награды
class DailyRewardJob implements ShouldQueue
{
    public function handle()
    {
        PlayerProfile::where('last_login', '>=', now()->startOfDay())
            ->where('last_daily_reward', '<', now()->startOfDay())
            ->chunk(1000, function ($players) {
                foreach ($players as $player) {
                    $player->increment('total_experience', 10);
                    $player->increment('consecutive_days', 1);
                    $player->last_daily_reward = now();
                    $player->save();
                }
            });
    }
}
```

### 9.3 Система уведомлений
```json
{
  "notifications": {
    "daily_reminder": {
      "enabled": true,
      "time": "19:00",
      "message": "Время позаботиться о своем эмоциональном состоянии!"
    },
    "high_stress_alert": {
      "enabled": true,
      "threshold": 85,
      "message": "Уровень стресса высокий. Попробуйте технику дыхания."
    }
  }
}
```

## 10. План развертывания

### 10.1 Docker Compose
```yaml
services:
  app:
    build: .
    environment:
      - APP_ENV=production
      - DB_CONNECTION=pgsql
  
  postgresql:
    image: postgres:15
    
  redis:
    image: redis:7-alpine
    
  prometheus:
    image: prom/prometheus
    
  grafana:
    image: grafana/grafana
```

### 10.2 CI/CD Pipeline
```yaml
# GitHub Actions
- Code quality checks (PHPStan, PHPCS)
- Unit and Feature tests
- Security scanning
- Docker build and push
- Automated deployment
```

## 11. Этапы разработки

### 11.1 MVP (4-6 недель)
1. **Создание архитектуры проекта**
   - Базовые контроллеры, сервисы, репозитории
   - Миграции основных таблиц
   - Конфигурация JWT и Redis
2. **Аутентификация и профили игроков**
   - AuthController + AuthService
   - PlayerController + PlayerService + PlayerRepository
   - Базовые CRUD операции
3. **Игровая логика**
   - GameLogicService для расчета характеристик
   - 5-10 базовых ситуаций через SituationService
   - Простые микродействия
4. **API endpoints**
   - Все основные маршруты из раздела 4
   - Валидация запросов
5. **Базовый мониторинг**
   - Prometheus метрики
   - Логирование через ActivityLogs

### 11.2 Версия 1.0 (8-12 недель)
1. Расширенный набор ситуаций
2. Система уровней и прогрессии
3. Push-уведомления
4. Административная панель
5. Полный мониторинг Grafana

### 11.3 Версия 2.0 (16-20 недель)
1. Расширенная система достижений
2. Машинное обучение для персонализации
3. Интеграция с внешними API
4. Расширенная аналитика
5. Микросервисная архитектура
