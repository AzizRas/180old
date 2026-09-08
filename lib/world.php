<?php
/* ============================================================
   Виртуальный мир.

   Принцип: мир НЕ является отдельной игрой. Единственный источник
   энергии — реально выполненные задания. Ничего нельзя купить,
   выиграть или накрутить. Если человек ничего не делает в жизни,
   его территория не растёт.

   Технически мир асинхронный (никакого реального времени и
   вебсокетов) — поэтому он работает на обычном PHP-хостинге.
   Встреча группы «в мире» — это сбор у общего костра: участники
   отмечаются, и все видят, кто пришёл.
   ============================================================ */

const WORLD_W = 7;
const WORLD_H = 7;

/* Что можно построить. req — минимум характеристики героя. */
function world_buildings(string $lang = 'ru'): array {
    $b = [
        'path'    => ['cost' => 10,  'stat' => 'discipline', 'req' => 0,   'icon' => '▪',  'ru' => 'Тропа',      'uz' => "So'qmoq"],
        'tent'    => ['cost' => 20,  'stat' => 'discipline', 'req' => 30,  'icon' => '⛺', 'ru' => 'Палатка',    'uz' => 'Chodir'],
        'garden'  => ['cost' => 30,  'stat' => 'health',     'req' => 60,  'icon' => '🌿', 'ru' => 'Огород',     'uz' => 'Polizcha'],
        'spring'  => ['cost' => 45,  'stat' => 'health',     'req' => 150, 'icon' => '💧', 'ru' => 'Родник',     'uz' => 'Buloq'],
        'library' => ['cost' => 45,  'stat' => 'mind',       'req' => 120, 'icon' => '📚', 'ru' => 'Библиотека', 'uz' => 'Kutubxona'],
        'forge'   => ['cost' => 55,  'stat' => 'business',   'req' => 120, 'icon' => '🔨', 'ru' => 'Мастерская', 'uz' => 'Ustaxona'],
        'tree'    => ['cost' => 40,  'stat' => 'soul',       'req' => 90,  'icon' => '🌳', 'ru' => 'Дерево',     'uz' => 'Daraxt'],
        'house'   => ['cost' => 90,  'stat' => 'discipline', 'req' => 250, 'icon' => '🏠', 'ru' => 'Дом',        'uz' => 'Uy'],
        'gym'     => ['cost' => 110, 'stat' => 'health',     'req' => 400, 'icon' => '🏋', 'ru' => 'Зал',        'uz' => 'Zal'],
        'tower'   => ['cost' => 130, 'stat' => 'mind',       'req' => 400, 'icon' => '🗼', 'ru' => 'Башня',      'uz' => 'Minora'],
        'market'  => ['cost' => 140, 'stat' => 'business',   'req' => 400, 'icon' => '🏪', 'ru' => 'Лавка',      'uz' => "Do'kon"],
        'garden2' => ['cost' => 160, 'stat' => 'soul',       'req' => 400, 'icon' => '🕌', 'ru' => 'Тихое место','uz' => 'Sokin joy'],
    ];
    foreach ($b as $k => $v) $b[$k]['label'] = $v[$lang] ?? $v['ru'];
    return $b;
}

function world_row(int $uid): array {
    $r = one('SELECT * FROM world_user WHERE user_id=?', [$uid]);
    if (!$r) {
        q('INSERT OR IGNORE INTO world_user(user_id,tiles,energy,spent,updated_at) VALUES(?,?,0,0,?)',
          [$uid, '{}', nowIso()]);
        $r = one('SELECT * FROM world_user WHERE user_id=?', [$uid]);
    }
    return $r;
}

