<?php
/* Общее: конфиг, сессии, доступ, лимиты, телефоны, загрузки */

/* Настройки: config.php + необязательный config.local.php поверх.
   Свои ключи и пароли держите в config.local.php — тогда при
   обновлении можно спокойно перезаписывать config.php, и ничего
   не потеряется. */
function cfg(string $path = null, $default = null) {
    static $c = null;
    if ($c === null) {
        $c = require __DIR__ . '/../config.php';
        $local = __DIR__ . '/../config.local.php';
        if (is_file($local)) {
            $l = require $local;
            if (is_array($l)) $c = cfg_merge($c, $l);
        }
    }
    if ($path === null) return $c;
    $cur = $c;
    foreach (explode('.', $path) as $k) {
        if (!is_array($cur) || !array_key_exists($k, $cur)) return $default;
        $cur = $cur[$k];
    }
    return $cur;
}

/* Глубокое слияние: значения из config.local.php перекрывают базовые */
function cfg_merge(array $base, array $over): array {
    foreach ($over as $k => $v) {
        if (is_array($v) && isset($base[$k]) && is_array($base[$k]) && !array_is_list($v)) {
            $base[$k] = cfg_merge($base[$k], $v);
        } else {
            $base[$k] = $v;
        }
    }
    return $base;
}

function tz(): DateTimeZone { static $z = null; return $z ?: ($z = new DateTimeZone(cfg('timezone', 'UTC'))); }
function today(): string  { return (new DateTime('now', tz()))->format('Y-m-d'); }
function nowIso(): string { return (new DateTime('now', tz()))->format('Y-m-d H:i:s'); }
function dateAdd(string $d, int $days): string {
    $dt = new DateTime($d); $dt->modify(($days >= 0 ? '+' : '-') . abs($days) . ' days');
    return $dt->format('Y-m-d');
}
function daysBetween(string $a, string $b): int {
    return (int) ((new DateTime(substr($a, 0, 10)))->diff(new DateTime(substr($b, 0, 10)))->format('%r%a'));
}
function minutesUntil(string $iso): int {
    return (int) round(((new DateTime($iso, tz()))->getTimestamp() - (new DateTime('now', tz()))->getTimestamp()) / 60);
}

/* --- ответы --- */
function jsonOut($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function fail(string $msg, int $code = 400, array $extra = []): void { jsonOut(['ok' => false, 'error' => $msg] + $extra, $code); }
function ok(array $data = []): void { jsonOut(['ok' => true] + $data); }

/* --- сессии --- */
function appSecret(): string {
    static $s = null;
    if ($s !== null) return $s;
    $c = (string) cfg('secret', '');
    if ($c !== '' && strpos($c, 'CHANGE_ME') === false) return $s = $c;
    $f = __DIR__ . '/../data/secret.key';
    if (is_file($f)) { $s = trim((string) file_get_contents($f)); if ($s !== '') return $s; }
    $s = bin2hex(random_bytes(32));
    @file_put_contents($f, $s); @chmod($f, 0600);
    return $s;
}
function sessSign(string $p): string { return hash_hmac('sha256', $p, appSecret()); }

function loginUser(int $id): void {
    $payload = $id . '.' . time();
    $token = $payload . '.' . sessSign($payload);
    setcookie('l180', $token, [
        'expires' => time() + 60 * 60 * 24 * 120, 'path' => '/',
        'httponly' => true, 'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    $_COOKIE['l180'] = $token;
}
function logoutUser(): void {
    setcookie('l180', '', ['expires' => time() - 3600, 'path' => '/']);
    unset($_COOKIE['l180']);
}
function currentUser(): ?array {
    static $cached = false, $u = null;
    if ($cached) return $u;
    $cached = true;
    $t = $_COOKIE['l180'] ?? '';
    if (!$t || substr_count($t, '.') !== 2) return null;
    [$id, $ts, $sig] = explode('.', $t);
    if (!hash_equals(sessSign($id . '.' . $ts), $sig)) return null;
    $u = one("SELECT * FROM users WHERE id=? AND status!='banned'", [(int) $id]);
    if ($u) {
        $prev = (string) ($u['last_seen'] ?? '');
        if (substr($prev, 0, 16) !== substr(nowIso(), 0, 16)) {   // пишем не чаще раза в минуту
            q('UPDATE users SET last_seen=? WHERE id=?', [nowIso(), $u['id']]);
        }
    }
    return $u;
}
function requireUser(): array { $u = currentUser(); if (!$u) fail('auth', 401); return $u; }
function requireAdmin(): array {
    $u = requireUser();
    if (!in_array($u['role'], ['admin', 'moder'], true)) fail('forbidden', 403);
    return $u;
}
function isAdmin(?array $u): bool { return $u && $u['role'] === 'admin'; }
function isStaff(?array $u): bool { return $u && in_array($u['role'], ['admin', 'moder'], true); }

/* --- доступ --- */
function hasAccess(array $u): bool {
    if (in_array($u['role'] ?? '', ['admin', 'moder'], true)) return true;
    return !empty($u['access_until']) && $u['access_until'] >= today();
}
function grantAccess(int $userId, int $days): string {
    $u = one('SELECT access_until FROM users WHERE id=?', [$userId]);
    $base = (!empty($u['access_until']) && $u['access_until'] > today()) ? $u['access_until'] : today();
    $until = dateAdd($base, $days);
    q('UPDATE users SET access_until=? WHERE id=?', [$until, $userId]);
    return $until;
}

/* --- лимиты --- */
/** true = можно, false = превышено */
function rateOk(string $bucket, int $limit, int $windowSec): bool {
    $now = time();
    $r = one('SELECT hits, reset_at FROM rate WHERE bucket=?', [$bucket]);
    if (!$r || (int) $r['reset_at'] < $now) {
        q('INSERT INTO rate(bucket,hits,reset_at) VALUES(?,1,?)
           ON CONFLICT(bucket) DO UPDATE SET hits=1, reset_at=excluded.reset_at',
           [$bucket, $now + $windowSec]);
        return true;
    }
    if ((int) $r['hits'] >= $limit) return false;
    q('UPDATE rate SET hits=hits+1 WHERE bucket=?', [$bucket]);
    return true;
}
function clientIp(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return substr(preg_replace('/[^0-9a-fA-F.:]/', '', $ip), 0, 45);
}

/* --- телефоны Узбекистана --- */
/** Приводит к виду 998XXXXXXXXX. Возвращает '' если номер невалидный. */
function normPhone(string $raw): string {
    $d = preg_replace('/\D+/', '', $raw);
    if ($d === '') return '';
    if (strlen($d) === 9)  $d = '998' . $d;                 // 901234567
    if (strlen($d) === 12 && strpos($d, '998') === 0) return $d;
    if (strlen($d) >= 10 && strlen($d) <= 15) return $d;    // иностранный номер — пропускаем
    return '';
}
function prettyPhone(string $p): string {
    if (strlen($p) === 12 && strpos($p, '998') === 0) {
        return '+998 ' . substr($p, 3, 2) . ' ' . substr($p, 5, 3) . ' ' . substr($p, 8, 2) . ' ' . substr($p, 10, 2);
    }
    return '+' . $p;
}
function maskPhone(string $p): string {
    if (strlen($p) < 6) return '•••';
    return '+' . substr($p, 0, 5) . str_repeat('•', max(0, strlen($p) - 7)) . substr($p, -2);
}

/* --- прочее --- */
function body(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw ?: '[]', true);
    return is_array($d) ? $d : [];
}
function s($v, int $max = 4000): string {
    $v = is_string($v) ? $v : '';
    $v = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v));
    return mb_substr($v, 0, $max);
}
function randCode(int $n = 6): string {
    $a = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; $o = '';
    for ($i = 0; $i < $n; $i++) $o .= $a[random_int(0, strlen($a) - 1)];
    return $o;
}
function clampInt($v, int $min, int $max, int $def): int {
    if (!is_numeric($v)) return $def;
    return max($min, min($max, (int) $v));
}
function plural(int $n, string $one, string $few, string $many): string {
    $n = abs($n) % 100; $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $many;
    if ($n1 > 1 && $n1 < 5) return $few;
    if ($n1 === 1) return $one;
    return $many;
}

