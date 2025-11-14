# API Документация

## Базовый URL

```
http://localhost:8080/api
```

## Аутентификация

Все защищенные endpoints требуют аутентификации через сессию Laravel Sanctum.

**CSRF токен:** Автоматически обрабатывается через Laravel Sanctum cookies.

---

## Endpoints

### 1. Аутентификация

#### POST `/api/login`
Вход в систему

**Тело запроса:**
```json
{
  "email": "admin@app.me",
  "password": "admin"
}
```

**Ответ (успех):**
```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@app.me",
    "role": "admin"
  }
}
```

**Ответ (ошибка):**
```json
{
  "message": "The provided credentials are incorrect."
}
```

**Статусы:**
- `200` — успешный вход
- `422` — неверные credentials

---

#### POST `/api/logout`
Выход из системы

**Требует:** аутентификацию

**Ответ:**
```json
{
  "message": "Logged out successfully"
}
```

**Статусы:**
- `200` — успешный выход

---

#### GET `/api/me`
Получить данные текущего пользователя

**Требует:** аутентификацию

**Ответ:**
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@app.me",
  "role": "admin"
}
```

**Статусы:**
- `200` — успех
- `401` — не авторизован

---

### 2. Финансовые данные

#### GET `/api/expenses`
Получить список расходов

**Требует:** аутентификацию

**Query параметры:**
```
?period=month           # Период: month, quarter, year, custom
&start_date=2025-01-01  # Начальная дата (для custom)
&end_date=2025-12-31    # Конечная дата (для custom)
&category=office        # Фильтр по категории (опционально)
```

**Примеры запросов:**
```bash
# Расходы за текущий месяц
GET /api/expenses?period=month

# Расходы за квартал
GET /api/expenses?period=quarter

# Расходы за произвольный период
GET /api/expenses?period=custom&start_date=2025-01-01&end_date=2025-03-31

# Расходы по категории
GET /api/expenses?period=year&category=office
```

**Ответ:**
```json
[
  {
    "id": 1,
    "user_id": 1,
    "amount": 15000.00,
    "category": "office",
    "description": "Офисная мебель",
    "date": "2025-01-15",
    "created_at": "2025-01-15T10:00:00.000000Z"
  },
  {
    "id": 2,
    "user_id": 1,
    "amount": 8500.50,
    "category": "salaries",
    "description": "Зарплата сотрудников",
    "date": "2025-01-20",
    "created_at": "2025-01-20T10:00:00.000000Z"
  }
]
```

**Авторизация:**
- `admin` — видит все расходы
- `user` — видит только свои расходы

**Статусы:**
- `200` — успех
- `401` — не авторизован

---

#### GET `/api/revenues`
Получить список доходов

**Требует:** аутентификацию

**Query параметры:** (аналогично `/api/expenses`)
```
?period=month
&start_date=2025-01-01
&end_date=2025-12-31
&source=sales           # Фильтр по источнику (опционально)
```

**Ответ:**
```json
[
  {
    "id": 1,
    "user_id": 1,
    "amount": 50000.00,
    "source": "sales",
    "description": "Продажа продукции",
    "date": "2025-01-10",
    "created_at": "2025-01-10T10:00:00.000000Z"
  }
]
```

**Статусы:**
- `200` — успех
- `401` — не авторизован

---

#### GET `/api/budgets`
Получить список бюджетов

**Требует:** аутентификацию

**Query параметры:**
```
?period=month           # Период бюджета
&category=office        # Категория (опционально)
```

**Ответ:**
```json
[
  {
    "id": 1,
    "user_id": 1,
    "amount": 100000.00,
    "category": "office",
    "period": "2025-01",
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
]
```

**Статусы:**
- `200` — успех
- `401` — не авторизован

---

#### GET `/api/projects`
Получить список проектов

**Требует:** аутентификацию

**Query параметры:**
```
?status=active          # Статус: active, completed, on_hold
```

**Ответ:**
```json
[
  {
    "id": 1,
    "user_id": 1,
    "name": "Разработка сайта",
    "budget": 200000.00,
    "spent": 150000.00,
    "status": "active",
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
]
```

**Статусы:**
- `200` — успех
- `401` — не авторизован

---

## Коды ошибок

| Код | Описание |
|-----|----------|
| `200` | Успешный запрос |
| `401` | Не авторизован (требуется вход) |
| `403` | Доступ запрещен (недостаточно прав) |
| `404` | Ресурс не найден |
| `422` | Ошибка валидации данных |
| `500` | Внутренняя ошибка сервера |

---

## Примеры использования

### JavaScript (Axios)

```javascript
import axios from 'axios';

// Настройка axios для работы с Laravel Sanctum
axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'http://localhost:8080';

// Вход
const login = async (email, password) => {
  await axios.get('/sanctum/csrf-cookie');
  const response = await axios.post('/api/login', { email, password });
  return response.data;
};

// Получение расходов за месяц
const getExpenses = async () => {
  const response = await axios.get('/api/expenses', {
    params: { period: 'month' }
  });
  return response.data;
};

// Получение расходов за произвольный период
const getCustomExpenses = async (startDate, endDate) => {
  const response = await axios.get('/api/expenses', {
    params: {
      period: 'custom',
      start_date: startDate,
      end_date: endDate
    }
  });
  return response.data;
};
```

### cURL

```bash
# Вход в систему
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@app.me","password":"admin"}' \
  -c cookies.txt

# Получение расходов (с cookie сессии)
curl -X GET "http://localhost:8080/api/expenses?period=month" \
  -b cookies.txt

# Выход
curl -X POST http://localhost:8080/api/logout \
  -b cookies.txt
```

---

## Фильтрация и сортировка

### Периоды

| Значение | Описание |
|----------|----------|
| `month` | Текущий месяц |
| `quarter` | Текущий квартал |
| `year` | Текущий год |
| `custom` | Произвольный период (требует start_date и end_date) |

### Категории расходов

- `office` — офисные расходы
- `salaries` — зарплаты
- `marketing` — маркетинг
- `equipment` — оборудование
- `other` — прочее

### Источники доходов

- `sales` — продажи
- `services` — услуги
- `investments` — инвестиции
- `other` — прочее

---

## Примечания

1. **CSRF защита:** При работе через браузер необходимо сначала получить CSRF cookie через `/sanctum/csrf-cookie`
2. **CORS:** Настроен для работы с `http://localhost:5173` (Vite dev server)
3. **Кэширование:** Рекомендуется кэшировать данные на клиенте для уменьшения нагрузки
4. **Rate limiting:** Не настроен (можно добавить при необходимости)

---

## Тестирование API

### Через Postman

1. Импортируйте коллекцию endpoints
2. Включите "Send cookies automatically"
3. Выполните запрос `/api/login` для получения сессии
4. Все последующие запросы будут использовать эту сессию

### Через браузер (DevTools)

```javascript
// В консоли браузера после успешного входа
fetch('http://localhost:8080/api/expenses?period=month', {
  credentials: 'include'
})
  .then(res => res.json())
  .then(data => console.log(data));
```

