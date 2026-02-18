#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "========================================="
echo "🔧 ТЕСТИРОВАНИЕ ЗАЩИТЫ ОТ ГОНОК"
echo "========================================="
echo ""

COOKIE_JAR="cookies.txt"
RESULTS_FILE="/tmp/race_results.txt"
DEBUG_DIR="/tmp/race_debug"

# Clean up any previous run
rm -f $COOKIE_JAR $RESULTS_FILE
mkdir -p $DEBUG_DIR

# Debug mode: set to 1 to save all HTML pages
DEBUG=0

# Check for debug flag
if [[ "$1" == "--debug" ]]; then
    DEBUG=1
    echo -e "${YELLOW}🔧 Debug mode enabled${NC}"
fi

# Функция для получения CSRF из любой страницы
get_csrf() {
    local url=$1
    local page_content=$(curl -s -b $COOKIE_JAR -c $COOKIE_JAR "$url")

    # Debug: save page content if DEBUG enabled
    if [ $DEBUG -eq 1 ]; then
        local debug_file=$DEBUG_DIR/$(echo $url | sed 's/[^a-zA-Z0-9]/_/g').html
        echo "$page_content" > "$debug_file"
        echo -e "${YELLOW}[DEBUG] Saved $url to $(basename $debug_file)${NC}" >&2
    fi

    # Ищем CSRF в meta теге
    local csrf=$(echo "$page_content" | grep -o 'meta name="csrf-token" content="[^"]*"' | head -1 | cut -d'"' -f4)

    # Если не нашли, ищем в форме
    if [ -z "$csrf" ]; then
        csrf=$(echo "$page_content" | grep -o 'name="_token" value="[^"]*"' | head -1 | cut -d'"' -f4)
    fi

    # Debug: show what we found
    if [ $DEBUG -eq 1 ]; then
        echo -e "${YELLOW}[DEBUG] URL: $url${NC}" >&2
        echo -e "${YELLOW}[DEBUG] CSRF found: ${csrf:-"NOT FOUND"}${NC}" >&2
        if [ -z "$csrf" ]; then
            echo -e "${YELLOW}[DEBUG] Page snippet:$(echo "$page_content" | head -20)${NC}" >&2
        fi
    fi

    echo "$csrf"
}

# 1. Логинимся как диспетчер
echo -n "🔑 Логинимся как диспетчер... "

CSRF=$(get_csrf "http://localhost:8080/login")
if [ -z "$CSRF" ]; then
    echo -e "\n${RED}❌ Не удалось получить CSRF${NC}"
    exit 1
fi

