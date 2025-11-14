# Архитектура системы

## Общее описание

WebGraphs — это SPA (Single Page Application) для визуализации финансовых данных с использованием современного стека технологий.

## Архитектурная схема

```
┌─────────────────┐      HTTP/JSON       ┌─────────────────┐      PDO/MySQL     ┌─────────────┐
│   Vue 3 SPA     │ ◄──────────────────► │  Laravel API    │ ◄─────────────────►│   MySQL     │
│  (Frontend)     │                      │   (Backend)     │                    │  (Database) │
└─────────────────┘                      └─────────────────┘                    └─────────────┘
        │                                         │
        │                                         │
    Vite Dev                                 Sanctum/Session
    Server                                   Authentication
```

## Компоненты системы

### 1. Frontend (Vue.js 3)

**Технологии:**
- Vue 3 с Composition API
- Vue Router 4 для маршрутизации
- Element Plus для UI компонентов
- ECharts (vue-echarts) для визуализации графиков
- Axios для HTTP запросов

**Структура:**
```
resources/js/
├── app.js                  # Точка входа, инициализация Vue
├── App.vue                 # Корневой компонент с навигацией
├── router/index.js         # Конфигурация маршрутов
├── views/                  # Страницы приложения
│   ├── Login.vue          # Страница авторизации
│   └── Graphs.vue         # Главная страница с табами
├── components/             # Переиспользуемые компоненты
│   └── tabs/
│       ├── FinanceTab.vue      # Вкладка финансовых графиков
│       └── AnalyticsTab.vue    # Вкладка аналитики (только для admin)
└── composables/            # Переиспользуемая логика
    └── useAuth.js         # Логика аутентификации
```

**Принципы:**
- Composition API для реактивности
- Composables для переиспользования логики
- Scoped стили для изоляции CSS
- Защита маршрутов через navigation guards
- Табовая структура для разделения контента

### 2. Backend (Laravel 12)

**Архитектурный паттерн:** MVC (Model-View-Controller)

**Структура:**
```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php      # Аутентификация
│   │       ├── ChartsController.php    # Данные для графиков
│   │       └── ExportController.php    # Экспорт данных
│   └── Middleware/
│       └── EnsureUserIsAdmin.php       # Проверка ролей
├── Models/
│   ├── User.php                        # Пользователь
│   ├── Expense.php                     # Расходы
│   ├── Revenue.php                     # Доходы
│   ├── Budget.php                      # Бюджеты
│   └── Project.php                     # Проекты
```

**API Endpoints:**

*Аутентификация:*
```
POST   /api/login           # Вход в систему
POST   /api/logout          # Выход из системы
GET    /api/me              # Текущий пользователь
```

*Графики (доступны всем авторизованным):*
```
GET    /api/charts/revenue      # Доходы по месяцам
GET    /api/charts/expenses     # Расходы по категориям
GET    /api/charts/profit       # Прибыль за период
```

*Аналитика (только для администраторов):*
```
GET    /api/charts/budget-vs-fact           # Бюджет vs Факт
GET    /api/charts/available-budget-months  # Доступные месяцы бюджета
GET    /api/charts/roi                      # ROI по проектам
```

### 3. База данных (MySQL)

**ER-диаграмма:**

```
┌──────────────┐
│    users     │
├──────────────┤
│ id           │
│ name         │
│ email        │
│ password     │
│ role         │
└──────────────┘

┌──────────────┐
│   expenses   │
├──────────────┤
│ id           │
│ amount       │
│ category     │
│ date         │
│ description  │
└──────────────┘

┌──────────────┐
│   revenues   │
├──────────────┤
│ id           │
│ amount       │
│ date         │
│ description  │
│ category     │
└──────────────┘

┌──────────────┐
│   budgets    │
├──────────────┤
│ id           │
│ category     │
│ planned_amount│
│ actual_amount│
│ year         │
│ month        │
└──────────────┘

┌──────────────┐
│   projects   │
├──────────────┤
│ id           │
│ name         │
│ investment   │
│ return       │
│ roi          │
│ start_date   │
│ end_date     │
│ status       │
└──────────────┘
```

**Примечание:** Финансовые данные не привязаны к конкретным пользователям через `user_id`. Это общие данные компании, к которым администраторы имеют полный доступ, а обычные пользователи видят ограниченный набор графиков.

## Модель безопасности

### Аутентификация

**Механизм:** Session-based authentication через Laravel Sanctum

**Процесс:**
1. Пользователь отправляет email/password
2. Laravel проверяет credentials
3. При успехе создается сессия
4. Cookie с session_id отправляется клиенту
5. Последующие запросы используют этот cookie

### Авторизация (Role-Based Access Control)

**Роли:**
- `admin` — доступ ко всем графикам и аналитике
- `user` — доступ только к базовым финансовым графикам

**Реализация:**
```php
// В модели User.php
public function isAdmin(): bool {
    return $this->role === 'admin';
}

// Middleware EnsureUserIsAdmin.php
public function handle(Request $request, Closure $next): Response
{
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Доступ запрещен');
    }
    return $next($request);
}

// В routes/api.php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/charts/budget-vs-fact', [ChartsController::class, 'budgetVsFact']);
    Route::get('/charts/roi', [ChartsController::class, 'roi']);
});
```

**На фронтенде:**
```javascript
// В router/index.js
router.beforeEach(async (to, from, next) => {
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    next({ name: 'Login' });
  } else {
    next();
  }
});

// В Graphs.vue
<el-tab-pane label="Аналитика" name="analytics" :disabled="!isAdmin">
  <AnalyticsTab />
</el-tab-pane>
```

## Визуализация данных

**Библиотека:** ECharts (через vue-echarts)

**Типы графиков:**

**Вкладка "Финансы" (доступна всем):**
1. **Line Chart** — доходы по месяцам (динамика во времени)
2. **Pie Chart** — расходы по категориям (распределение)
3. **Bar Chart** — прибыль за период (доходы - расходы)

**Вкладка "Аналитика" (только для admin):**
1. **Bar Chart** — бюджет vs факт (плановые и фактические показатели)
2. **Horizontal Bar Chart** — ROI по проектам (рентабельность инвестиций)

**Фильтрация:**
- По датам через DatePicker (выбор произвольного диапазона)
- По месяцам для бюджетных данных (выпадающий список доступных периодов)
- Автоматический расчет количества месяцев между датами

## Развертывание

**Контейнеризация:** Docker Compose

**Сервисы:**
```yaml
app:    PHP-FPM 8.3 + Laravel
nginx:  Веб-сервер (порт 8080)
db:     MySQL 8.0 (порт 3306)
web:    Node.js 22 + Vite (порт 5173)
```

**Преимущества:**
- Изолированное окружение
- Простое развертывание на любой системе
- Консистентность между dev/prod
- Автоматический запуск всех сервисов одной командой

## Принятые технические решения

1. **SPA вместо серверного рендеринга** — лучший UX, современный подход
2. **Session auth вместо JWT** — проще для небольшого проекта, безопаснее для веб-приложений
3. **Docker Compose** — упрощает развертывание и настройку окружения
4. **Composition API** — современный подход Vue 3, лучшая переиспользуемость кода
5. **Element Plus** — готовые компоненты, быстрая разработка, профессиональный вид
6. **ECharts** — мощная библиотека с богатыми возможностями кастомизации
7. **MySQL вместо PostgreSQL** — меньше требований к окружению, проще настройка
8. **Табовая структура** — удобное разделение контента по уровням доступа
9. **Общие финансовые данные** — упрощение модели данных для учебного проекта
