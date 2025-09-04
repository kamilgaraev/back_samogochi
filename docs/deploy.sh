#!/bin/bash
# Скрипт для развертывания документации на сервере

echo "🚀 Развертывание документации Tamagotchi API"

# Проверяем наличие Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js не найден. Устанавливаем..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
fi

# Проверяем версию Node.js
echo "✅ Node.js версия: $(node --version)"
echo "✅ npm версия: $(npm --version)"

# Переходим в папку с документацией
cd "$(dirname "$0")"
echo "📁 Текущая папка: $(pwd)"

# Устанавливаем зависимости
echo "📦 Устанавливаем зависимости..."
npm install

# Валидируем спецификацию
echo "🔍 Проверяем спецификацию..."
npm run lint

# Собираем статическую документацию
echo "🏗️  Собираем документацию..."
npm run build

# Создаем директорию для веб-сервера
sudo mkdir -p /var/www/api-docs
sudo chown -R www-data:www-data /var/www/api-docs

# Копируем собранную документацию
echo "📋 Копируем файлы..."
sudo cp -r ./build/* /var/www/api-docs/

# Устанавливаем правильные права
sudo chown -R www-data:www-data /var/www/api-docs
sudo chmod -R 755 /var/www/api-docs

echo "✅ Документация собрана и скопирована в /var/www/api-docs"
echo ""
echo "🌐 Теперь настройте nginx для раздачи статики:"
echo "   - Создайте конфиг для поддомена docs.your-domain.com"
echo "   - Либо добавьте location /docs в существующий конфиг"
echo ""
echo "📖 Пример конфига nginx находится в nginx-docs.conf"
