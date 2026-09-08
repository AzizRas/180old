<?php
/* ============================================================
   Социальный слой: публичные профили, лента, поддержка, жалобы.
   Ключевое правило приватности: наружу НИКОГДА не уходит текст цели,
   только направление (Тело / Дело / Разум / Душа / Дисциплина).
   ============================================================ */

/* Краткая карточка человека — то, что видят другие */
function user_card(int $uid, string $lang = 'ru', ?array $viewer = null): ?array {
    $u = one('SELECT id,name,avatar,badge,badge_note,role,city,bio,created_at,last_seen,status FROM users WHERE id=?', [$uid]);
    if (!$u) return null;
    if ($u['status'] === 'banned' && !isStaff($viewer)) return null;

    $qq = get_quest($uid);
    $h  = one('SELECT * FROM hero WHERE user_id=?', [$uid]);
    $sq = squad_of($uid);
    $dir = $qq ? direction_of($qq['category'], $lang) : ['label' => '—', 'icon' => '•'];

    $day = $qq ? current_day($qq) : 0;
    $pct = 0;
    if ($qq) {
        $r = one('SELECT AVG(completion) a FROM day_stats WHERE quest_id=? AND day_no>=?',
                 [$qq['id'], max(1, $day - 6)]);
        $pct = (int) round(((float) ($r['a'] ?? 0)) * 100);
    }

    return [
        'id'        => (int) $u['id'],
        'name'      => $u['name'],
        'avatar'    => $u['avatar'] ? 'uploads/avatars/' . $u['avatar'] : '',
        'initial'   => mb_strtoupper(mb_substr($u['name'] ?: '?', 0, 1)),
        'badge'     => badge_view((string) $u['badge'], $lang),
        'badge_note'=> $u['badge_note'],
        'role'      => $u['role'],
        'city'      => $u['city'],
        'bio'       => $u['bio'],
        'direction' => $dir,
        'day'       => $day,
        'week_pct'  => $pct,
        'level'     => $h ? hero_level((int) $h['xp']) : 1,
        'xp'        => (int) ($h['xp'] ?? 0),
        'streak'    => (int) ($h['streak'] ?? 0),
        'best'      => (int) ($h['best_streak'] ?? 0),
        'comebacks' => (int) ($h['comebacks'] ?? 0),
        'squad'     => $sq ? ['id' => (int) $sq['id'], 'name' => $sq['name']] : null,
        'online'    => !empty($u['last_seen']) && (time() - strtotime($u['last_seen'])) < 600,
        'since'     => substr((string) $u['created_at'], 0, 10),
        'is_me'     => $viewer && (int) $viewer['id'] === (int) $u['id'],
        'banned'    => $u['status'] === 'banned',
    ];
}

