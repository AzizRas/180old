<?php
/* ============================================================
   Группа из 5.

   Приватность: наружу уходит ТОЛЬКО направление (Тело / Дело /
   Разум / Душа / Дисциплина). Текст цели, цифры веса, суммы,
   диагнозы — никогда.

   Рейтинг: не «кто сделал больше», а «кто вырос сильнее
   относительно себя неделю назад». Командный счёт — средний
   процент выполнения ЛИЧНОГО адаптированного плана, поэтому
   человек на облегчённой нагрузке не тянет группу вниз.
   ============================================================ */

function squad_of(int $userId): ?array {
    return one('SELECT s.* FROM squads s JOIN squad_members m ON m.squad_id=s.id WHERE m.user_id=? LIMIT 1', [$userId]);
}

function squad_create(array $u, string $name): array {
    if (squad_of((int) $u['id'])) fail('already_in_squad');
    $name = s($name, 60) ?: ($u['lang'] === 'uz' ? 'Yangi jamoa' : 'Новая команда');
    do { $code = randCode(6); } while (one('SELECT code FROM squads WHERE code=?', [$code]));
    q('INSERT INTO squads(name,code,leader_id,city,lang,created_at) VALUES(?,?,?,?,?,?)',
      [$name, $code, $u['id'], $u['city'], $u['lang'], nowIso()]);
    $id = lastId();
    q('INSERT INTO squad_members(squad_id,user_id,joined_at) VALUES(?,?,?)', [$id, $u['id'], nowIso()]);
    meetings_ensure($id, $u['lang']);
    feed_add((int) $u['id'], 'squad', [], 'squad');
    return one('SELECT * FROM squads WHERE id=?', [$id]);
}

function squad_join(array $u, string $code): array {
    if (squad_of((int) $u['id'])) fail('already_in_squad');
    $sq = one('SELECT * FROM squads WHERE code=?', [strtoupper(s($code, 12))]);
    if (!$sq) fail('squad_not_found', 404);
    $n = (int) (one('SELECT COUNT(*) c FROM squad_members WHERE squad_id=?', [$sq['id']])['c'] ?? 0);
    if ($n >= (int) cfg('squad.size', 5)) fail('squad_full');
    q('INSERT INTO squad_members(squad_id,user_id,joined_at) VALUES(?,?,?)', [$sq['id'], $u['id'], nowIso()]);
    squad_post((int) $sq['id'], (int) $u['id'],
        ($u['lang'] === 'uz' ? "{$u['name']} jamoaga qo'shildi" : "{$u['name']} присоединился к группе"), 'system');
    meetings_ensure((int) $sq['id'], $u['lang']);
    feed_add((int) $u['id'], 'squad', [], 'squad');
    return $sq;
}

function squad_leave(array $u): void {
    $sq = squad_of((int) $u['id']);
    if (!$sq) return;
    q('DELETE FROM squad_members WHERE user_id=?', [$u['id']]);
    $left = (int) (one('SELECT COUNT(*) c FROM squad_members WHERE squad_id=?', [$sq['id']])['c'] ?? 0);
    if ($left === 0) q('DELETE FROM squads WHERE id=?', [$sq['id']]);
    elseif ((int) $sq['leader_id'] === (int) $u['id']) {
        $next = one('SELECT user_id FROM squad_members WHERE squad_id=? ORDER BY joined_at LIMIT 1', [$sq['id']]);
        if ($next) q('UPDATE squads SET leader_id=? WHERE id=?', [$next['user_id'], $sq['id']]);
    }
}

