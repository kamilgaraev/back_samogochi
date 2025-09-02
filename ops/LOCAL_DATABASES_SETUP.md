# Установка локальных баз данных PostgreSQL и Redis

Этот документ описывает процесс установки PostgreSQL и Redis локально на сервер для избежания проблем с сетевой связностью к облачным кластерам.

## Быстрая установка

### 1. Запуск автоматического скрипта установки

```bash
cd /opt/tamagotchi-api

# Сделать скрипт исполняемым
chmod +x ops/scripts/install-local-databases.sh

# Запустить установку (требует root)
sudo ./ops/scripts/install-local-databases.sh
```

### 2. Обновление конфигурации приложения

```bash
# Скопировать локальный конфиг
cp ops/config/env.local .env

# Генерация JWT секрета (если отсутствует)
php artisan jwt:secret --show
# Добавить полученный секрет в .env файл
```

### 3. Запуск миграций

```bash
# Тестировать подключение
php artisan tinker
# В tinker: DB::connection()->getPdo();

# Запустить миграции
php artisan migrate --force

# Заполнить базовые данные
php artisan db:seed
```

### 4. Перезапуск приложения

```bash
# Перезапустить все сервисы
systemctl restart tamagotchi-queue php8.3-fpm nginx

# Проверить статус
systemctl status postgresql redis-server tamagotchi-queue
```

## Ручная установка (если автоскрипт не сработал)

### Установка PostgreSQL

```bash
# Обновить пакеты
apt update

# Установить PostgreSQL 15
apt install -y postgresql-15 postgresql-client-15 postgresql-contrib-15

# Запустить и включить автозапуск
systemctl start postgresql
systemctl enable postgresql
```

### Создание пользователя и базы данных

```bash
# Переключиться на пользователя postgres
sudo -u postgres psql

# В PostgreSQL консоли:
CREATE USER tamagotchi_user WITH PASSWORD 'secure_local_password_2024';
CREATE DATABASE tamagotchi_db OWNER tamagotchi_user;
GRANT ALL PRIVILEGES ON DATABASE tamagotchi_db TO tamagotchi_user;
GRANT ALL PRIVILEGES ON SCHEMA public TO tamagotchi_user;

# Проверить
\l
\du
\q
```

### Настройка доступа PostgreSQL

```bash
# Найти версию PostgreSQL
PG_VERSION=$(sudo -u postgres psql -t -c "SELECT version();" | grep -oP "\d+\.\d+" | head -1)

# Редактировать pg_hba.conf
nano /etc/postgresql/${PG_VERSION}/main/pg_hba.conf

# Добавить в конец файла:
local   tamagotchi_db   tamagotchi_user                 md5
host    tamagotchi_db   tamagotchi_user   127.0.0.1/32  md5
host    tamagotchi_db   tamagotchi_user   ::1/128       md5

# Перезапустить PostgreSQL
systemctl restart postgresql
```

### Установка Redis

```bash
# Установить Redis
apt install -y redis-server

# Запустить и включить автозапуск
systemctl start redis-server
systemctl enable redis-server

# Проверить работу
redis-cli ping
```

## Тестирование подключений

### PostgreSQL

```bash
# Тест подключения
PGPASSWORD='secure_local_password_2024' psql -h localhost -U tamagotchi_user -d tamagotchi_db -c "SELECT 'OK' as status;"

# Тест через PHP
php -r "
try {
    \$pdo = new PDO('pgsql:host=localhost;port=5432;dbname=tamagotchi_db', 'tamagotchi_user', 'secure_local_password_2024');
    echo 'PostgreSQL OK' . PHP_EOL;
} catch (Exception \$e) {
    echo 'PostgreSQL Error: ' . \$e->getMessage() . PHP_EOL;
}
"
```

### Redis

```bash
# Тест Redis
redis-cli ping

# Тест через PHP
php -r "
try {
    \$redis = new Redis();
    \$redis->connect('127.0.0.1', 6379);
    echo 'Redis OK' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Redis Error: ' . \$e->getMessage() . PHP_EOL;
}
"
```

## Параметры подключения

### PostgreSQL
- **Host**: localhost
- **Port**: 5432
- **Database**: tamagotchi_db
- **Username**: tamagotchi_user
- **Password**: secure_local_password_2024

### Redis
- **Host**: localhost
- **Port**: 6379
- **Password**: (не требуется)

## Решение проблем

### PostgreSQL не запускается

```bash
# Проверить статус
systemctl status postgresql

# Проверить логи
journalctl -u postgresql -n 50

# Проверить конфигурацию
sudo -u postgres psql -c "SHOW config_file;"
```

### Ошибки подключения PostgreSQL

```bash
# Проверить pg_hba.conf
cat /etc/postgresql/*/main/pg_hba.conf | grep tamagotchi

# Проверить listen_addresses в postgresql.conf
grep listen_addresses /etc/postgresql/*/main/postgresql.conf

# Перезагрузить конфигурацию
systemctl reload postgresql
```

### Redis не запускается

```bash
# Проверить статус
systemctl status redis-server

# Проверить логи
journalctl -u redis-server -n 50

# Проверить конфигурацию
cat /etc/redis/redis.conf | grep -v '^#' | grep -v '^$'
```

## Мониторинг производительности

### PostgreSQL

```bash
# Активные подключения
sudo -u postgres psql -c "SELECT count(*) FROM pg_stat_activity;"

# Размер базы данных
sudo -u postgres psql -c "SELECT pg_size_pretty(pg_database_size('tamagotchi_db'));"

# Активные запросы
sudo -u postgres psql -c "SELECT pid, usename, application_name, client_addr, state, query FROM pg_stat_activity WHERE state = 'active';"
```

### Redis

```bash
# Информация о Redis
redis-cli info

# Использование памяти
redis-cli info memory

# Подключенные клиенты
redis-cli info clients
```

## Бэкап данных

### PostgreSQL

```bash
# Создать бэкап
sudo -u postgres pg_dump tamagotchi_db > /opt/backups/tamagotchi_db_$(date +%Y%m%d_%H%M%S).sql

# Восстановить из бэкапа
sudo -u postgres psql tamagotchi_db < /opt/backups/tamagotchi_db_backup.sql
```

### Redis

```bash
# Создать снимок
redis-cli BGSAVE

# Скопировать файл данных
cp /var/lib/redis/dump.rdb /opt/backups/redis_dump_$(date +%Y%m%d_%H%M%S).rdb
```
