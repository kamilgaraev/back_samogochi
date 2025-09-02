#!/bin/bash

set -e

log_info() {
    echo "[INFO] $(date '+%Y-%m-%d %H:%M:%S') $1"
}

log_success() {
    echo "[SUCCESS] $(date '+%Y-%m-%d %H:%M:%S') $1"
}

log_error() {
    echo "[ERROR] $(date '+%Y-%m-%d %H:%M:%S') $1" >&2
}

log_info "Начинается установка локальных PostgreSQL и Redis..."

# Обновляем пакеты
log_info "Обновление списка пакетов..."
apt update

# Устанавливаем PostgreSQL
log_info "Установка PostgreSQL 16..."
apt install -y postgresql-16 postgresql-client-16 postgresql-contrib-16

# Устанавливаем Redis
log_info "Установка Redis..."
apt install -y redis-server

# Настройка PostgreSQL
log_info "Настройка PostgreSQL..."

# Запускаем и включаем PostgreSQL
systemctl start postgresql
systemctl enable postgresql

# Проверяем статус
systemctl is-active postgresql || {
    log_error "PostgreSQL не запустился"
    exit 1
}

# Создаем пользователя и базу данных для приложения
log_info "Создание пользователя и базы данных..."

sudo -u postgres psql << 'EOF'
-- Создаем пользователя
CREATE USER tamagotchi_user WITH PASSWORD 'secure_local_password_2024';

-- Создаем базу данных
CREATE DATABASE tamagotchi_db OWNER tamagotchi_user;

-- Даем права
GRANT ALL PRIVILEGES ON DATABASE tamagotchi_db TO tamagotchi_user;
GRANT ALL PRIVILEGES ON SCHEMA public TO tamagotchi_user;

-- Выводим информацию
\l
\du
EOF

# Настройка Redis
log_info "Настройка Redis..."

# Включаем Redis как службу
systemctl start redis-server
systemctl enable redis-server

# Проверяем Redis
systemctl is-active redis-server || {
    log_error "Redis не запустился"
    exit 1
}

# Настройка pg_hba.conf для локального подключения
log_info "Настройка доступа PostgreSQL..."

PG_VERSION="16"
PG_HBA_FILE="/etc/postgresql/${PG_VERSION}/main/pg_hba.conf"

# Делаем бэкап конфига
cp "$PG_HBA_FILE" "${PG_HBA_FILE}.backup"

# Добавляем правило для локального подключения с паролем
cat >> "$PG_HBA_FILE" << 'EOF'

# Tamagotchi application access
local   tamagotchi_db   tamagotchi_user                 md5
host    tamagotchi_db   tamagotchi_user   127.0.0.1/32  md5
host    tamagotchi_db   tamagotchi_user   ::1/128       md5
EOF

# Перезапускаем PostgreSQL для применения изменений
systemctl restart postgresql

# Тестируем подключения
log_info "Тестирование подключений..."

# Тест PostgreSQL
log_info "Тестирование PostgreSQL..."
PGPASSWORD='secure_local_password_2024' psql -h localhost -U tamagotchi_user -d tamagotchi_db -c "SELECT 'PostgreSQL работает!' as status;" || {
    log_error "Не удалось подключиться к PostgreSQL"
    exit 1
}

# Тест Redis
log_info "Тестирование Redis..."
redis-cli ping | grep -q PONG || {
    log_error "Redis не отвечает"
    exit 1
}

# Выводим информацию о конфигурации
log_success "Установка завершена успешно!"
echo
echo "=== ИНФОРМАЦИЯ О БАЗАХ ДАННЫХ ==="
echo "PostgreSQL:"
echo "  Host: localhost"
echo "  Port: 5432"
echo "  Database: tamagotchi_db"
echo "  Username: tamagotchi_user"
echo "  Password: secure_local_password_2024"
echo
echo "Redis:"
echo "  Host: localhost"
echo "  Port: 6379"
echo "  Password: (не требуется)"
echo
echo "=== СТАТУС СЕРВИСОВ ==="
systemctl is-active postgresql && echo "PostgreSQL: АКТИВЕН" || echo "PostgreSQL: НЕ АКТИВЕН"
systemctl is-active redis-server && echo "Redis: АКТИВЕН" || echo "Redis: НЕ АКТИВЕН"
echo
echo "=== СЛЕДУЮЩИЕ ШАГИ ==="
echo "1. Обновите .env файл приложения"
echo "2. Запустите миграции: php artisan migrate"
echo "3. Перезапустите приложение"
