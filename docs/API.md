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

### 2. Аналитические данные (Графики)

Эти endpoints возвращают агрегированные данные из "data mart" (суммарных таблиц).

#### GET `/api/charts/revenue`
Доходы по месяцам

**Query параметры:**
- `months` (int, default: 12) — количество месяцев для анализа.

**Ответ:**
```json
{
  "labels": ["Янв 2025", "Фев 2025"],
  "data": [150000.00, 180000.00],
  "total": 330000.00
}
```

---

#### GET `/api/charts/expenses`
Расходы по категориям (агрегированные за период)

**Query параметры:**
- `months` (int, default: 12) — период агрегации.

**Ответ:**
```json
{
  "categories": ["Офис", "Маркетинг"],
  "data": [45000.00, 30000.00],
  "total": 75000.00
}
```

---

#### GET `/api/charts/profit`
Прибыль по месяцам

**Query параметры:**
- `months` (int, default: 6)

**Ответ:**
```json
{
  "labels": ["Янв 2025", "Фев 2025"],
  "data": [105000.00, 150000.00]
}
```

---

#### GET `/api/charts/available-budget-months`
Список месяцев, для которых есть бюджетные данные

**Ответ:**
```json
[
  {
    "value": { "year": 2025, "month": 1 },
    "label": "Январь 2025"
  }
]
```

---

#### GET `/api/charts/budget-vs-fact`
Сравнение плана и факта

**Требует:** права администратора

**Query параметры:**
- `year`, `month` (опционально) — данные за конкретный месяц.
- `months` (опционально, default: 6) — агрегация за период, если не указан конкретный месяц.

**Ответ:**
```json
{
  "categories": ["Офис", "Зарплаты"],
  "planned": [50000.00, 100000.00],
  "actual": [48000.00, 105000.00]
}
```

---

#### GET `/api/charts/roi`
ROI по проектам

**Требует:** права администратора

**Ответ:**
```json
{
  "projects": ["Project A"],
  "roi": [15.5],
  "investment": [100000.00],
  "return": [115500.00],
  "status": ["active"]
}
```

---

### 3. Отчеты и Экспорт

Эти endpoints доступны только администраторам.

#### GET `/api/reports/monthly-summary`
Данные для ежемесячного финансового отчета (JSON превью)

**Query параметры:**
- `from` (string, optional) — дата начала в формате `YYYY-MM`.
- `to` (string, optional) — дата конца в формате `YYYY-MM`.

**Ответ:**
```json
[
  {
    "year": 2025,
    "month": 1,
    "revenue_total": "150000.00",
    "expense_total": "100000.00",
    "profit_total": "50000.00",
    "profit_margin_pct": "33.33"
  }
]
```

---

#### GET `/api/reports/monthly-summary/export`
Экспорт ежемесячного финансового отчета

**Query параметры:**
- `from`, `to` — те же, что и выше.
- `format` (string, optional) — `csv` (по умолчанию) или `xlsx`.

**Ответ:** Файл выбранного формата.

---

#### GET `/api/reports/budget-plan-fact`
Данные для отчета План vs Факт (JSON превью)

**Query параметры:**
- `from` (string, optional) — `YYYY-MM`.
- `to` (string, optional) — `YYYY-MM`.

**Ответ:**
```json
[
  {
    "year": 2025,
    "month": 1,
    "category": "Офис",
    "planned_amount": "50000.00",
    "actual_amount": "48000.00",
    "delta_amount": "-2000.00",
    "delta_pct": "-4.00"
  }
]
```

---

#### GET `/api/reports/budget-plan-fact/export`
Экспорт отчета План vs Факт

**Query параметры:**
- `from`, `to` — те же, что и выше.
- `format` (string, optional) — `csv` (по умолчанию) или `xlsx`.

**Ответ:** Файл выбранного формата.

---

#### GET `/api/reports/operations/export`
Экспорт детальных операций (только экспорт)

**Query параметры:**
- `type` (string, required) — `expenses` или `revenues`.
- `from` (string, optional) — `YYYY-MM-DD`.
- `to` (string, optional) — `YYYY-MM-DD`.
- `format` (string, optional) — `csv` (по умолчанию) или `xlsx`.

**Ответ:** Файл выбранного формата.

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
const getRevenueChart = async (months = 12) => {
  const response = await axios.get('/api/charts/revenue', {
    params: { months }
  });
  return response.data;
};

// Получение сравнения бюджета (админ)
const getBudgetVsFact = async (year, month) => {
  const response = await axios.get('/api/charts/budget-vs-fact', {
    params: { year, month }
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

# Получение данных графика (с cookie сессии)
curl -X GET "http://localhost:8080/api/charts/revenue?months=12" \
  -b cookies.txt

# Выход
curl -X POST http://localhost:8080/api/logout \
  -b cookies.txt
```

---

## Фильтрация и агрегация

### Периоды
В отличие от транзакционных данных, графики используют параметр `months` для определения глубины анализа в месяцах.

### Категории
Категории в агрегированных данных соответствуют категориям из исходных таблиц `expenses` и `budgets`.

---

## Примечания

1. **CSRF защита:** При работе через браузер необходимо сначала получить CSRF cookie через `/sanctum/csrf-cookie`
2. **CORS:** Настроен для работы с `http://localhost:5173` (Vite dev server)
3. **Data Mart:** Данные в графиках могут обновляться с задержкой, если не запущен процесс пересчета витрин.
4. **Thin Frontend:** Вся логика агрегации вынесена на сторону сервера.

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
fetch('http://localhost:8080/api/charts/revenue?months=6', {
  credentials: 'include'
})
  .then(res => res.json())
  .then(data => console.log(data));
```

