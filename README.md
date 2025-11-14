# WebGraphs

Веб-приложение для визуализации финансовых данных компании с использованием интерактивных графиков.

## Описание

Система визуализации финансовой информации с разделением доступа по ролям. Предоставляет интерактивные графики для анализа доходов, расходов, бюджета и ROI проектов.

**Ключевые возможности:**
- Визуализация финансовых данных (доходы, расходы, прибыль)
- Аналитические графики для администраторов (бюджет vs факт, ROI)
- Фильтрация по датам и периодам
- Разграничение доступа по ролям (admin/user)
- Session-based аутентификация

## Технологический стек

**Backend:**
- Laravel 12 (PHP 8.2)
- MySQL 8.0
- RESTful API
- Sanctum (Session Auth)

**Frontend:**
- Vue.js 3 (Composition API)
- Element Plus (UI компоненты)
- ECharts (визуализация графиков)
- Vue Router 4
- Vite

**Инфраструктура:**
- Docker Compose
- Nginx
- Node.js 22

## Быстрый старт

```bash
# Запуск контейнеров
docker compose up -d

# Установка зависимостей (первый запуск)
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec web npm install
```

**Доступ к приложению:**
- Frontend: http://localhost:5173
- Backend API: http://localhost:8080

**Тестовые пользователи:**

| Email | Пароль | Роль |
|-------|--------|------|
| admin@app.me | admin | Admin |
| user@app.me | user | User |

## Документация

Полная документация проекта находится в директории `docs/`:

- **[INSTALLATION.md](docs/INSTALLATION.md)** - Детальная инструкция по установке, настройке окружения и развертыванию
- **[ARCHITECTURE.md](docs/ARCHITECTURE.md)** - Описание архитектуры системы, структуры проекта и принятых технических решений
- **[API.md](docs/API.md)** - Полная спецификация API endpoints с примерами запросов и ответов
- **[AUTHENTICATION.md](docs/AUTHENTICATION.md)** - Документация по аутентификации и авторизации

## Основные команды

```bash
# Запуск и остановка
docker compose up -d
docker compose down

# Просмотр логов
docker compose logs -f app

# Выполнение команд Laravel
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed

# Установка пакетов
docker compose exec app composer install
docker compose exec web npm install
```

Подробные инструкции см. в [INSTALLATION.md](docs/INSTALLATION.md)