/* Полный профиль: карточка + характеристики + достижения */
function user_profile(int $uid, string $lang, ?array $viewer): ?array {
    $c = user_card($uid, $lang, $viewer);
    if (!$c) return null;
    $h = one('SELECT * FROM hero WHERE user_id=?', [$uid]);
    $c['stats'] = [
        'health' => (int) ($h['health'] ?? 0), 'discipline' => (int) ($h['discipline'] ?? 0),
        'mind'   => (int) ($h['mind'] ?? 0),   'business'   => (int) ($h['business'] ?? 0),
        'soul'   => (int) ($h['soul'] ?? 0),
    ];
    $c['achievements'] = all('SELECT key,title,created_at FROM achievements WHERE user_id=? ORDER BY id DESC LIMIT 30', [$uid]);
    $c['photos'] = array_map(fn($p) => ['id' => (int) $p['id'], 'url' => 'uploads/photos/' . $p['file'],
                                        'day' => (int) $p['day_no'], 'caption' => $p['caption']],
        all("SELECT id,file,day_no,caption FROM photos
             WHERE user_id=? AND status='ok' AND visibility='public' ORDER BY id DESC LIMIT 12", [$uid]));
    $c['cheers'] = (int) (one('SELECT COUNT(*) c FROM cheers WHERE to_id=?', [$uid])['c'] ?? 0);
    return $c;
}

/* ---------------- ЛЕНТА ---------------- */
/* В ленту попадают только события-достижения. Ни целей, ни цифр веса. */
function feed_add(int $uid, string $type, array $payload = [], string $scope = 'public'): void {
    q('INSERT INTO feed(user_id,type,payload,scope,created_at) VALUES(?,?,?,?,?)',
      [$uid, $type, json_encode($payload, JSON_UNESCAPED_UNICODE), $scope, nowIso()]);
}

function feed_text(array $row, string $lang): string {
    $p = json_decode($row['payload'] ?: '{}', true) ?: [];
    $n = (int) ($p['n'] ?? 0);
    $ru = [
        'joined'    => 'начал свои 180 дней',
        'day'       => "прошёл день {$n}",
        'streak'    => "{$n} " . plural($n, 'день', 'дня', 'дней') . ' подряд',
        'comeback'  => 'вернулся после перерыва',
        'perfect'   => 'закрыл день полностью',
        'level'     => "поднял героя до уровня {$n}",
        'meeting'   => 'был на встрече группы',
        'photo'     => 'добавил фото в журнал',
        'squad'     => 'вошёл в группу',
        'expedition'=> 'команда закрыла экспедицию',
        'stage'     => "дошёл до этапа {$n}",
        'world_first'=> 'построил первое здание в своём мире',
        'world_ten' => 'застроил десять клеток мира',
    ];
    $uz = [
        'joined'    => "180 kunini boshladi",
        'day'       => "{$n}-kunni o'tdi",
        'streak'    => "ketma-ket {$n} kun",
        'comeback'  => 'tanaffusdan keyin qaytdi',
        'perfect'   => "kunni to'liq yopdi",
        'level'     => "qahramonni {$n}-darajaga ko'tardi",
        'meeting'   => 'jamoa uchrashuvida bo\'ldi',
        'photo'     => "kundalikka surat qo'shdi",
        'squad'     => 'jamoaga kirdi',
        'expedition'=> 'jamoa ekspeditsiyani yopdi',
        'stage'     => "{$n}-bosqichga yetdi",
        'world_first'=> "o'z dunyosida birinchi binoni qurdi",
        'world_ten' => "dunyoning o'nta katagini qurdi",
    ];
    $m = $lang === 'uz' ? $uz : $ru;
    return $m[$row['type']] ?? $row['type'];
}

function feed_list(array $viewer, int $limit = 40, int $before = 0): array {
    $lang = $viewer['lang'];
    $args = [];
    $where = "f.scope='public'";
    if ($before > 0) { $where .= ' AND f.id < ?'; $args[] = $before; }
    $args[] = $limit;

    $rows = all("SELECT f.*, u.name, u.avatar, u.badge, u.status
                 FROM feed f JOIN users u ON u.id=f.user_id
                 WHERE {$where} AND u.status='active'
                 ORDER BY f.id DESC LIMIT ?", $args);

    $mine = [];
    foreach (all('SELECT feed_id FROM cheers WHERE from_id=?', [$viewer['id']]) as $c) $mine[(int) $c['feed_id']] = 1;

    $out = [];
    foreach ($rows as $r) {
        $cn = (int) (one('SELECT COUNT(*) c FROM cheers WHERE feed_id=?', [$r['id']])['c'] ?? 0);
        $out[] = [
            'id'      => (int) $r['id'],
            'user_id' => (int) $r['user_id'],
            'name'    => $r['name'],
            'avatar'  => $r['avatar'] ? 'uploads/avatars/' . $r['avatar'] : '',
            'initial' => mb_strtoupper(mb_substr($r['name'] ?: '?', 0, 1)),
            'badge'   => badge_view((string) $r['badge'], $lang),
            'text'    => feed_text($r, $lang),
            'type'    => $r['type'],
            'ago'     => ago($r['created_at'], $lang),
            'cheers'  => $cn,
            'cheered' => isset($mine[(int) $r['id']]),
        ];
    }
    return $out;
}

function ago(string $iso, string $lang = 'ru'): string {
    $sec = max(0, time() - strtotime($iso));
    $uz = $lang === 'uz';
    if ($sec < 90)    return $uz ? 'hozir' : 'только что';
    $m = (int) round($sec / 60);
    if ($m < 60)      return $uz ? "{$m} daq" : "{$m} мин";
    $h = (int) round($m / 60);
    if ($h < 24)      return $uz ? "{$h} soat" : "{$h} ч";
    $d = (int) round($h / 24);
    if ($d < 30)      return $uz ? "{$d} kun" : "{$d} дн";
    return substr($iso, 0, 10);
}

function cheer(int $fromId, int $feedId): array {
    $f = one('SELECT user_id FROM feed WHERE id=?', [$feedId]);
    if (!$f) fail('not_found', 404);
    if ((int) $f['user_id'] === $fromId) fail('self');
    if (!rateOk('cheer:' . $fromId, 60, 3600)) fail('too_many');
    try {
        q('INSERT INTO cheers(from_id,to_id,feed_id,created_at) VALUES(?,?,?,?)',
          [$fromId, (int) $f['user_id'], $feedId, nowIso()]);
    } catch (Throwable $e) { /* уже поддержал */ }
    return ['cheers' => (int) (one('SELECT COUNT(*) c FROM cheers WHERE feed_id=?', [$feedId])['c'] ?? 0)];
}

/* ---------------- КТО СЕЙЧАС ИДЁТ ---------------- */
/* Экран «Люди»: живая витрина, чтобы приложение не выглядело пустым */
function people_list(array $viewer, string $filter = 'active', int $limit = 30): array {
    $lang = $viewer['lang'];
    $sql = "SELECT u.id FROM users u
            JOIN quests q ON q.user_id=u.id AND q.status='active'
            WHERE u.status='active' AND u.id<>? ";
    $args = [$viewer['id']];

    if ($filter === 'new')        $sql .= ' ORDER BY q.start_date DESC, u.id DESC';
    elseif ($filter === 'far')    $sql .= ' ORDER BY q.start_date ASC';
    elseif ($filter === 'city' && $viewer['city'] !== '') {
        $sql .= ' AND u.city=? ORDER BY u.last_seen DESC'; $args[] = $viewer['city'];
    } else                        $sql .= ' ORDER BY u.last_seen DESC';

    $sql .= ' LIMIT ?'; $args[] = $limit;

    $out = [];
    foreach (all($sql, $args) as $r) {
        $c = user_card((int) $r['id'], $lang, $viewer);
        if ($c) $out[] = $c;
    }
    return $out;
}

/* Живая статистика для лендинга и пустых состояний */
function public_stats(): array {
    return [
        'users'    => (int) (one("SELECT COUNT(*) c FROM users WHERE status='active'")['c'] ?? 0),
        'walking'  => (int) (one("SELECT COUNT(*) c FROM quests WHERE status='active'")['c'] ?? 0),
        'tasks'    => (int) (one("SELECT COUNT(*) c FROM tasks WHERE status='done'")['c'] ?? 0),
        'comebacks'=> (int) (one('SELECT COALESCE(SUM(comebacks),0) c FROM hero')['c'] ?? 0),
        'squads'   => (int) (one('SELECT COUNT(*) c FROM squads')['c'] ?? 0),
        'today'    => (int) (one("SELECT COUNT(*) c FROM users WHERE last_seen>=?", [date('Y-m-d 00:00:00')])['c'] ?? 0),
    ];
}

/* ---------------- ЖАЛОБЫ ---------------- */
function report_create(array $u, string $type, int $targetId, string $reason, string $note): void {
    if (!in_array($type, ['user', 'message', 'photo'], true)) fail('bad_type');
    if (!array_key_exists($reason, report_reasons('ru'))) fail('bad_reason');
    if (!rateOk('report:' . $u['id'], 10, 3600)) fail('too_many');
    if ($type === 'user' && $targetId === (int) $u['id']) fail('self');

    $dup = one("SELECT id FROM reports WHERE reporter_id=? AND target_type=? AND target_id=? AND status='new'",
               [$u['id'], $type, $targetId]);
    if ($dup) return;

    q('INSERT INTO reports(reporter_id,target_type,target_id,reason,note,created_at) VALUES(?,?,?,?,?,?)',
      [$u['id'], $type, $targetId, $reason, s($note, 800), nowIso()]);

    // 3 жалобы от разных людей на один объект — сразу прячем до решения модератора
    $n = (int) (one("SELECT COUNT(DISTINCT reporter_id) c FROM reports WHERE target_type=? AND target_id=? AND status='new'",
                    [$type, $targetId])['c'] ?? 0);
    if ($n >= 3) {
        if ($type === 'message') q('UPDATE squad_msgs SET hidden=1 WHERE id=?', [$targetId]);
        if ($type === 'photo')   q("UPDATE photos SET status='hidden' WHERE id=?", [$targetId]);
        if ($type === 'user')    q("UPDATE users SET status='shadow' WHERE id=? AND role='user'", [$targetId]);
    }
}

/* ---------------- МЕТКИ (назначает админ) ---------------- */
function badge_set(int $uid, string $key, string $note = ''): void {
    if ($key !== '' && !isset(badge_catalog()[$key])) return;
    q('UPDATE users SET badge=?, badge_note=? WHERE id=?', [$key, s($note, 60), $uid]);
    if ($key !== '') {
        $b = badge_catalog()[$key];
        q('INSERT OR IGNORE INTO achievements(user_id,key,title,created_at) VALUES(?,?,?,?)',
          [$uid, 'badge_' . $key, $b['icon'] . ' ' . $b['ru'], nowIso()]);
    }
}
