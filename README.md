# WebGraphs - Приложение для финансовых графиков

SPA приложение на Laravel + Vue.js для демонстрации финансовых данных департамента.

## Технологии

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 (Composition API)
- **UI Framework**: Element Plus
- **Стили**: Less (с иерархией)
- **Роутинг**: Vue Router 4
- **Сборка**: Vite
- **Окружение**: Docker Compose

## Структура проекта

```
resources/js/
├── App.vue              # Корневой компонент с навигацией
├── app.js               # Точка входа + настройка Element Plus
├── router/
│   └── index.js         # Конфигурация маршрутов
├── views/               # Страницы
│   ├── Home.vue         # Главная
│   └── Graphs.vue       # Графики
└── components/          # Переиспользуемые компоненты (создавайте здесь)
```

## Docker контейнеры

- **app** (PHP 8.2) - Laravel + Composer
- **db** (MySQL 8.0) - База данных
- **nginx** - http://localhost:8080
- **web** (Node 22) - Vite dev server http://localhost:5173

## Быстрый старт

```bash
# Запуск контейнеров
docker compose up -d

# Проверка статуса
docker compose ps

# Логи Vite (для отладки)
docker compose logs -f web

# Остановка
docker compose down
```

## Полезные команды

### Laravel (PHP)
```bash
# Artisan команды
docker compose exec app php artisan migrate
docker compose exec app php artisan make:controller GraphController

# Composer
docker compose exec app composer require package/name
```

### Frontend (Node)
```bash
# Установка npm пакетов
docker compose exec web npm install package-name

# Запуск внутри контейнера
docker compose exec web sh
```

## Работа со стилями

Используем **Less** с вложенностью:

```vue
<style lang="less" scoped>
.my-component {
  padding: 20px;

  .nested-element {
    color: #409eff;

    &:hover {
      opacity: 0.8;
    }
  }
}
</style>
```

## Element Plus компоненты

Доступны глобально, импорт не нужен:

```vue
<template>
  <el-button type="primary">Кнопка</el-button>
  <el-card>Карточка</el-card>
  <el-table :data="tableData">...</el-table>
</template>
```