/* Пересчёт энергии: всё, что заработано за путь, минус потраченное */
function world_sync(int $uid): array {
    $row = world_row($uid);
    $qq  = get_quest($uid);
    $earned = 0;
    if ($qq) {
        $p = (int) (one('SELECT COALESCE(SUM(points),0) p FROM day_stats WHERE quest_id=?', [$qq['id']])['p'] ?? 0);
        $earned = (int) floor($p / 4);          // 4 очка прогресса = 1 энергия
        // бонус за встречи: приходить на сбор выгодно
        $m = (int) (one('SELECT COUNT(*) c FROM meeting_rsvp WHERE user_id=? AND attended=1', [$uid])['c'] ?? 0);
        $earned += $m * 15;
    }
    $energy = max(0, $earned - (int) $row['spent']);
    if ($energy !== (int) $row['energy']) {
        q('UPDATE world_user SET energy=?, updated_at=? WHERE user_id=?', [$energy, nowIso(), $uid]);
        $row['energy'] = $energy;
    }
    return $row;
}

function world_state(int $uid, string $lang = 'ru'): array {
    $row = world_sync($uid);
    $tiles = json_decode($row['tiles'] ?: '{}', true) ?: [];
    $h = one('SELECT * FROM hero WHERE user_id=?', [$uid]) ?: [];

    $bs = world_buildings($lang);
    $avail = [];
    foreach ($bs as $k => $b) {
        $have = (int) ($h[$b['stat']] ?? 0);
        $avail[] = [
            'key' => $k, 'icon' => $b['icon'], 'label' => $b['label'],
            'cost' => $b['cost'], 'stat' => $b['stat'], 'req' => $b['req'],
            'have' => $have,
            'locked' => $have < $b['req'],
        ];
    }

    $grid = [];
    for ($y = 0; $y < WORLD_H; $y++) {
        for ($x = 0; $x < WORLD_W; $x++) {
            $k = $x . ',' . $y;
            $t = $tiles[$k] ?? null;
            $grid[] = ['x' => $x, 'y' => $y,
                       'b' => $t['t'] ?? '',
                       'icon' => $t ? ($bs[$t['t']]['icon'] ?? '?') : ''];
        }
    }

    return [
        'grid'      => $grid,
        'w'         => WORLD_W, 'h' => WORLD_H,
        'energy'    => (int) $row['energy'],
        'built'     => count($tiles),
        'buildings' => $avail,
    ];
}

function world_build(int $uid, int $x, int $y, string $type, string $lang = 'ru'): array {
    if ($x < 0 || $y < 0 || $x >= WORLD_W || $y >= WORLD_H) fail('bad_cell');
    $bs = world_buildings($lang);
    if (!isset($bs[$type])) fail('bad_building');
    $b = $bs[$type];

    $h = one('SELECT * FROM hero WHERE user_id=?', [$uid]) ?: [];
    if ((int) ($h[$b['stat']] ?? 0) < $b['req']) fail('locked');

    $row = world_sync($uid);
    if ((int) $row['energy'] < $b['cost']) fail('no_energy');

    $tiles = json_decode($row['tiles'] ?: '{}', true) ?: [];
    $k = $x . ',' . $y;
    if (isset($tiles[$k])) fail('busy');

    $tiles[$k] = ['t' => $type, 'at' => today()];
    q('UPDATE world_user SET tiles=?, spent=spent+?, updated_at=? WHERE user_id=?',
      [json_encode($tiles, JSON_UNESCAPED_UNICODE), $b['cost'], nowIso(), $uid]);

    if (count($tiles) === 1)  feed_add($uid, 'world_first', []);
    if (count($tiles) === 10) feed_add($uid, 'world_ten', []);

    return world_state($uid, $lang);
}

/* ============================================================
   ЭКСПЕДИЦИИ ГРУППЫ
   Недельная общая цель. Прогресс = сумма процентов выполнения
   личных (уже адаптированных) планов участников. Поэтому человек
   на облегчённом плане вносит столько же, сколько остальные.
   ============================================================ */