LOGIN_RESPONSE=$(curl -s -b $COOKIE_JAR -c $COOKIE_JAR -X POST "http://localhost:8080/login" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$CSRF&email=dispatcher@example.com&password=password" \
    -w "%{http_code}" -o /dev/null)

if [ "$LOGIN_RESPONSE" = "302" ]; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${RED}❌ Ошибка входа (код: $LOGIN_RESPONSE)${NC}"
    exit 1
fi

sleep 1

# 2. Создаем заявку (форма на главной странице)
echo -n "📝 Создаем заявку... "

# Получаем CSRF с главной страницы
CSRF=$(get_csrf "http://localhost:8080/")
if [ -z "$CSRF" ]; then
    echo -e "\n${RED}❌ Не удалось получить CSRF${NC}"
    exit 1
fi

CREATE_RESPONSE=$(curl -s -b $COOKIE_JAR -c $COOKIE_JAR -X POST "http://localhost:8080/requests" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$CSRF&client_name=Тестовый+клиент&phone=%2B79991234567&address=Тестовый+адрес&problem_text=Тестовая+проблема" \
    -w "%{http_code}" -o /dev/null)

if [ "$CREATE_RESPONSE" = "302" ]; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "\n${RED}❌ Ошибка создания (код: $CREATE_RESPONSE)${NC}"
    exit 1
fi

sleep 4

# 3. Получаем ID созданной заявки
echo -n "🔍 Ищем ID заявки... "

DISPATCHER_HTML=$(curl -s -b $COOKIE_JAR "http://localhost:8080/dispatcher/requests")
echo "$DISPATCHER_HTML" > "$DEBUG_DIR/dispatcher_page.html"

# Ищем ID в формах назначения (первый метод: из URL формы назначения)
# Pattern: dispatcher/requests/ID/assign -> field3 = ID
REQUEST_ID=$(echo "$DISPATCHER_HTML" | grep -o 'dispatcher/requests/[0-9]\+/assign' | head -1 | cut -d'/' -f3)

# Если не нашли, ищем из ссылки просмотра (второй метод)
# Pattern: dispatcher/requests/ID -> field3 = ID
if [ -z "$REQUEST_ID" ]; then
    REQUEST_ID=$(echo "$DISPATCHER_HTML" | grep -o 'dispatcher/requests/[0-9]\+' | head -1 | cut -d'/' -f3)
fi

# Если все еще не нашли, ищем ID в первой ячейке таблицы (третий метод)
if [ -z "$REQUEST_ID" ]; then
    REQUEST_ID=$(echo "$DISPATCHER_HTML" | grep -o '<td>[0-9]\+</td>' | head -1 | grep -o '[0-9]\+')
fi

if [ -n "$REQUEST_ID" ]; then
    echo -e "${GREEN}✅ ID: $REQUEST_ID${NC}"
else
    echo -e "\n${RED}❌ Не удалось найти ID заявки${NC}"
    echo "Проверьте файл: $DEBUG_DIR/dispatcher_page.html"
    # Выводим отладку, что искали
    echo "Отладка:"
    echo "  - Проверка URL форм: $(echo "$DISPATCHER_HTML" | grep -c 'dispatcher/requests/[0-9]\+/assign') совпадений"
    echo "  - Проверка URL ссылок: $(echo "$DISPATCHER_HTML" | grep -c 'dispatcher/requests/[0-9]\+') совпадений"
    echo "  - Проверка ячеек таблицы: $(echo "$DISPATCHER_HTML" | grep -c '<td>[0-9]\+</td>') совпадений"
    exit 1
fi

# 4. Находим ID мастера
echo -n "👤 Ищем мастера... "

# Ищем Ивана
MASTER_ID=$(echo "$DISPATCHER_HTML" | grep -o 'value="[0-9]\+"[^>]*>Иван' | head -1 | cut -d'"' -f2)

if [ -n "$MASTER_ID" ]; then
    MASTER_EMAIL="ivan@example.com"
    MASTER_NAME="Иван"
else
    # Если Ивана нет, ищем Петра
    MASTER_ID=$(echo "$DISPATCHER_HTML" | grep -o 'value="[0-9]\+"[^>]*>Петр' | head -1 | cut -d'"' -f2)
    if [ -n "$MASTER_ID" ]; then
        MASTER_EMAIL="petr@example.com"
        MASTER_NAME="Петр"
    fi
fi

if [ -n "$MASTER_ID" ]; then
    echo -e "${GREEN}✅ $MASTER_NAME (ID: $MASTER_ID)${NC}"
else
    echo -e "\n${RED}❌ Мастер не найден${NC}"
    exit 1
fi

# 5. Назначаем мастера на заявку
echo -n "📌 Назначаем мастера... "

CSRF=$(get_csrf "http://localhost:8080/dispatcher/requests")

ASSIGN_RESPONSE=$(curl -s -b $COOKIE_JAR -c $COOKIE_JAR -X POST "http://localhost:8080/dispatcher/requests/$REQUEST_ID/assign" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$CSRF&_method=PATCH&master_id=$MASTER_ID" \
    -w "%{http_code}" -o /dev/null)

if [ "$ASSIGN_RESPONSE" = "302" ]; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "\n${RED}❌ Ошибка назначения (код: $ASSIGN_RESPONSE)${NC}"
    exit 1
fi

sleep 1

# 5.5. Выходим из системы диспетчера
echo -n "🚪 Выходим из системы... "

CSRF=$(get_csrf "http://localhost:8080/dispatcher/requests")
LOGOUT_RESPONSE=$(curl -s -b $COOKIE_JAR -c $COOKIE_JAR -X POST "http://localhost:8080/logout" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$CSRF" \
    -w "%{http_code}" -o /dev/null)

if [ "$LOGOUT_RESPONSE" = "302" ] || [ "$LOGOUT_RESPONSE" = "200" ]; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${YELLOW}⚠️  Предупреждение: выход не удался (код: $LOGOUT_RESPONSE)${NC}"
fi

sleep 1

# 6. Логинимся как мастер
echo -n "🔑 Логинимся как $MASTER_NAME... "

CSRF=$(get_csrf "http://localhost:8080/login")

MASTER_LOGIN=$(curl -s -b $COOKIE_JAR -c $COOKIE_JAR -X POST "http://localhost:8080/login" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$CSRF&email=$MASTER_EMAIL&password=password" \
    -w "%{http_code}" -o /dev/null)

if [ "$MASTER_LOGIN" = "302" ]; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "\n${RED}❌ Ошибка входа (код: $MASTER_LOGIN)${NC}"
    exit 1
fi

sleep 1

# 7. Запускаем 5 параллельных запросов
echo ""
echo "🚀 ЗАПУСК 5 ПАРАЛЛЕЛЬНЫХ ЗАПРОСОВ:"
echo "======================"

> $RESULTS_FILE

for i in {1..5}; do
    {
        CSRF=$(get_csrf "http://localhost:8080/master/requests")

        if [ -z "$CSRF" ]; then
            echo "$i:ERROR:NO_CSRF" >> $RESULTS_FILE
            exit
        fi

        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -b $COOKIE_JAR -c $COOKIE_JAR \
            -X POST "http://localhost:8080/master/requests/$REQUEST_ID/take" \
            -H "Content-Type: application/x-www-form-urlencoded" \
            -d "_token=$CSRF&_method=PATCH")

        echo "$i:$HTTP_CODE" >> $RESULTS_FILE
    } &

    sleep 0.2
done

wait

# Показываем результаты по HTTP кодам
echo ""
echo "📋 Результаты по HTTP кодам:"
while IFS=: read -r num code; do
    if [ "$code" = "302" ]; then
        echo -e "${GREEN}✅ Запрос $num: УСПЕХ (302)${NC}"
    else
        echo -e "${RED}❌ Запрос $num: ОТКАЗ (код: $code)${NC}"
    fi
done < $RESULTS_FILE

# 8. Проверяем финальный статус заявки (истинный источник правды)
echo ""
echo "🔍 Проверяем финальный статус заявки $REQUEST_ID..."
MASTER_HTML=$(curl -s -b $COOKIE_JAR "http://localhost:8080/master/requests")
echo "$MASTER_HTML" > "$DEBUG_DIR/master_after_race.html"
if [ $DEBUG -eq 1 ]; then
    echo -e "${YELLOW}[DEBUG] Saved master page after race to master_after_race.html${NC}" >&2
fi

# Извлекаем строку таблицы с нужным ID (достаточно много строк, чтобы захватить статус)
ROW=$(echo "$MASTER_HTML" | grep -A 15 "<td>$REQUEST_ID</td>")

if echo "$ROW" | grep -q "В работе"; then
    FINAL_STATUS="in_progress"
    echo -e "${GREEN}✅ Статус: В работе (in_progress)${NC}"
elif echo "$ROW" | grep -q "Назначена"; then
    FINAL_STATUS="assigned"
    echo -e "${YELLOW}⚠️  Статус: Назначена (assigned) - ни один запрос не изменил статус${NC}"
elif echo "$ROW" | grep -q "Выполнена"; then
    FINAL_STATUS="done"
    echo -e "${YELLOW}⚠️  Статус: Выполнена (done) - заявка уже завершена${NC}"
else
    FINAL_STATUS="not_found"
    echo -e "${RED}❌ Не удалось найти заявку $REQUEST_ID на странице мастера${NC}"
    echo "Возможно, она больше не назначена на мастера"
fi

echo ""
echo "======================"
echo "📊 РЕЗУЛЬТАТЫ ТЕСТА"
echo "======================"
echo -e "📁 Файлы отладки: $DEBUG_DIR"

# Определяем успех по финальному статусу
if [ "$FINAL_STATUS" = "in_progress" ]; then
    echo -e "${GREEN}🎉 ТЕСТ ПРОЙДЕН!${NC}"
    echo -e "   Ожидалось: 1 успех, 4 неудачи"
    echo -e "   Факт: заявка перешла в статус 'in_progress' (ровно 1 запрос должен был изменить статус)"
    EXIT_CODE=0
else
    echo -e "${RED}❌ ТЕСТ НЕ ПРОЙДЕН${NC}"
    echo -e "   Ожидалось: 1 успех, 4 неудачи"
    echo -e "   Факт: заявка осталась в статусе '$FINAL_STATUS' (защита от гонок не сработала)"
    EXIT_CODE=1
fi

rm -f $COOKIE_JAR $RESULTS_FILE

exit $EXIT_CODE
