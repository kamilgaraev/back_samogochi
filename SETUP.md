# Инструкции по настройке проекта Антистресс Тамагочи (Backend)

## Требования

- PHP 8.2+
- PostgreSQL 15+
- Redis 7+
- Composer

## Установка

### 1. Клонирование и установка зависимостей

```bash
composer install
```

### 2. Настройка окружения

Создайте файл `.env` на основе `.env.example`:

```bash
cp .env.example .env
```

### 3. Настройте переменные окружения в `.env`:

```env
APP_NAME=AntiStressTamagotchi
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# База данных PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tamagotchi_db
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Redis для кэширования и очередей
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# JWT настройки для авторизации
JWT_SECRET=
JWT_TTL=15
JWT_REFRESH_TTL=43200
```

### 4. Генерация ключей

```bash
php artisan key:generate
php artisan jwt:secret
```

### 5. Создание базы данных

Создайте базу данных PostgreSQL с именем `tamagotchi_db`

### 6. Миграции и данные

```bash
php artisan migrate
php artisan db:seed
```

### 7. Запуск сервера разработки

```bash
php artisan serve
```

## API Endpoints

### Авторизация
- `POST /api/auth/register` - Регистрация
- `POST /api/auth/login` - Авторизация
- `POST /api/auth/logout` - Выход (требует токен)
- `POST /api/auth/refresh` - Обновление токена
- `POST /api/auth/forgot-password` - Сброс пароля
- `POST /api/auth/reset-password` - Восстановление пароля

### Профиль игрока (требуют авторизации)

## Фоновые процессы

### Запуск фоновых задач вручную

```bash
# Восстановление энергии игроков
php artisan game:energy-regen

# Выдача ежедневных наград
php artisan game:daily-rewards
```

### Настройка автоматического выполнения задач

Для автоматического выполнения задач добавьте в crontab:

```bash
# Редактировать crontab
crontab -e

# Добавить строку для запуска Laravel Scheduler каждую минуту
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

Или запустите Laravel Scheduler в фоне:

```bash
# Запуск scheduler в background (для разработки)
php artisan schedule:work &
```

### Проверка очередей Redis

```bash
# Запуск worker для обработки очередей (если используются)
php artisan queue:work --queue=default

# Просмотр статуса очередей
php artisan queue:monitor
```

## Rate Limiting

API защищен от спама следующими лимитами:

- **Авторизация**: 10 запросов в минуту на IP/пользователя
- **Игровые эндпоинты**: 120 запросов в минуту на пользователя  
- **Администрирование**: 30 запросов в минуту на пользователя
- **Аналитика**: 60 запросов в минуту на пользователя

При превышении лимита возвращается HTTP 429 с заголовками:
- `X-RateLimit-Limit`: максимальное количество запросов
- `X-RateLimit-Remaining`: оставшиеся запросы 
- `Retry-After`: через сколько секунд можно повторить

## Docker Deployment

### Требования
- Docker Engine 24.0+  
- Docker Compose 2.20+
- PostgreSQL 17 (внешний сервер)
- Redis 8.1 (внешний сервер)

### Быстрый старт

```bash
# 1. Настроить environment
cp docker/env.example .env.docker
# Отредактировать .env.docker с вашими настройками БД

# 2. Собрать и запустить
docker compose up -d

# 3. Инициализация (только первый раз)
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan key:generate  
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan optimize

# 4. Проверить работу
curl http://localhost/health
```

### Development режим

```bash
docker compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

Подробная документация в `docker/README.md`

### Профиль игрока (требуют авторизации)
- `GET /api/player/profile` - Получение профиля
- `PUT /api/player/profile` - Обновление профиля  
- `GET /api/player/stats` - Статистика игрока
- `GET /api/player/progress` - Прогресс и достижения
- `POST /api/player/daily-reward` - Получение ежедневной награды
- `POST /api/player/add-experience` - Добавление опыта
- `POST /api/player/update-energy` - Изменение энергии
- `POST /api/player/update-stress` - Изменение уровня стресса

## Структура проекта

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   └── PlayerController.php
│   └── Middleware/
│       └── AdminMiddleware.php
├── Models/
│   ├── User.php
│   ├── PlayerProfile.php
│   ├── ActivityLog.php
│   ├── GameConfig.php
│   ├── PlayerSituation.php
│   ├── PlayerMicroAction.php
│   ├── Situation.php
│   ├── SituationOption.php
│   └── MicroAction.php
├── Services/
│   ├── AuthService.php
│   └── PlayerService.php
├── Repositories/
│   └── PlayerRepository.php
└── Providers/
    └── RepositoryServiceProvider.php
```

## Тестирование API

### Регистрация пользователя
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'
```

### Авторизация
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

### Получение профиля
```bash
curl -X GET http://localhost:8000/api/player/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Получение статистики
```bash
curl -X GET http://localhost:8000/api/player/stats \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Изменение уровня стресса
```bash
curl -X POST http://localhost:8000/api/player/update-stress \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"amount": -10}'
```

### Получение ежедневной награды  
```bash
curl -X POST http://localhost:8000/api/player/daily-reward \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Следующие шаги

1. Добавить модели для игровых ситуаций и микродействий
2. Создать контроллеры для игровой логики
3. Реализовать систему метрик и аналитики
4. Настроить Docker для развертывания

## Безопасность

- JWT токены имеют короткое время жизни (15 минут)
- Refresh токены действуют 30 дней
- Все API endpoints логируются в таблицу activity_logs
- Пароли хэшируются с помощью bcrypt
