# Развертывание документации на сервере

## 🎯 Варианты развертывания

### Вариант 1: На том же сервере с API (рекомендуется)

#### Шаг 1: Подготовка сервера

```bash
# Проверяем, установлен ли Node.js
node --version

# Если нет, устанавливаем
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

#### Шаг 2: Автоматическое развертывание

```bash
# В папке проекта
cd docs
chmod +x deploy.sh
./deploy.sh
```

#### Шаг 3: Настройка nginx

**Вариант 3.1: Отдельный поддомен**
```bash
# Копируем конфиг
sudo cp nginx-docs.conf /etc/nginx/sites-available/api-docs

# Редактируем домен
sudo nano /etc/nginx/sites-available/api-docs
# Замените docs.your-domain.com на ваш домен

# Активируем
sudo ln -s /etc/nginx/sites-available/api-docs /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

**Вариант 3.2: Добавить в существующий конфиг API**
```bash
# Добавляем в основной конфиг API
sudo nano /etc/nginx/sites-available/your-api-config

# Добавляем в server блок:
location /docs {
    alias /var/www/api-docs;
    try_files $uri $uri/ /index.html;
}

sudo nginx -t
sudo systemctl reload nginx
```

#### Шаг 4: Результат

Документация будет доступна:
- `http://your-domain.com/docs` (если добавили в основной конфиг)
- `http://docs.your-domain.com` (если создали отдельный поддомен)

---

### Вариант 2: GitHub Pages (бесплатно)

#### Шаг 1: Подготовка репозитория
```bash
# Создаем ветку gh-pages
git checkout -b gh-pages

# Собираем документацию
cd docs
npm install
npm run build

# Копируем в корень
cp -r build/* ../
git add .
git commit -m "Deploy docs to GitHub Pages"
git push origin gh-pages
```

#### Шаг 2: Настройка в GitHub
1. Идите в Settings → Pages
2. Выберите Source: Deploy from branch
3. Branch: gh-pages
4. Folder: / (root)

Документация будет доступна: `https://username.github.io/repository-name`

---

### Вариант 3: Vercel/Netlify (бесплатно)

#### Vercel
```bash
npm install -g vercel
cd docs
vercel --prod
```

#### Netlify
1. Подключите GitHub репозиторий к Netlify
2. Build command: `cd docs && npm run build`
3. Publish directory: `docs/build`

---

## 🔄 Автоматическое обновление

### Создание хука для автообновления
```bash
# Создаем скрипт обновления
sudo nano /usr/local/bin/update-api-docs
```

```bash
#!/bin/bash
cd /path/to/your/project/docs
git pull origin main
npm install
npm run build
cp -r build/* /var/www/api-docs/
sudo systemctl reload nginx
echo "$(date): API docs updated" >> /var/log/api-docs-update.log
```

```bash
# Делаем исполняемым
sudo chmod +x /usr/local/bin/update-api-docs

# Добавляем в cron для автообновления (опционально)
sudo crontab -e
# Добавляем: 0 */6 * * * /usr/local/bin/update-api-docs
```

---

## 🎨 Кастомизация

### Изменение темы Redocly
В `redocly.yaml`:
```yaml
theme:
  colors:
    primary:
      main: '#your-color'
  logo:
    url: 'https://your-domain.com/logo.png'
```

### Добавление кастомного CSS
Создайте `docs/custom.css` и укажите в конфигурации.

---

## 🔧 Команды для управления

```bash
# Разработка (локальный просмотр)
npm run dev

# Сборка для продакшена
npm run build

# Проверка спецификации
npm run lint

# Создание единого JSON файла
npm run bundle

# Обновление на сервере
./deploy.sh
```

---

## 📊 Мониторинг

### Проверка доступности
```bash
curl -I http://your-domain.com/docs
```

### Просмотр логов nginx
```bash
sudo tail -f /var/log/nginx/api-docs-access.log
sudo tail -f /var/log/nginx/api-docs-error.log
```

---

## 🚨 Решение проблем

### Документация не открывается
1. Проверьте права на файлы: `ls -la /var/www/api-docs/`
2. Проверьте конфиг nginx: `sudo nginx -t`
3. Посмотрите логи: `sudo tail -f /var/log/nginx/error.log`

### Ошибки сборки
1. Проверьте валидность OpenAPI: `npm run lint`
2. Убедитесь что Node.js >= 16: `node --version`
3. Очистите кеш: `rm -rf node_modules && npm install`
