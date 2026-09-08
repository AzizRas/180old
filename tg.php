<?php
/* ============================================================
   Telegram-бот: вход без SMS и уведомления.

   Как это работает:
     1. На сайте человек жмёт «Войти через Telegram».
     2. Сайт создаёт одноразовый код и открывает t.me/ВАШ_БОТ?start=КОД
     3. Бот показывает одну кнопку «Поделиться номером».
     4. Человек нажимает — Telegram сам передаёт боту его номер.
     5. Бот привязывает номер к коду, сайт это видит и впускает.

   Пароля нет, SMS не тратится, номер подтверждён самим Telegram.

   НАСТРОЙКА (один раз):
     1. Создать бота у @BotFather, получить токен.
     2. Вписать токен и username бота в config.php → tg.
     3. Открыть в браузере:
        https://180kun.online/tg.php?setup=ВАШ_CRON_KEY
        Эта страница сама зарегистрирует вебхук и покажет результат.
   ============================================================ */
declare(strict_types=1);
require __DIR__ . '/lib/util.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/content.php';
require __DIR__ . '/lib/copy.php';
require __DIR__ . '/lib/ai.php';
require __DIR__ . '/lib/plan.php';
require __DIR__ . '/lib/planner.php';
require __DIR__ . '/lib/engine.php';

date_default_timezone_set(cfg('timezone', 'UTC'));

/* ---------- вспомогательное ---------- */
function tg_token(): string { return (string) cfg('tg.token', ''); }
function tg_api(string $method, array $data = []): ?array {
    if (tg_token() === '' || !function_exists('curl_init')) return null;
    $ch = curl_init('https://api.telegram.org/bot' . tg_token() . '/' . $method);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 8,
    ]);
    $r = curl_exec($ch); curl_close($ch);
    $d = json_decode((string) $r, true);
    return is_array($d) ? $d : null;
}
function tg_send(string $chatId, string $text, array $markup = null): void {
    $p = ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML',
          'disable_web_page_preview' => true];
    if ($markup) $p['reply_markup'] = $markup;
    tg_api('sendMessage', $p);
}

/* ============================================================
   Установка вебхука: /tg.php?setup=CRON_KEY
   ============================================================ */
