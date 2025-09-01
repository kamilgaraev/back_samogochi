# Tamagotchi API - Deployment Guide

## Быстрое развертывание

### 1. Подготовка сервера

Минимальные требования:
- Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- 2GB RAM
- 20GB свободного места
- Root доступ

### 2. Автоматическое развертывание

```bash
# Скачать скрипт развертывания
wget https://raw.githubusercontent.com/your-repo/tamagotchi-api/main/deploy.sh

# Сделать исполняемым
chmod +x deploy.sh

# Запустить развертывание
sudo ./deploy.sh
```

### 3. Ручная настройка после развертывания

После запуска скрипта обновите файл конфигурации:

```bash
sudo nano /opt/tamagotchi-api/.env.docker
```

Обязательно измените:
- `APP_URL` - ваш домен
- `DB_*` - настройки базы данных
- `REDIS_*` - настройки Redis
- `JWT_SECRET` - генерируйте новый секрет

### 4. SSL сертификаты

Замените самоподписанные сертификаты на валидные:

```bash
# Скопируйте ваши сертификаты
sudo cp your-cert.pem /opt/tamagotchi-api/docker/ssl/cert.pem
sudo cp your-private-key.pem /opt/tamagotchi-api/docker/ssl/privkey.pem

# Перезапустите nginx
sudo docker-compose -f /opt/tamagotchi-api/docker-compose.yml -f /opt/tamagotchi-api/docker-compose.production.yml restart nginx
```

### 5. Настройка домена

Настройте A-запись в DNS:
```
your-domain.com -> IP_ВАШЕГО_СЕРВЕРА
```

## Управление приложением

### Проверка статуса
```bash
cd /opt/tamagotchi-api
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml ps
```

### Просмотр логов
```bash
# Все сервисы
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml logs

# Конкретный сервис
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml logs app
```

### Перезапуск сервисов
```bash
cd /opt/tamagotchi-api
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml restart
```

### Обновление приложения
```bash
sudo /opt/tamagotchi-api/update.sh
```

## Мониторинг

### Health Check
```bash
curl http://your-domain.com/health
```

### Проверка контейнеров
```bash
sudo docker ps
```

### Системные ресурсы
```bash
# CPU и память
sudo docker stats

# Дисковое пространство
df -h
```

## Резервное копирование

### Создание бэкапа
```bash
# Автоматически создается при обновлении
# Ручное создание:
sudo mkdir -p /opt/backups/tamagotchi-api/manual_$(date +%Y%m%d_%H%M%S)
sudo cp -r /opt/tamagotchi-api /opt/backups/tamagotchi-api/manual_$(date +%Y%m%d_%H%M%S)
```

### Восстановление из бэкапа
```bash
sudo docker-compose -f /opt/tamagotchi-api/docker-compose.yml -f /opt/tamagotchi-api/docker-compose.production.yml down
sudo cp -r /opt/backups/tamagotchi-api/backup_TIMESTAMP/* /opt/tamagotchi-api/
sudo docker-compose -f /opt/tamagotchi-api/docker-compose.yml -f /opt/tamagotchi-api/docker-compose.production.yml up -d
```

## Устранение неполадок

### Контейнеры не запускаются
```bash
# Проверить логи
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml logs

# Проверить конфигурацию
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml config

# Пересобрать с нуля
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml down -v
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml up --build -d
```

### Проблемы с сетью
```bash
# Проверить порты
sudo netstat -tulpn | grep :80
sudo netstat -tulpn | grep :443

# Проверить firewall
sudo ufw status
```

### Проблемы с Redis
```bash
# Проверить подключение изнутри контейнера
sudo docker-compose exec app redis-cli -h 31.130.151.39 -p 6379 --user default --pass '?:W3K@aXg(0D!@' ping
```

## Безопасность

### Регулярные обновления
```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Обновление Docker
sudo apt update && sudo apt install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

### Firewall
Скрипт автоматически настраивает UFW:
- Порт 22 (SSH)
- Порт 80 (HTTP)
- Порт 443 (HTTPS)

### Мониторинг безопасности
```bash
# Проверить активные соединения
sudo netstat -tulpn

# Проверить логи входов
sudo tail -f /var/log/auth.log
```

## Масштабирование

### Увеличение queue workers
```bash
# Отредактировать docker-compose.production.yml
sudo nano /opt/tamagotchi-api/docker-compose.production.yml

# Изменить replicas для queue-worker
# Перезапустить
sudo docker-compose -f docker-compose.yml -f docker-compose.production.yml up -d --scale queue-worker=5
```

### Load Balancer
Для высоких нагрузок рекомендуется:
- Nginx в качестве reverse proxy
- Несколько экземпляров приложения
- Shared Redis и Database

## Поддержка

При возникновении проблем проверьте:
1. Логи контейнеров
2. Системные ресурсы
3. Сетевые подключения
4. Конфигурационные файлы

Полезные команды для диагностики включены в разделы выше.
