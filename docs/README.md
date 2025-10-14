# Tamagotchi API Documentation

Модульная документация API в формате OpenAPI 3.0 для Redocly.

## Быстрый старт

```bash
cd docs
npm install
npm run dev
```

Откройте http://localhost:8080

## Команды

- `npm run dev` - Локальный просмотр документации
- `npm run build` - Сборка статической HTML документации
- `npm run lint` - Валидация OpenAPI спецификации
- `npm run bundle` - Создание единого JSON файла

## Структура

```
docs/
├── openapi.yaml              # Основной файл с общей информацией
├── components/
│   ├── schemas.yaml          # Схемы данных (User, Player, etc.)
│   └── responses.yaml        # Стандартные ответы API
├── paths/
│   ├── auth.yaml            # Эндпоинты аутентификации
│   ├── player.yaml          # Эндпоинты игрока
│   ├── customization.yaml   # Система кастомизации
│   ├── situations.yaml      # Ситуации
│   └── micro-actions.yaml   # Микродействия
├── redocly.yaml             # Конфигурация Redocly
└── package.json             # Зависимости
```

## Серверы

- Production: `http://31.130.149.164/api`
- Development: `http://localhost:8000/api`

## Аутентификация

JWT Bearer токен в заголовке: `Authorization: Bearer TOKEN`

## 🎨 Система кастомизации

Игра включает прогрессивную систему кастомизации с разблокировкой элементов по уровням.

### Примеры использования:

```bash
# Получить все кастомизации игрока
GET /api/customization
Authorization: Bearer YOUR_JWT_TOKEN

# Получить кастомизацию категории "футболки"
GET /api/customization/wardrobe_shirt
Authorization: Bearer YOUR_JWT_TOKEN

# Выбрать элемент кастомизации
POST /api/customization/wardrobe_shirt
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
  "selected": 3
}

# Отметить элементы как просмотренные
POST /api/customization/viewed
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
  "key": "wardrobe_shirt",
  "viewed_items": [1, 2, 3]
}
```

### Категории кастомизации:
- **Гардероб**: `wardrobe_shirt`, `wardrobe_pants`, `wardrobe_accessory`
- **Мебель**: `furniture_table`, `furniture_chair`, `furniture_lamp`
