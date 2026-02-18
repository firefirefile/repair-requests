#!/bin/bash

echo "========================================="
echo "🔍 ДИАГНОСТИКА CSRF"
echo "========================================="
echo ""

# 1. Получаем страницу логина и CSRF
echo "1. Получаем CSRF токен..."
CSRF=$(curl -s -c cookies.txt http://localhost:8080/login | grep -o 'name="_token" value="[^"]*"' | cut -d'"' -f4)
echo "   CSRF токен: $CSRF"
echo ""

# 2. Пробуем залогиниться с CSRF
echo "2. Логинимся как диспетчер..."
RESULT=$(curl -s -b cookies.txt -c cookies.txt -X POST http://localhost:8080/login \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$CSRF&email=dispatcher@example.com&password=password" \
    -w "%{http_code}" -o /tmp/login_result.html)

echo "   HTTP код: $RESULT"
if [ "$RESULT" = "302" ]; then
    echo "   ✅ Логин успешен"
else
    echo "   ❌ Ошибка логина"
    cat /tmp/login_result.html
fi
echo ""

# 3. Проверяем что мы на странице диспетчера
echo "3. Проверка доступа к /dispatcher/requests..."
HTTP_CODE=$(curl -s -b cookies.txt -o /tmp/dispatcher.html -w "%{http_code}" http://localhost:8080/dispatcher/requests)
echo "   HTTP код: $HTTP_CODE"

if [ "$HTTP_CODE" = "200" ]; then
    echo "   ✅ Доступ есть"
else
    echo "   ❌ Редирект на логин"
    echo "   Содержимое ответа:"
    cat /tmp/dispatcher.html | head -5
fi
echo ""

# 4. Пробуем создать заявку с тем же CSRF
echo "4. Создание заявки..."
CSRF_NEW=$(curl -s -b cookies.txt http://localhost:8080/login | grep -o 'name="_token" value="[^"]*"' | cut -d'"' -f4)
echo "   Новый CSRF: $CSRF_NEW"
echo "   Старый CSRF: $CSRF"

RESULT=$(curl -s -b cookies.txt -X POST http://localhost:8080/requests \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$CSRF_NEW&client_name=Тест&phone=123&address=Тест&problem_text=Тест" \
    -w "%{http_code}" -o /tmp/create_result.html)

echo "   HTTP код создания: $RESULT"
if [ "$RESULT" = "302" ]; then
    echo "   ✅ Заявка создана"
else
    echo "   ❌ Ошибка создания"
    cat /tmp/create_result.html
fi

# Чистим
rm -f cookies.txt /tmp/login_result.html /tmp/dispatcher.html /tmp/create_result.html