function expedition_defs(string $lang = 'ru'): array {
    $ru = [
        ['key' => 'bridge',  'title' => 'Построить мост',        'reward' => 'Мост в общем лагере'],
        ['key' => 'well',    'title' => 'Вырыть колодец',        'reward' => 'Колодец в общем лагере'],
        ['key' => 'wall',    'title' => 'Поставить стену',       'reward' => 'Стена вокруг лагеря'],
        ['key' => 'field',   'title' => 'Засеять поле',          'reward' => 'Поле у лагеря'],
        ['key' => 'tower',   'title' => 'Сложить сторожевую башню','reward' => 'Башня над лагерем'],
        ['key' => 'road',    'title' => 'Проложить дорогу',      'reward' => 'Дорога из лагеря'],
    ];
    $uz = [
        ['key' => 'bridge',  'title' => "Ko'prik qurish",        'reward' => "Umumiy lagerdagi ko'prik"],
        ['key' => 'well',    'title' => 'Quduq qazish',          'reward' => 'Lagerdagi quduq'],
        ['key' => 'wall',    'title' => "Devor ko'tarish",       'reward' => 'Lager atrofidagi devor'],
        ['key' => 'field',   'title' => 'Dala ekish',            'reward' => 'Lager yonidagi dala'],
        ['key' => 'tower',   'title' => 'Qorovul minorasi',      'reward' => 'Lager ustidagi minora'],
        ['key' => 'road',    'title' => "Yo'l solish",           'reward' => 'Lagerdan chiqadigan yo\'l'],
    ];
    return $lang === 'uz' ? $uz : $ru;
}

function week_bounds(): array {
    $mon = (new DateTime('monday this week', tz()))->format('Y-m-d');
    $sun = (new DateTime('sunday this week', tz()))->format('Y-m-d');
    return [$mon, $sun];
}

function expedition_current(int $squadId, string $lang = 'ru'): ?array {
    [$from, $to] = week_bounds();
    $members = (int) (one('SELECT COUNT(*) c FROM squad_members WHERE squad_id=?', [$squadId])['c'] ?? 0);
    if ($members === 0) return null;

    $e = one("SELECT * FROM expeditions WHERE squad_id=? AND starts_on=? LIMIT 1", [$squadId, $from]);
    if ($e) {
        // состав мог измениться — цель пересчитываем, но только вверх и только пока идёт неделя
        $want = $members * 45;
        if ($e['state'] === 'active' && (int) $e['target'] < $want) {
            q('UPDATE expeditions SET target=? WHERE id=?', [$want, $e['id']]);
            $e['target'] = $want;
        }
        return expedition_view($e, $lang);
    }

    $defs = expedition_defs($lang);
    $week = (int) (new DateTime($from))->format('W');
    $d = $defs[$week % count($defs)];
    $target = $members * 45;   // ~64% среднего выполнения от каждого за неделю

    q('INSERT INTO expeditions(squad_id,key,title,target,progress,starts_on,ends_on,reward,created_at)
       VALUES(?,?,?,?,0,?,?,?,?)',
       [$squadId, $d['key'], $d['title'], $target, $from, $to, $d['reward'], nowIso()]);
    return expedition_view(one('SELECT * FROM expeditions WHERE id=?', [lastId()]), $lang);
}

