#!/bin/bash
# Быстрая сборка документации для Laravel

echo "📚 Собираем документацию для Laravel..."

cd "$(dirname "$0")"

# Проверяем Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Нужен Node.js. Устанавливайте: https://nodejs.org/"
    exit 1
fi

# Устанавливаем зависимости если нужно
if [ ! -d "node_modules" ]; then
    echo "📦 Устанавливаем зависимости..."
    npm install
fi

# Собираем документацию
echo "🏗️ Собираем..."
npm run build

echo "✅ Готово! Документация доступна по адресу:"
echo "   http://your-domain.com/docs"
