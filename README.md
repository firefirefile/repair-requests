# 🛠️ Веб-сервис "Заявки в ремонтную службу"

Тестовое задание: веб-приложение для приёма и обработки заявок в ремонтную службу с разделением по ролям (диспетчер/мастер).

## 🚀 Быстрый старт

### Требования
- Docker и Docker Compose (или Colima на macOS)
- Git

### Установка и запуск

```bash
# Клонировать репозиторий
git clone https://github.com/firefirefile/repair-requests
cd repair-requests

# Запустить контейнеры (если используешь Colima)
colima start
docker-compose up -d

# Установить зависимости и настроить приложение
docker-compose exec app composer install
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

# Запустить миграции и сиды
docker-compose exec app php artisan migrate:fresh --seed

# Готово! Открой в браузере
open http://localhost:8080
