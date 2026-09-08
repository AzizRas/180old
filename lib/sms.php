<?php
/* ============================================================
   Отправка SMS с кодом подтверждения.
   Драйверы: log (по умолчанию) | eskiz | playmobile | telegram
   Драйвер 'log' пишет код в data/sms.log и НЕ требует договора —
   на нём можно запускаться и тестировать хоть сегодня.
   ============================================================ */

function sms_driver(): string { return (string) cfg('sms.driver', 'log'); }

/** @return array{ok:bool, via:string, error:string} */
function sms_send(string $phone, string $text): array {
    $d = sms_driver();
    try {
        $r = match ($d) {
            'eskiz'      => sms_eskiz($phone, $text),
            'playmobile' => sms_playmobile($phone, $text),
            'telegram'   => sms_telegram($phone, $text),
            default      => sms_log($phone, $text),
        };
        return ['ok' => $r, 'via' => $d, 'error' => $r ? '' : 'send_failed'];
    } catch (Throwable $e) {
        error_log('[180kun SMS] ' . $e->getMessage());
        // Резервный путь: если боевой провайдер упал, код всё равно попадёт в лог,
        // и админ сможет продиктовать его человеку вручную.
        sms_log($phone, $text);
        return ['ok' => false, 'via' => $d, 'error' => $e->getMessage()];
    }
}

/* ---------- log: код пишется в файл ---------- */
function sms_log(string $phone, string $text): bool {
    $f = __DIR__ . '/../data/sms.log';
    $line = '[' . nowIso() . '] ' . $phone . ' :: ' . str_replace("\n", ' ', $text) . "\n";
    @file_put_contents($f, $line, FILE_APPEND | LOCK_EX);
    @chmod($f, 0600);
    return true;
}

/* ---------- Eskiz.uz ---------- */
function sms_eskiz(string $phone, string $text): bool {
    $token = eskiz_token();
    if (!$token) throw new RuntimeException('eskiz_no_token');
    $r = sms_http(
        rtrim((string) cfg('sms.eskiz.base', 'https://notify.eskiz.uz/api'), '/') . '/message/sms/send',
        ['Authorization: Bearer ' . $token],
        [
            'mobile_phone' => $phone,
            'message'      => $text,
            'from'         => (string) cfg('sms.eskiz.from', '4546'),
        ]
    );
    if ($r === null) throw new RuntimeException('eskiz_http');
    $st = strtolower((string) ($r['status'] ?? ''));
    if ($st === 'waiting' || $st === 'success' || isset($r['id'])) return true;
    throw new RuntimeException('eskiz: ' . json_encode($r, JSON_UNESCAPED_UNICODE));
}

function eskiz_token(): ?string {
    $f = __DIR__ . '/../data/eskiz.token';
    if (is_file($f)) {
        $d = json_decode((string) file_get_contents($f), true);
        if (is_array($d) && !empty($d['token']) && ($d['exp'] ?? 0) > time()) return $d['token'];
    }
    $r = sms_http(
        rtrim((string) cfg('sms.eskiz.base', 'https://notify.eskiz.uz/api'), '/') . '/auth/login',
        [],
        ['email' => (string) cfg('sms.eskiz.email'), 'password' => (string) cfg('sms.eskiz.password')]
    );
    $tok = $r['data']['token'] ?? null;
    if (!$tok) return null;
    @file_put_contents($f, json_encode(['token' => $tok, 'exp' => time() + 25 * 24 * 3600]));
    @chmod($f, 0600);
    return $tok;
}

/* ---------- PlayMobile (smsxabar) ---------- */
function sms_playmobile(string $phone, string $text): bool {
    $payload = ['messages' => [[
        'recipient' => $phone,
        'message-id' => 'k' . bin2hex(random_bytes(6)),
        'sms' => ['originator' => (string) cfg('sms.playmobile.from', '3700'),
                  'content' => ['text' => $text]],
    ]]];
    $auth = base64_encode(cfg('sms.playmobile.login') . ':' . cfg('sms.playmobile.password'));
    $r = sms_http(
        (string) cfg('sms.playmobile.base', 'https://send.smsxabar.uz/broker-api/send'),
        ['Authorization: Basic ' . $auth],
        $payload, true
    );
    return $r !== null;
}

/* ---------- Telegram: код приходит в бот ---------- */
/* Работает, если человек ранее написал боту и мы знаем его chat_id
   (сохраняется в facts как tg_chat). Иначе — исключение и падение в лог. */
