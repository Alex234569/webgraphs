# Развертывание в Kubernetes (Minikube)

Этот каталог содержит манифесты и Docker-файлы для развертывания проекта WebGraphs в кластере Kubernetes.

## Предварительные требования

1.  **Docker**
2.  **kubectl**
3.  **minikube**

## Подготовка Minikube

Запустите minikube и включите аддон ingress:

```bash
minikube start
minikube addons enable ingress
```

## Сборка и загрузка образов

Поскольку мы используем локальный рабочий процесс без внешнего реестра, необходимо собрать образы и загрузить их напрямую в minikube.

```bash
# Сборка образов
docker build -t webgraphs-backend-php:latest -f k8s/docker/php/Dockerfile .
docker build -t webgraphs-backend-nginx:latest -f k8s/docker/nginx/Dockerfile .

# Загрузка образов в minikube
minikube image load webgraphs-backend-php:latest
minikube image load webgraphs-backend-nginx:latest
```

## Развертывание

Для развертывания всех компонентов используется **Kustomize** (встроен в kubectl).

Применить все манифесты одной командой:

```bash
kubectl apply -k k8s/manifests/
```

Эта команда автоматически создаст Namespace, секреты, базу данных, бэкенд и ингресс в правильном порядке.

### Структура манифестов
Манифесты организованы по логическим группам:
- `k8s/manifests/infra/`: Общая инфраструктура (Namespace, Ingress).
- `k8s/manifests/db/`: База данных MySQL и её секреты.
- `k8s/manifests/app/`: Бэкенд (API + Frontend), Worker очереди, CronJob и конфигурации.
- `k8s/manifests/kustomization.yaml`: Главный файл сборки.

## Настройка приложения

### 1. Миграции и сидеры
После того как поды бэкенда и базы данных будут запущены, выполните миграции:

```bash
kubectl exec -it $(kubectl get pods -n webgraphs -l app=backend -o jsonpath="{.items[0].metadata.name}") -n webgraphs -c php -- php artisan migrate --force
kubectl exec -it $(kubectl get pods -n webgraphs -l app=backend -o jsonpath="{.items[0].metadata.name}") -n webgraphs -c php -- php artisan db:seed --force
```

### 2. Ключ приложения
В файле `k8s/manifests/app/backend-secret.yaml` указан заглушечный `APP_KEY`. Для реальной работы сгенерируйте ключ и обновите секрет:
```bash
php artisan key:generate --show
```
Скопируйте ключ и обновите `backend-secret.yaml`, затем примените манифесты снова:
```bash
kubectl apply -k k8s/manifests/
```

## Доступ к приложению

Добавьте запись в ваш файл `/etc/hosts` (или `C:\Windows\System32\drivers\etc\hosts` на Windows):

```
<IP_АДРЕС_MINIKUBE> webgraphs.local
```
Узнать IP можно командой `minikube ip`.

После этого приложение будет доступно по адресу: `http://webgraphs.local`

## Проверка работы

### Очередь (Queue Worker)
Проверить статус воркера можно через логи пода:
```bash
kubectl logs -f deployment/queue-worker -n webgraphs
```

### Пересчет витрин (CronJob)
CronJob настроен на ежедневный запуск. Для ручного запуска джобы для проверки:
```bash
kubectl create job --from=cronjob/metrics-rebuild test-rebuild -n webgraphs
kubectl logs -l job-name=test-rebuild -n webgraphs
```

## Устранение неполадок

*   **Ошибка PullImage:** Убедитесь, что в манифестах указано `imagePullPolicy: Never` и вы выполнили `minikube image load`.
*   **База данных не готова:** MySQL может загружаться дольше других сервисов. Поды бэкенда могут перезапускаться, пока БД не станет доступной — это нормально.
*   **Ingress не работает:** Убедитесь, что аддон включен (`minikube addons enable ingress`) и IP-адрес в hosts указан верно.
*   **Права доступа:** Laravel требует прав на запись в `storage` и `bootstrap/cache`. В Docker-файле это настроено, но при монтировании томов могут возникнуть нюансы (в данных манифестах тома для кода не монтируются, используется содержимое образа).
