<?php
/**
 * Тест подключения к OpenAI-совместимому API (Qwen / DashScope / OpenRouter)
 * Не требует Composer и сторонних библиотек.
 */

// ==========================================
// 1. НАСТРОЙКИ (ИЗМЕНИ ПОД СЕБЯ)
// ==========================================

// Выбери нужный вариант, закомментировав другой

// --- ВАРИАНТ А: Qwen через DashScope ---
$api_key = 'sk-ws-H.DDPPIXD.JtEh.MEUCIQDlh_sPljwMCGKP259uzx2YRQ5EttE5WC0l9-gHvJ-xvQIgWVCBeqXuXf-VPmmn6COWGrPHacyFKSybo'; 
$base_url = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions';
$model    = 'qwen-turbo-latest'; // Если ошибка, попробуй 'qwen-turbo' или 'qwen-plus'

// --- ВАРИАНТ Б: Qwen через OpenRouter ---
// $api_key = 'sk-or-v1-ТВОЙ_КЛЮЧ_OPENROUTER';
// $base_url = 'https://openrouter.ai/api/v1/chat/completions';
// $model    = 'qwen/qwen-2.5-7b-instruct:free';


// ==========================================
// 2. ТЕЛО СКРИПТА (НЕ ТРОГАЙ)
// ==========================================

// Проверка наличия cURL
if (!function_exists('curl_init')) {
    die("❌ ОШИБКА: Расширение cURL не установлено в PHP.\n");
}

echo "🚀 Начинаю тестирование API...\n";
echo "📍 URL: {$base_url}\n";
echo "🤖 Модель: {$model}\n";
echo str_repeat("-", 50) . "\n";

// Формируем JSON запроса
$payload = json_encode([
    'model'      => $model,
    'messages'   => [
        [
            'role'    => 'user', 
            'content' => 'Say exactly the word PONG and nothing else.'
        ]
    ],
    'max_tokens' => 10,
]);

// Инициализация cURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL            => $base_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
        // Для OpenRouter рекомендуется добавлять заголовки, но для теста не обязательно
        // 'HTTP-Referer: http://localhost',
        // 'X-Title: Test Script'
    ],
]);

// Выполняем запрос
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$curl_errno = curl_errno($ch);
curl_close($ch);

// ==========================================
// 3. ОБРАБОТКА РЕЗУЛЬТАТОВ
// ==========================================

// Если cURL вернул ошибку сети (таймаут, DNS, блокировка)
if ($curl_errno) {
    echo "❌ ОШИБКА СЕТИ (cURL):\n";
    echo "Код ошибки: {$curl_errno}\n";
    echo "Сообщение: {$curl_error}\n\n";
    echo "💡 Возможные причины:\n";
    echo "- Сервер API заблокирован в твоей стране/на хостинге.\n";
    echo "- Неправильно указан URL.\n";
    echo "- Проблемы с интернетом на сервере.\n";
    exit(1);
}

// Пытаемся распарсить ответ как JSON
$json_data = json_decode($response, true);

if ($http_code === 200 && isset($json_data['choices'][0]['message']['content'])) {
    echo "✅ УСПЕХ! Подключение работает!\n";
    echo "HTTP Код: {$http_code}\n";
    echo "Ответ модели: " . trim($json_data['choices'][0]['message']['content']) . "\n";
    
    if (isset($json_data['usage'])) {
        echo "Использовано токенов: " . ($json_data['usage']['total_tokens'] ?? 'N/A') . "\n";
    }
} else {
    echo "⚠️ API ОТВЕТИЛО С ОШИБКОЙ!\n";
    echo "HTTP Код: {$http_code}\n\n";
    echo "Тело ответа:\n";
    
    // Красивый вывод ошибки, если это JSON
    if ($json_data && isset($json_data['error'])) {
        echo "Тип ошибки: " . ($json_data['error']['type'] ?? 'unknown') . "\n";
        echo "Сообщение: " . ($json_data['error']['message'] ?? 'No message') . "\n";
        
        // Подсказки по частым ошибкам
        if ($http_code == 401) {
            echo "\n💡 ПОДСКАЗКА: Неверный API-ключ или он отозван. Проверь ключ в консоли провайдера.\n";
        } elseif ($http_code == 404 || strpos($json_data['error']['message'], 'not found') !== false) {
            echo "\n💡 ПОДСКАЗКА: Модель '{$model}' не найдена. Попробуй изменить название (например, 'qwen-turbo').\n";
        } elseif ($http_code == 429) {
            echo "\n💡 ПОДСКАЗКА: Превышен лимит запросов или закончились средства на балансе.\n";
        }
    } else {
        // Если ответ не JSON (например, HTML страница ошибки Cloudflare)
        echo substr($response, 0, 500) . "\n";
        echo "\n💡 ПОДСКАЗКА: Ответ не является JSON. Возможно, запрос блокируется фаерволом/WAF.\n";
    }
}

echo str_repeat("-", 50) . "\n";
echo "⚠️ ВНИМАНИЕ: Не забудь удалить этот файл после проверки!\n";
