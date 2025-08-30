# Docker Development Guide

## Требования

- Docker Engine 24.0+
- Docker Compose 2.20+
- Внешние сервисы:
  - PostgreSQL 17
  - Redis 8.1

## Настройка

### 1. Подготовка environment файла

```bash
# Скопировать шаблон конфигурации
cp docker/env.example .env.docker

# Отредактировать настройки подключения к внешним базам данных
nano .env.docker
```

**Обязательно настройте:**
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`
- `APP_KEY` (32 символа)
- `JWT_SECRET`

### 2. Запуск Production Environment

```bash
# Собрать и запустить контейнеры
docker compose up -d

# Выполнить миграции (только первый раз)
docker compose exec app php artisan migrate --seed

# Сгенерировать ключи (только первый раз)
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret

# Очистить кэш
docker compose exec app php artisan optimize
```

### 3. Запуск Development Environment

```bash
# Использовать override файл для разработки
docker compose -f docker-compose.yml -f docker-compose.override.yml up -d

# Установить зависимости для разработки
docker compose exec app composer install

# Запустить тесты
docker compose exec app php artisan test
```

## Управление

### Основные команды

```bash
# Просмотр логов
docker compose logs -f app
docker compose logs -f nginx

# Перестроить контейнеры
docker compose build --no-cache

# Остановить контейнеры
docker compose down

# Очистить volumes (ОСТОРОЖНО!)
docker compose down -v
```

### Фоновые процессы

```bash
# Проверить статус фоновых задач
docker compose exec app supervisorctl status

# Перезапустить worker'ы
docker compose exec app supervisorctl restart laravel-queue-worker:*

# Ручной запуск команд
docker compose exec app php artisan game:energy-regen
docker compose exec app php artisan game:daily-rewards
```

### Мониторинг

```bash
# Проверить здоровье приложения
curl http://localhost/health

# Просмотр метрик через supervisor
docker compose exec app supervisorctl tail -f laravel-scheduler
docker compose exec app supervisorctl tail -f laravel-queue-worker
```

## Архитектура контейнеров

- **app**: Laravel приложение + PHP-FPM 8.3
- **nginx**: Веб-сервер с оптимизированной конфигурацией
- **scheduler**: Отдельный контейнер для Laravel Scheduler
- **queue-worker**: Отдельный контейнер для обработки очередей
- **mailhog**: Email-сервер для разработки (только в dev режиме)

## Производительность

### Оптимизации

- **OPcache**: Включен в production
- **Gzip**: Настроен в Nginx  
- **Static caching**: Год кэширования для статических файлов
- **Rate limiting**: Защита от DDoS на уровне Nginx
- **Connection pooling**: Keepalive для upstream соединений

### Масштабирование

```bash
# Увеличить количество queue worker'ов
docker compose up -d --scale queue-worker=4

# Горизонтальное масштабирование app контейнеров
docker compose up -d --scale app=3
```

## Безопасность

- Контейнеры работают от `www-data` пользователя
- Nginx proxy с rate limiting
- Security headers настроены
- Логирование всех запросов
- Отдельная сеть для контейнеров

## Troubleshooting

### Проблемы с подключением к БД

```bash
# Проверить подключение к PostgreSQL
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();

# Проверить подключение к Redis
docker compose exec app php artisan tinker
>>> Redis::ping();
```

### Проблемы с правами доступа

```bash
# Исправить права доступа
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```