if (isset($_GET['setup'])) {
    header('Content-Type: text/html; charset=utf-8');
    if ((string) cfg('cron_key', '') === '' || $_GET['setup'] !== cfg('cron_key')) { http_response_code(403); exit('forbidden'); }
    $url = rtrim((string) cfg('site_url', ''), '/') . '/tg.php';
    $secret = substr(hash_hmac('sha256', 'tgwh', appSecret()), 0, 32);
    echo '<meta charset="utf-8"><body style="background:#0B0E14;color:#E9EEF8;font:14px/1.6 system-ui;padding:24px">';
    echo '<h2>Telegram-бот · настройка</h2>';
    if (tg_token() === '') { echo '<p style="color:#FF4D5E">Не задан tg.token в config.php</p>'; exit; }
    $me = tg_api('getMe');
    if (!($me['ok'] ?? false)) { echo '<p style="color:#FF4D5E">Токен не принят Telegram. Проверьте tg.token.</p>'; exit; }
    echo '<p>Бот: <b>@' . htmlspecialchars($me['result']['username']) . '</b></p>';
    $r = tg_api('setWebhook', ['url' => $url, 'secret_token' => $secret,
                               'allowed_updates' => ['message'], 'drop_pending_updates' => true]);
    echo '<p>Вебхук: ' . (($r['ok'] ?? false)
        ? '<b style="color:#28C486">установлен</b> → ' . htmlspecialchars($url)
        : '<b style="color:#FF4D5E">ошибка</b> ' . htmlspecialchars(json_encode($r, JSON_UNESCAPED_UNICODE))) . '</p>';
    $info = tg_api('getWebhookInfo');
    echo '<pre style="background:#151C2C;padding:12px;border-radius:10px;overflow:auto">'
       . htmlspecialchars(json_encode($info['result'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    if (($me['result']['username'] ?? '') !== trim((string) cfg('tg.username', ''), '@')) {
        echo '<p style="color:#FFB020">В config.php → tg.username впишите: <b>'
           . htmlspecialchars($me['result']['username']) . '</b></p>';
    }
    echo '<p style="color:#8A97B4">Когда всё зелёное — удалите этот параметр из адресной строки. Страница закрыта ключом cron.</p>';
    exit;
}

/* ============================================================
   Вебхук
   ============================================================ */
$secret = substr(hash_hmac('sha256', 'tgwh', appSecret()), 0, 32);
$got = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
if (!hash_equals($secret, $got)) { http_response_code(403); exit('forbidden'); }

$upd = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($upd)) { http_response_code(200); exit('ok'); }

$msg  = $upd['message'] ?? null;
if (!$msg) { echo 'ok'; exit; }
$chat = (string) ($msg['chat']['id'] ?? '');
$text = trim((string) ($msg['text'] ?? ''));
$lang = (($msg['from']['language_code'] ?? '') === 'uz') ? 'uz' : 'ru';
$name = trim(($msg['from']['first_name'] ?? '') . ' ' . ($msg['from']['last_name'] ?? ''));
$tgId = (string) ($msg['from']['id'] ?? '');
if ($chat === '' || $tgId === '') { echo 'ok'; exit; }

$bot  = trim((string) cfg('tg.username', ''), '@');
$site = rtrim((string) cfg('site_url', ''), '/');

/* ============================================================
   ВХОД ЗА ОДНО НАЖАТИЕ

   Раньше человеку надо было ещё поделиться номером. Это лишний шаг:
   Telegram и так подтверждает, кто он, — id пользователя приходит внутри
   запроса, а сам запрос подписан секретным токеном вебхука.

   Теперь: нажал «Запустить» — уже зарегистрирован и авторизован.
   Номер можно привязать потом, кнопкой, и только если он нужен.
   ============================================================ */

/* Найти или создать человека по его Telegram-id */
function tg_user(string $tgId, string $name, string $lang): array {
    $u = one('SELECT * FROM users WHERE tg_id=?', [$tgId]);
    if ($u) return $u;

    $trial = (int) cfg('trial_days', 7);
    q('INSERT INTO users(email,phone,tg_id,pass_hash,name,lang,role,access_until,created_at)
       VALUES(?,?,?,?,?,?,?,?,?)',
      ['tg' . $tgId . '@180kun.local', null, $tgId, '',
       ($name !== '' ? mb_substr($name, 0, 40) : 'Участник'),
       $lang, 'user', $trial > 0 ? dateAdd(today(), $trial) : null, nowIso()]);
    $uid = lastId();
    q('INSERT OR IGNORE INTO hero(user_id) VALUES(?)', [$uid]);
    q('INSERT OR IGNORE INTO onboarding(user_id,updated_at) VALUES(?,?)', [$uid, nowIso()]);
    q('INSERT OR IGNORE INTO world_user(user_id,tiles,updated_at) VALUES(?,?,?)', [$uid, '{}', nowIso()]);
    return one('SELECT * FROM users WHERE id=?', [$uid]);
}

/* Одноразовая ссылка «вернуться на сайт уже внутри» */
function tg_login_link(int $uid, string $chat, string $site): string {
    $n = bin2hex(random_bytes(16));
    q("INSERT INTO tg_login(nonce,user_id,chat_id,state,created_at) VALUES(?,?,?,'ok',?)",
      [$n, $uid, $chat, nowIso()]);
    return $site . '/index.php?tg=' . $n;
}

/* Кнопка возврата на сайт под сообщением */
function tg_back_button(string $url, string $lang): array {
    return ['inline_keyboard' => [[
        ['text' => $lang === 'uz' ? "🚀 180 kun ni ochish" : '🚀 Открыть 180 kun', 'url' => $url],
    ]]];
}

/* --- Человек прислал контакт: привязываем номер --------------------- */
if (!empty($msg['contact']['phone_number'])) {
    // Принимаем только СВОЙ контакт, а не пересланный чужой
    if ((string) ($msg['contact']['user_id'] ?? '') !== (string) ($msg['from']['id'] ?? '')) {
        tg_send($chat, $lang === 'uz'
            ? "Iltimos, o'z raqamingizni yuboring — pastdagi tugma orqali."
            : 'Пришлите, пожалуйста, свой номер — кнопкой ниже.');
        echo 'ok'; exit;
    }
    $phone = normPhone((string) $msg['contact']['phone_number']);
    if ($phone === '') { tg_send($chat, 'Не удалось разобрать номер.'); echo 'ok'; exit; }

    $u   = tg_user($tgId, $name, $lang);
    $old = one('SELECT * FROM users WHERE phone=? AND id<>?', [$phone, $u['id']]);

    if ($old) {
        // Человек уже заходил по SMS. Склеиваем: Telegram переезжает
        // на старый аккаунт, пустой новый удаляем. Прогресс сохраняется.
        $fresh = !get_quest((int) $u['id']);
        if ($fresh) {
            q('UPDATE users SET tg_id=NULL WHERE id=?', [$u['id']]);
            q('UPDATE users SET tg_id=? WHERE id=?', [$tgId, $old['id']]);
            q('DELETE FROM users WHERE id=?', [$u['id']]);
            $u = one('SELECT * FROM users WHERE id=?', [$old['id']]);
        } else {
            tg_send($chat, $lang === 'uz'
                ? "Bu raqam boshqa hisobga biriktirilgan. Yordam: " . (string) cfg('donate.contact', '')
                : 'Этот номер уже привязан к другому аккаунту. Напишите нам: ' . (string) cfg('donate.contact', ''));
            echo 'ok'; exit;
        }
    } else {
        q('UPDATE users SET phone=? WHERE id=?', [$phone, $u['id']]);
        if (normPhone((string) cfg('admin_phone', '')) === $phone && $u['role'] === 'user') {
            q("UPDATE users SET role='admin' WHERE id=?", [$u['id']]);
        }
    }

    factSet((int) $u['id'], 'tg_chat', $chat, 'system');
    $link = tg_login_link((int) $u['id'], $chat, $site);
    tg_send($chat, $lang === 'uz'
        ? "Raqam biriktirildi. Ilovaga qaytishingiz mumkin."
        : 'Номер привязан. Можно возвращаться в приложение.',
        ['remove_keyboard' => true]);
    tg_send($chat, $lang === 'uz' ? "Ilovaga o'tish:" : 'Перейти в приложение:',
            tg_back_button($link, $lang));
    echo 'ok'; exit;
}

/* --- /start [код] — вход за одно нажатие ---------------------------- */
if (str_starts_with($text, '/start')) {
    $existed = (bool) one('SELECT id FROM users WHERE tg_id=?', [$tgId]);
    $u = tg_user($tgId, $name, $lang);
    factSet((int) $u['id'], 'tg_chat', $chat, 'system');

    // Если пришёл по ссылке с сайта — открываем ту вкладку, что ждёт
    $nonce = trim(substr($text, 6));
    if ($nonce !== '' && preg_match('/^[a-f0-9]{16,64}$/', $nonce)) {
        q("UPDATE tg_login SET user_id=?, chat_id=?, state='ok' WHERE nonce=? AND state='wait'",
          [$u['id'], $chat, $nonce]);
    }

    // И всегда даём свою ссылку: человек мог прийти прямо в бот,
    // или зайти с телефона, где вкладка сайта уже закрыта.
    $link = tg_login_link((int) $u['id'], $chat, $site);
    $c    = constitution($lang);

    tg_send($chat,
        "<b>180 kun</b>\n" . $c['lead'] . "\n\n" .
        ($lang === 'uz'
            ? "Tayyor — siz ro'yxatdan o'tdingiz. Pastdagi tugmani bosing va yo'lingiz boshlanadi."
            : "Готово — вы уже зарегистрированы. Нажмите кнопку ниже, и путь начнётся."),
        tg_back_button($link, $lang));

    // Тем, кто раньше заходил по SMS, даём необязательную кнопку —
    // чтобы подтянуть старый аккаунт с прогрессом, а не начинать заново.
    if (!$existed && !$u['phone']) {
        tg_send($chat, $lang === 'uz'
            ? "Ilgari SMS orqali kirgan bo'lsangiz — raqamingizni yuboring, eski hisobingiz va butun progressingiz qaytariladi. Aks holda bu xabarni e'tiborsiz qoldiring."
            : 'Если раньше вы заходили по SMS — пришлите номер, и мы вернём ваш старый аккаунт со всем прогрессом. Если нет — просто не обращайте внимания.',
            ['keyboard' => [[['text' => $lang === 'uz' ? "📱 Raqamni yuborish" : '📱 Поделиться номером',
                              'request_contact' => true]]],
             'resize_keyboard' => true, 'one_time_keyboard' => true]);
    }
    echo 'ok'; exit;
}

/* --- Привязать номер (по желанию) ----------------------------------- */
if ($text === '/nomer' || $text === '/phone') {
    tg_send($chat, $lang === 'uz'
        ? "Raqamni biriktirish uchun pastdagi tugmani bosing. Bu SMS orqali kirgan eski hisobingizni ulash uchun kerak."
        : 'Нажмите кнопку ниже, чтобы привязать номер. Это нужно, если раньше вы заходили по SMS — подтянем ваш старый аккаунт.',
        ['keyboard' => [[['text' => $lang === 'uz' ? "📱 Raqamni yuborish" : '📱 Поделиться номером',
                          'request_contact' => true]]],
         'resize_keyboard' => true, 'one_time_keyboard' => true]);
    echo 'ok'; exit;
}

/* --- всё остальное --------------------------------------------------- */
$known = one('SELECT id FROM users WHERE tg_id=?', [$tgId]);
if ($known) {
    $link = tg_login_link((int) $known['id'], $chat, $site);
    tg_send($chat, ($lang === 'uz' ? "Ilovaga o'tish:" : 'Перейти в приложение:'),
            tg_back_button($link, $lang));
} else {
    tg_send($chat, ($lang === 'uz' ? "Boshlash uchun /start bosing." : 'Нажмите /start, чтобы начать.'));
}
echo 'ok';
