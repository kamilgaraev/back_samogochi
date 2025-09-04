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