/* Автоподбор: сначала свой город и язык, потом просто язык, потом новая группа */
function squad_auto(array $u): array {
    $size = (int) cfg('squad.size', 5);
    $try = [];
    if ($u['city'] !== '') $try[] = ['sql' => 'AND s.lang=? AND s.city=?', 'args' => [$u['lang'], $u['city']]];
    $try[] = ['sql' => 'AND s.lang=?', 'args' => [$u['lang']]];
    $try[] = ['sql' => '', 'args' => []];

    foreach ($try as $t) {
        $sq = one("SELECT s.*, COUNT(m.user_id) n
                   FROM squads s LEFT JOIN squad_members m ON m.squad_id=s.id
                   WHERE 1=1 {$t['sql']}
                   GROUP BY s.id HAVING n>0 AND n < ?
                   ORDER BY n DESC LIMIT 1", array_merge($t['args'], [$size]));
        if ($sq) return squad_join($u, $sq['code']);
    }
    return squad_create($u, $u['lang'] === 'uz' ? 'Yangi jamoa' : 'Новая команда');
}

/* Метрики участника за окно дней */
function member_metrics(int $userId, int $days = 7, int $offset = 0): array {
    $qq = get_quest($userId);
    if (!$qq) return ['pct' => 0, 'points' => 0, 'day' => 0, 'active' => false,
                      'streak' => 0, 'comebacks' => 0, 'category' => ''];
    sync_days($qq);
    $cur  = current_day($qq);
    $to   = max(1, $cur - $offset);
    $from = max(1, $to - $days + 1);
    $r = one('SELECT AVG(completion) a, SUM(points) p FROM day_stats WHERE quest_id=? AND day_no BETWEEN ? AND ?',
             [$qq['id'], $from, $to]);
    $h = one('SELECT streak, comebacks FROM hero WHERE user_id=?', [$userId]);
    $lastActive = one('SELECT MAX(day_no) d FROM day_stats WHERE quest_id=? AND completion>=0.5', [$qq['id']]);
    return [
        'pct'       => (int) round(((float) ($r['a'] ?? 0)) * 100),
        'points'    => (int) ($r['p'] ?? 0),
        'day'       => $cur,
        'streak'    => (int) ($h['streak'] ?? 0),
        'comebacks' => (int) ($h['comebacks'] ?? 0),
        'active'    => ($cur - (int) ($lastActive['d'] ?? 0)) <= 3,
        'category'  => $qq['category'],
    ];
}

function squad_view(array $u): ?array {
    $sq = squad_of((int) $u['id']);
    if (!$sq) return null;
    $lang = $u['lang'];

    $members = []; $sumPct = 0; $alive = 0; $aliveDays = 0;
    foreach (all('SELECT u.id,u.name,u.avatar,u.badge,u.city,u.bio,u.last_seen
                  FROM squad_members m JOIN users u ON u.id=m.user_id
                  WHERE m.squad_id=? ORDER BY m.joined_at', [$sq['id']]) as $m) {
        $uid  = (int) $m['id'];
        $now  = member_metrics($uid, 7, 0);
        $prev = member_metrics($uid, 7, 7);
        $delta = $now['pct'] - $prev['pct'];
        $sumPct += $now['pct'];
        if ($now['active']) $alive++;

        $qq = get_quest($uid);
        if ($qq) {
            $cur = current_day($qq); $from = max(1, $cur - 6);
            $aliveDays += (int) (one('SELECT COUNT(*) c FROM day_stats WHERE quest_id=? AND day_no BETWEEN ? AND ? AND completion>=0.5',
                                [$qq['id'], $from, $cur])['c'] ?? 0);
        }

        $members[] = [
            'id'        => $uid,
            'name'      => $m['name'],
            'avatar'    => $m['avatar'] ? 'uploads/avatars/' . $m['avatar'] : '',
            'initial'   => mb_strtoupper(mb_substr($m['name'] ?: '?', 0, 1)),
            'badge'     => badge_view((string) $m['badge'], $lang),
            'is_me'     => $uid === (int) $u['id'],
            'is_leader' => $uid === (int) $sq['leader_id'],
            'pct'       => $now['pct'],
            'delta'     => $delta,
            'day'       => $now['day'],
            'streak'    => $now['streak'],
            'comebacks' => $now['comebacks'],
            'active'    => $now['active'],
            'online'    => !empty($m['last_seen']) && (time() - strtotime($m['last_seen'])) < 600,
            // ЦЕЛЬ НЕ ОТДАЁТСЯ. Только направление.
            'direction' => direction_of($now['category'] ?: 'discipline', $lang),
            'bio'       => $m['bio'],
        ];
    }

    $n = max(1, count($members));
    $byDelta = $members;
    usort($byDelta, fn($a, $b) => $b['delta'] <=> $a['delta']);

    return [
        'id'        => (int) $sq['id'],
        'name'      => $sq['name'],
        'code'      => $sq['code'],
        'motto'     => $sq['motto'],
        'city'      => $sq['city'],
        'cities'    => squad_cities((int) $sq['id']),
        'format'    => meeting_format((int) $sq['id'], $lang),
        'size'      => (int) cfg('squad.size', 5),
        'is_leader' => (int) $sq['leader_id'] === (int) $u['id'],
        'members'   => $members,
        'top_improved' => array_slice($byDelta, 0, 3),
        'team_pct'  => (int) round($sumPct / $n),
        'alive'     => $alive,
        'alive_min' => (int) cfg('squad.alive_min', 3),
        'weekly'    => ['done' => $aliveDays, 'target' => $n * 4,
                        'pct' => (int) round(min(1, $aliveDays / max(1, $n * 4)) * 100)],
    ];
}

function squad_post(int $squadId, int $userId, string $text, string $kind = 'chat', ?int $refId = null): void {
    $text = s($text, 900);
    if ($text === '') return;
    if ($kind === 'chat' && !rateOk('sqmsg:' . $userId, 40, 600)) return;
    q('INSERT INTO squad_msgs(squad_id,user_id,text,kind,ref_id,created_at) VALUES(?,?,?,?,?,?)',
      [$squadId, $userId, $text, $kind, $refId, nowIso()]);
}

function squad_chat(int $squadId, string $lang = 'ru', int $limit = 80): array {
    $rows = all('SELECT m.id, m.text, m.kind, m.ref_id, m.hidden, m.created_at,
                        u.name, u.id uid, u.avatar, u.badge
                 FROM squad_msgs m JOIN users u ON u.id=m.user_id
                 WHERE m.squad_id=? ORDER BY m.id DESC LIMIT ?', [$squadId, $limit]);
    $out = [];
    foreach (array_reverse($rows) as $r) {
        $photo = null;
        if ($r['kind'] === 'photo' && $r['ref_id']) {
            $p = one("SELECT file,status FROM photos WHERE id=?", [$r['ref_id']]);
            if ($p && $p['status'] === 'ok') $photo = 'uploads/photos/' . $p['file'];
        }
        $out[] = [
            'id'      => (int) $r['id'],
            'uid'     => (int) $r['uid'],
            'name'    => $r['name'],
            'avatar'  => $r['avatar'] ? 'uploads/avatars/' . $r['avatar'] : '',
            'initial' => mb_strtoupper(mb_substr($r['name'] ?: '?', 0, 1)),
            'badge'   => badge_view((string) $r['badge'], $lang),
            'kind'    => $r['kind'],
            'photo'   => $photo,
            'text'    => (int) $r['hidden'] === 1
                ? ($lang === 'uz' ? '[xabar moderatsiyada]' : '[сообщение скрыто на проверку]')
                : $r['text'],
            'hidden'  => (int) $r['hidden'] === 1,
            'ago'     => ago($r['created_at'], $lang),
        ];
    }
    return $out;
}

function squad_board(int $limit = 20): array {
    $out = [];
    foreach (all('SELECT id,name FROM squads') as $sq) {
        $ids = all('SELECT user_id FROM squad_members WHERE squad_id=?', [$sq['id']]);
        if (!$ids) continue;
        $sum = 0; $cb = 0;
        foreach ($ids as $i) {
            $m = member_metrics((int) $i['user_id'], 7, 0);
            $sum += $m['pct']; $cb += $m['comebacks'];
        }
        $out[] = ['id' => (int) $sq['id'], 'name' => $sq['name'], 'members' => count($ids),
                  'pct' => (int) round($sum / count($ids)), 'comebacks' => $cb];
    }
    usort($out, fn($a, $b) => [$b['pct'], $b['comebacks']] <=> [$a['pct'], $a['comebacks']]);
    return array_slice($out, 0, $limit);
}

/* ============================================================
   ВСТРЕЧИ — 2 раза в неделю. Это ядро удержания.
   ============================================================ */

/* Досоздаёт встречи на 2 недели вперёд по расписанию из конфига */
function meetings_ensure(int $squadId, string $lang = 'ru'): void {
    $days  = cfg('meetings.days', [3, 0]);           // 0=вс, 3=ср
    $time  = (string) cfg('meetings.time', '20:00');
    $weeks = 2;
    for ($w = 0; $w < $weeks; $w++) {
        foreach ($days as $dow) {
            $d = new DateTime('now', tz());
            $d->modify('monday this week');
            $d->modify('+' . ($w * 7 + (($dow + 6) % 7)) . ' days');
            [$hh, $mm] = array_pad(explode(':', $time), 2, '00');
            $d->setTime((int) $hh, (int) $mm);
            if ($d->getTimestamp() < time() - 3600) continue;
            $iso = $d->format('Y-m-d H:i:s');
            if (one('SELECT id FROM meetings WHERE squad_id=? AND starts_at=?', [$squadId, $iso])) continue;
            $wk = (int) $d->format('W');
            $a = meeting_agenda($wk, $lang);
            $fmt = meeting_format($squadId, $lang);
            q('INSERT INTO meetings(squad_id,starts_at,kind,title,agenda,place,link,created_at) VALUES(?,?,?,?,?,?,?,?)',
              [$squadId, $iso, $fmt['kind'], $a['title'], $a['agenda'], $fmt['place'], $fmt['link'], nowIso()]);
        }
    }
}

/* ------------------------------------------------------------
   Формат встречи выбирается сам, по городам участников.
   Трое и больше в одном городе — встречаемся вживую.
   Меньше — созвон, потому что тащить человека через полстраны
   ради двадцати минут никто не будет.
   ------------------------------------------------------------ */
function meeting_format(int $squadId, string $lang = 'ru'): array {
    $rows = all("SELECT u.city, COUNT(*) c FROM squad_members m JOIN users u ON u.id=m.user_id
                 WHERE m.squad_id=? AND u.city<>'' GROUP BY u.city ORDER BY c DESC LIMIT 1", [$squadId]);
    $min = (int) cfg('meetings.offline_min', 3);
    if ($rows && (int) $rows[0]['c'] >= $min) {
        return ['kind' => 'offline', 'city' => $rows[0]['city'],
                'place' => ($lang === 'uz' ? "Joyni yetakchi belgilaydi · " : 'Место назначает лидер · ') . $rows[0]['city'],
                'link'  => ''];
    }
    return ['kind' => 'online', 'city' => '',
            'place' => '', 'link' => (string) cfg('meetings.default_link', '')];
}

/* Города группы — для подсказки лидеру */
function squad_cities(int $squadId): array {
    return array_map(fn($r) => ['city' => $r['city'], 'n' => (int) $r['c']],
        all("SELECT u.city, COUNT(*) c FROM squad_members m JOIN users u ON u.id=m.user_id
             WHERE m.squad_id=? AND u.city<>'' GROUP BY u.city ORDER BY c DESC", [$squadId]));
}

function meetings_list(int $squadId, array $u): array {
    meetings_ensure($squadId, $u['lang']);
    $rows = all("SELECT * FROM meetings WHERE squad_id=? AND starts_at >= ?
                 ORDER BY starts_at LIMIT 4", [$squadId, date('Y-m-d H:i:s', time() - 7200)]);
    $out = [];
    foreach ($rows as $m) {
        $rs = all('SELECT r.answer, r.attended, u.id, u.name, u.avatar
                   FROM meeting_rsvp r JOIN users u ON u.id=r.user_id WHERE r.meeting_id=?', [$m['id']]);
        $mine = null; $yes = [];
        foreach ($rs as $r) {
            if ((int) $r['id'] === (int) $u['id']) $mine = $r['answer'];
            if ($r['answer'] === 'yes') $yes[] = ['id' => (int) $r['id'], 'name' => $r['name'],
                'initial' => mb_strtoupper(mb_substr($r['name'] ?: '?', 0, 1)),
                'avatar' => $r['avatar'] ? 'uploads/avatars/' . $r['avatar'] : ''];
        }
        $mins = minutesUntil($m['starts_at']);
        $out[] = [
            'id'      => (int) $m['id'],
            'starts'  => $m['starts_at'],
            'when'    => when_human($m['starts_at'], $u['lang']),
            'kind'    => $m['kind'],
            'title'   => $m['title'],
            'agenda'  => $m['agenda'],
            'place'   => $m['place'],
            'link'    => $m['link'],
            'state'   => $m['state'],
            'my'      => $mine,
            'yes'     => $yes,
            'live'    => $mins <= 15 && $mins >= -60,   // окно, когда можно отметиться
            'mins'    => $mins,
        ];
    }
    return $out;
}

function when_human(string $iso, string $lang): string {
    $d = new DateTime($iso, tz());
    $now = new DateTime('now', tz());
    $diff = daysBetween($now->format('Y-m-d'), $d->format('Y-m-d'));
    $t = $d->format('H:i');
    $dowRu = ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];
    $dowUz = ['Yak','Du','Se','Chor','Pay','Ju','Sha'];
    $dow = ($lang === 'uz' ? $dowUz : $dowRu)[(int) $d->format('w')];
    if ($diff === 0) return ($lang === 'uz' ? 'Bugun' : 'Сегодня') . ' ' . $t;
    if ($diff === 1) return ($lang === 'uz' ? 'Ertaga' : 'Завтра') . ' ' . $t;
    return $dow . ' ' . $d->format('d.m') . ' ' . $t;
}

function meeting_rsvp(int $meetingId, int $uid, string $answer): void {
    if (!in_array($answer, ['yes', 'no', 'maybe'], true)) fail('bad_answer');
    $m = one('SELECT m.*, s.id sid FROM meetings m JOIN squads s ON s.id=m.squad_id WHERE m.id=?', [$meetingId]);
    if (!$m) fail('not_found', 404);
    if (!one('SELECT 1 FROM squad_members WHERE squad_id=? AND user_id=?', [$m['sid'], $uid])) fail('not_member', 403);
    q('INSERT INTO meeting_rsvp(meeting_id,user_id,answer,updated_at) VALUES(?,?,?,?)
       ON CONFLICT(meeting_id,user_id) DO UPDATE SET answer=excluded.answer, updated_at=excluded.updated_at',
      [$meetingId, $uid, $answer, nowIso()]);
}

/* Отметка присутствия — открыта за 15 минут до и час после начала */
function meeting_checkin(int $meetingId, array $u): array {
    $m = one('SELECT m.*, s.id sid FROM meetings m JOIN squads s ON s.id=m.squad_id WHERE m.id=?', [$meetingId]);
    if (!$m) fail('not_found', 404);
    if (!one('SELECT 1 FROM squad_members WHERE squad_id=? AND user_id=?', [$m['sid'], $u['id']])) fail('not_member', 403);
    $mins = minutesUntil($m['starts_at']);
    if ($mins > 15 || $mins < -60) fail('not_now');

    q('INSERT INTO meeting_rsvp(meeting_id,user_id,answer,attended,updated_at) VALUES(?,?,?,1,?)
       ON CONFLICT(meeting_id,user_id) DO UPDATE SET attended=1, answer=\'yes\', updated_at=excluded.updated_at',
      [$meetingId, $u['id'], 'yes', nowIso()]);

    q('UPDATE hero SET xp=xp+25 WHERE user_id=?', [$u['id']]);
    q('INSERT OR IGNORE INTO achievements(user_id,key,title,created_at) VALUES(?,?,?,?)',
      [$u['id'], 'meet_first', $u['lang'] === 'uz' ? '🤝 Birinchi uchrashuv' : '🤝 Первая встреча', nowIso()]);
    feed_add((int) $u['id'], 'meeting');

    $n = (int) (one('SELECT COUNT(*) c FROM meeting_rsvp WHERE meeting_id=? AND attended=1', [$meetingId])['c'] ?? 0);
    $total = (int) (one('SELECT COUNT(*) c FROM squad_members WHERE squad_id=?', [$m['sid']])['c'] ?? 0);
    if ($n === $total && $total >= 3) {
        squad_post((int) $m['sid'], (int) $u['id'],
            $u['lang'] === 'uz' ? '🔥 Butun jamoa yig\'ildi' : '🔥 Собралась вся группа', 'system');
        foreach (all('SELECT user_id FROM squad_members WHERE squad_id=?', [$m['sid']]) as $mm)
            q('UPDATE hero SET xp=xp+20 WHERE user_id=?', [$mm['user_id']]);
    }
    return ['attended' => $n, 'total' => $total];
}