function sms_telegram(string $phone, string $text): bool {
    $u = one('SELECT id FROM users WHERE phone=?', [$phone]);
    $chat = $u ? factGet((int) $u['id'], 'tg_chat') : '';
    if ($chat === '') throw new RuntimeException('tg_no_chat');
    $r = sms_http('https://api.telegram.org/bot' . cfg('sms.telegram.token') . '/sendMessage',
        [], ['chat_id' => $chat, 'text' => $text], true);
    return !empty($r['ok']);
}

/* ---------- HTTP ---------- */
function sms_http(string $url, array $headers, array $data, bool $json = false): ?array {
    if (!function_exists('curl_init')) throw new RuntimeException('no_curl');
    $ch = curl_init($url);
    $h = $headers;
    $h[] = $json ? 'Content-Type: application/json' : 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $h,
        CURLOPT_POSTFIELDS => $json ? json_encode($data, JSON_UNESCAPED_UNICODE) : http_build_query($data),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 8,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($res === false) return null;
    $d = json_decode((string) $res, true);
    if ($code >= 300) {
        error_log('[180kun SMS] http ' . $code . ' ' . substr((string) $res, 0, 300));
        if (!is_array($d)) return null;
    }
    return is_array($d) ? $d : ['raw' => $res];
}

/* ============================================================
   OTP
   ============================================================ */

function otp_text(string $code, string $lang = 'ru'): string {
    return $lang === 'uz'
        ? "180 kun: tasdiqlash kodi {$code}. Hech kimga aytmang."
        : "180 kun: код подтверждения {$code}. Никому его не сообщайте.";
}

/** Создаёт и отправляет код. Возвращает ['ok'=>bool,'wait'=>сек,'via'=>..,'debug'=>код|null] */
function otp_create(string $phone, string $lang = 'ru', string $purpose = 'login'): array {
    $ip = clientIp();

    // Лимиты: на номер и на IP — защита от накрутки SMS и от перебора
    if (!rateOk('otp:p:' . $phone, (int) cfg('otp.per_phone_hour', 5), 3600))
        return ['ok' => false, 'error' => 'too_many_phone'];
    if (!rateOk('otp:i:' . $ip, (int) cfg('otp.per_ip_hour', 15), 3600))
        return ['ok' => false, 'error' => 'too_many_ip'];

    // Не чаще раза в N секунд
    $last = one('SELECT created_at FROM otp WHERE phone=? ORDER BY id DESC LIMIT 1', [$phone]);
    $cool = (int) cfg('otp.resend_sec', 60);
    if ($last) {
        $passed = time() - strtotime($last['created_at']);
        if ($passed < $cool) return ['ok' => false, 'error' => 'cooldown', 'wait' => $cool - $passed];
    }

    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $ttl  = (int) cfg('otp.ttl_sec', 300);

    q("UPDATE otp SET used_at=? WHERE phone=? AND used_at IS NULL", [nowIso(), $phone]); // старые гасим
    q('INSERT INTO otp(phone,code_hash,purpose,ip,expires_at,created_at) VALUES(?,?,?,?,?,?)',
      [$phone, password_hash($code, PASSWORD_DEFAULT), $purpose, $ip,
       date('Y-m-d H:i:s', time() + $ttl), nowIso()]);

    $res = sms_send($phone, otp_text($code, $lang));
    q('UPDATE otp SET sent_via=? WHERE id=?', [$res['via'], lastId()]);

    return [
        'ok'    => true,
        'wait'  => $cool,
        'ttl'   => $ttl,
        'via'   => $res['via'],
        'sent'  => $res['ok'],
        // код возвращается в ответ ТОЛЬКО в режиме log и только если это явно разрешено
        'debug' => (sms_driver() === 'log' && cfg('otp.show_code_in_dev', true)) ? $code : null,
    ];
}

/** Проверяет код. Возвращает true/false. */
function otp_check(string $phone, string $code): bool {
    $code = preg_replace('/\D+/', '', $code);
    if (strlen($code) !== 6) return false;
    if (!rateOk('otpchk:' . $phone, (int) cfg('otp.check_per_hour', 20), 3600)) return false;

    $row = one("SELECT * FROM otp WHERE phone=? AND used_at IS NULL ORDER BY id DESC LIMIT 1", [$phone]);
    if (!$row) return false;
    if (strtotime($row['expires_at']) < time()) return false;
    if ((int) $row['attempts'] >= (int) cfg('otp.max_attempts', 5)) return false;

    q('UPDATE otp SET attempts=attempts+1 WHERE id=?', [$row['id']]);
    if (!password_verify($code, $row['code_hash'])) return false;

    q('UPDATE otp SET used_at=? WHERE id=?', [nowIso(), $row['id']]);
    return true;
}
