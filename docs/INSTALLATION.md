# Установка и развертывание

## Требования

- **Docker** версии 20.10 или выше
- **Docker Compose** версии 2.0 или выше
- **Git** для клонирования репозитория
- Минимум **4 GB RAM** для работы контейнеров

## Быстрая установка

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd webgraphs
```

### 2. Создание файла окружения

```bash
cp .env.example .env
```

**Настройка `.env` файла:**
```env
APP_NAME=WebGraphs
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=webgraphs
DB_USERNAME=root
DB_PASSWORD=root

SESSION_DRIVER=cookie
SESSION_LIFETIME=120
```

### 3. Запуск Docker контейнеров

```bash
docker compose up -d
```

**Проверка статуса:**
```bash
docker compose ps
```

Должны быть запущены 4 контейнера:
- `webgraphs-app-1` (PHP/Laravel)
- `webgraphs-db-1` (MySQL)
- `webgraphs-nginx-1` (Nginx)
- `webgraphs-web-1` (Node.js/Vite)

### 4. Установка зависимостей

**Backend (Laravel):**
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

**Frontend (Vue.js):**
```bash
docker compose exec web npm install
```

### 5. Настройка базы данных

```bash
# Выполнить миграции и заполнить тестовыми данными
docker compose exec app php artisan migrate:fresh --seed
```

**Результат:** Будут созданы таблицы и 2 тестовых пользователя:
- `admin@app.me` / `admin` (роль: admin)
- `user@app.me` / `user` (роль: user)

### 6. Доступ к приложению

Откройте в браузере:
- **Frontend (Vue.js):** http://localhost:5173
- **Backend API:** http://localhost:8080/api