function expedition_view(array $e, string $lang = 'ru'): array {
    $top = all("SELECT l.user_id, SUM(l.amount) s, u.name, u.avatar, u.badge
                FROM expedition_log l JOIN users u ON u.id=l.user_id
                WHERE l.exp_id=? GROUP BY l.user_id ORDER BY s DESC", [$e['id']]);
    return [
        'id'       => (int) $e['id'],
        'key'      => $e['key'],
        'title'    => $e['title'],
        'target'   => (int) $e['target'],
        'progress' => (int) $e['progress'],
        'pct'      => (int) round(min(1, (int) $e['progress'] / max(1, (int) $e['target'])) * 100),
        'ends_on'  => $e['ends_on'],
        'days_left'=> max(0, daysBetween(today(), $e['ends_on'])),
        'state'    => $e['state'],
        'reward'   => $e['reward'],
        'top'      => array_map(fn($t) => [
            'user_id' => (int) $t['user_id'], 'name' => $t['name'],
            'avatar'  => $t['avatar'] ? 'uploads/avatars/' . $t['avatar'] : '',
            'initial' => mb_strtoupper(mb_substr($t['name'] ?: '?', 0, 1)),
            'badge'   => badge_view((string) $t['badge'], $lang),
            'amount'  => (int) $t['s'],
        ], $top),
    ];
}

/* Вызывается при закрытии дня: вклад = процент выполнения × 10 */
function expedition_contribute(int $uid, int $pct): void {
    $sq = squad_of($uid);
    if (!$sq) return;
    $e = one("SELECT * FROM expeditions WHERE squad_id=? AND starts_on=? AND state='active'",
             [$sq['id'], week_bounds()[0]]);
    if (!$e) return;

    $amount = max(0, (int) round($pct / 10));
    // за сегодня учитываем только разницу — чтобы отметки/снятия не накручивали
    $already = (int) (one('SELECT COALESCE(SUM(amount),0) s FROM expedition_log
                           WHERE exp_id=? AND user_id=? AND created_at>=?',
                          [$e['id'], $uid, today() . ' 00:00:00'])['s'] ?? 0);
    $delta = $amount - $already;
    if ($delta === 0) return;

    q('INSERT INTO expedition_log(exp_id,user_id,amount,created_at) VALUES(?,?,?,?)',
      [$e['id'], $uid, $delta, nowIso()]);
    q('UPDATE expeditions SET progress=MAX(0, progress+?) WHERE id=?', [$delta, $e['id']]);

    $e = one('SELECT * FROM expeditions WHERE id=?', [$e['id']]);
    if ((int) $e['progress'] >= (int) $e['target'] && $e['state'] === 'active') {
        q("UPDATE expeditions SET state='won' WHERE id=?", [$e['id']]);
        foreach (all('SELECT user_id FROM squad_members WHERE squad_id=?', [$sq['id']]) as $m) {
            q('UPDATE hero SET xp=xp+60 WHERE user_id=?', [$m['user_id']]);
            q('INSERT OR IGNORE INTO achievements(user_id,key,title,created_at) VALUES(?,?,?,?)',
              [$m['user_id'], 'exp_' . $e['key'], '🏕 ' . $e['title'], nowIso()]);
        }
        feed_add($uid, 'expedition', ['title' => $e['title']]);
        squad_post((int) $sq['id'], (int) $sq['leader_id'],
            '🏕 ' . $e['title'] . ' — готово. ' . $e['reward'] . ' появился в лагере.', 'system');
    }
}

/* Общий лагерь группы: что построено экспедициями + кто сейчас у костра */
function camp_state(int $squadId, string $lang = 'ru'): array {
    $won = all("SELECT key,title,reward,ends_on FROM expeditions WHERE squad_id=? AND state='won' ORDER BY id", [$squadId]);
    $icons = ['bridge' => '🌉', 'well' => '⛲', 'wall' => '🧱', 'field' => '🌾', 'tower' => '🗼', 'road' => '🛤'];

    // «У костра» — те, кто отметился на ближайшей встрече или заходил за 15 минут
    $near = all("SELECT u.id,u.name,u.avatar,u.badge,u.last_seen
                 FROM squad_members m JOIN users u ON u.id=m.user_id
                 WHERE m.squad_id=? ORDER BY u.last_seen DESC", [$squadId]);
    $present = [];
    foreach ($near as $n) {
        $present[] = [
            'id' => (int) $n['id'], 'name' => $n['name'],
            'initial' => mb_strtoupper(mb_substr($n['name'] ?: '?', 0, 1)),
            'avatar' => $n['avatar'] ? 'uploads/avatars/' . $n['avatar'] : '',
            'badge' => badge_view((string) $n['badge'], $lang),
            'here' => !empty($n['last_seen']) && (time() - strtotime($n['last_seen'])) < 900,
        ];
    }

    return [
        'landmarks' => array_map(fn($w) => ['icon' => $icons[$w['key']] ?? '🏕',
                                            'title' => $w['title'], 'reward' => $w['reward'],
                                            'date' => $w['ends_on']], $won),
        'members'   => $present,
    ];
}