/* --- факты о человеке (прогрессивный профиль) --- */
function factSet(int $uid, string $key, string $val, string $src = 'user'): void {
    q('INSERT INTO facts(user_id,key,value,source,updated_at) VALUES(?,?,?,?,?)
       ON CONFLICT(user_id,key) DO UPDATE SET value=excluded.value, source=excluded.source, updated_at=excluded.updated_at',
       [$uid, s($key, 40), s($val, 600), $src, nowIso()]);
}
function factGet(int $uid, string $key, string $def = ''): string {
    $r = one('SELECT value FROM facts WHERE user_id=? AND key=?', [$uid, $key]);
    return $r ? (string) $r['value'] : $def;
}
function factsAll(int $uid): array {
    $o = [];
    foreach (all('SELECT key,value FROM facts WHERE user_id=?', [$uid]) as $r) $o[$r['key']] = $r['value'];
    return $o;
}

/* --- загрузка изображений --- */
/** Возвращает имя файла или бросает исключение. Пересохраняет через GD — это убивает
    любой встроенный в картинку код и лишние метаданные. */
function saveImage(array $file, string $dir, int $maxSide = 1400): string {
    if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) throw new RuntimeException('upload_error');
    $maxBytes = (int) cfg('uploads.max_bytes', 6 * 1024 * 1024);
    if (($file['size'] ?? 0) > $maxBytes) throw new RuntimeException('too_big');

    $tmp = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmp) && !is_file($tmp)) throw new RuntimeException('no_file');

    $info = @getimagesize($tmp);
    if (!$info) throw new RuntimeException('not_image');
    [$w, $h, $type] = $info;
    if ($w < 40 || $h < 40 || $w > 12000 || $h > 12000) throw new RuntimeException('bad_size');

    if (!function_exists('imagecreatefromjpeg')) {
        // GD нет — сохраняем как есть, но только разрешённые типы
        $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$type] ?? null;
        if (!$ext) throw new RuntimeException('bad_type');
        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        if (!@move_uploaded_file($tmp, $dir . '/' . $name) && !@copy($tmp, $dir . '/' . $name))
            throw new RuntimeException('save_failed');
        return $name;
    }

    $src = match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($tmp),
        IMAGETYPE_PNG  => @imagecreatefrompng($tmp),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : false,
        default        => false,
    };
    if (!$src) throw new RuntimeException('bad_type');

    $scale = min(1, $maxSide / max($w, $h));
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));
    $dst = imagecreatetruecolor($nw, $nh);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($src);

    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $name = bin2hex(random_bytes(12)) . '.jpg';
    $okSave = imagejpeg($dst, $dir . '/' . $name, 82);
    imagedestroy($dst);
    if (!$okSave) throw new RuntimeException('save_failed');
    return $name;
}
