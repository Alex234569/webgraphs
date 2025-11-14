# Авторизация в WebGraphs

## Учетные записи

После выполнения `php artisan migrate:fresh --seed` создаются 2 пользователя:

| Email        | Пароль    | Роль   |
|--------------|-----------|--------|
| admin@app.me | **admin** | admin  |
| user@app.me  | **user**  | user   |

## Как работает авторизация

### Backend (Laravel)

**API Endpoints:**
- `POST /api/login` - вход
- `POST /api/logout` - выход (требует авторизации)
- `GET /api/me` - получить текущего пользователя (требует авторизации)

**Контроллер:** `app/Http/Controllers/Api/AuthController.php`

**Использование сессий:**
- Авторизация работает через сессии Laravel (не токены)
- CSRF защита включена
- Cookie хранятся в браузере

### Frontend (Vue.js)

**Composable:** `resources/js/composables/useAuth.js`

Методы:
- `login(email, password)` - вход
- `logout()` - выход
- `fetchUser()` - получить пользователя
- `isAuthenticated()` - проверка авторизации
- `isAdmin()` - проверка роли админа

**Компоненты:**
- `Login.vue` - страница входа
- `App.vue` - хедер с информацией о пользователе

**Защита маршрутов:**
```javascript
// В router/index.js
{
  path: '/graphs',
  meta: { requiresAuth: true },  // Требует авторизации
}
```

## Использование в компонентах

```vue
<script setup>
import { useAuth } from '../composables/useAuth';

const { user, isAuthenticated, isAdmin, logout } = useAuth();
</script>

<template>
  <div v-if="isAuthenticated()">
    <p>Привет, {{ user.name }}!</p>
    <p v-if="isAdmin()">Вы администратор</p>
    <button @click="logout">Выход</button>
  </div>
</template>
```

## Проверка роли на backend

```php
// В контроллере
if (auth()->user()->isAdmin()) {
    // Действия для админа
}

// Или через middleware (нужно создать)
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('auth')
    ->middleware('admin');
```

## Создание middleware для проверки роли

```bash
docker compose exec app php artisan make:middleware EnsureUserIsAdmin
```

```php
// app/Http/Middleware/EnsureUserIsAdmin.php
public function handle($request, Closure $next)
{
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Доступ запрещен');
    }
    return $next($request);
}
```

Зарегистрировать в `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);
})
```

## Тестирование

1. Откройте http://localhost:8080
2. Вы будете перенаправлены на /login
3. Войдите с учетными данными admin@app.me / admin
4. После входа вы попадете на главную страницу
5. В правом верхнем углу увидите аватар и имя пользователя с тегом "Admin"
6. Нажмите на аватар → Выход

## Отладка

**Проверить сессию в БД:**
```bash
docker compose exec db mysql -u webgraphs_user -psecret webgraphs -e "SELECT * FROM sessions;"
```

**Очистить сессии:**
```bash
docker compose exec app php artisan session:clear
```
