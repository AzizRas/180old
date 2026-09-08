<?php
/* ============================================================
   Генерация плана на 180 дней.
   Задачи создаются НЕ разом на 180 дней, а по мере наступления
   дня — иначе изменение интенсивности не влияло бы на будущее.
   ============================================================ */

function stage_of_day(int $day): int {
    $len = (int) cfg('engine.stage_len', 30);
    return max(1, min(6, (int) ceil($day / $len)));
}

function tasks_count_for(int $intensity): int {
    return [1 => 2, 2 => 3, 3 => 3, 4 => 4, 5 => 5][max(1, min(5, $intensity))] ?? 3;
}

/* Создаёт квест из ответов интервью */
function create_quest(int $userId, array $answers, string $lang): array {
    $a = [];
    foreach ($answers as $x) $a[$x['id'] ?? ''] = $x['value'] ?? '';

    $cat = $a['category'] ?? 'discipline';
    if (!isset(categories_def()[$cat])) $cat = 'discipline';

    $minutes   = clampInt($a['minutes'] ?? 30, 5, 300, 30);
    $intensity = $minutes >= 120 ? 4 : ($minutes >= 60 ? 3 : ($minutes >= 30 ? 2 : 1));

    $title = s($a['goal'] ?? '', 200);
    if ($title === '') $title = categories_def()[$cat][$lang] ?? categories_def()[$cat]['ru'];

    q('INSERT INTO quests(user_id,title,category,why,obstacle,future_self,minutes_day,intensity,start_date,source,created_at)
       VALUES(?,?,?,?,?,?,?,?,?,?,?)',
        [$userId, $title, $cat, s($a['why'] ?? '', 1000), s($a['obstacle'] ?? '', 200),
         s($a['future'] ?? '', 3000), $minutes, $intensity, today(), 'template', nowIso()]);

    $qid = lastId();
    q('INSERT OR IGNORE INTO hero(user_id) VALUES(?)', [$userId]);
    q('INSERT OR IGNORE INTO coach_state(user_id,memory) VALUES(?, ?)', [$userId, json_encode([
        'start' => today(),
        'notes' => [],
    ], JSON_UNESCAPED_UNICODE)]);

    $quest = one('SELECT * FROM quests WHERE id=?', [$qid]);

    // Персональный пакет заданий на первую неделю — сразу, чтобы день 1
    // уже был собран под этого человека, а не из общей библиотеки.
    // Любая ошибка ИИ здесь безопасна: день соберётся из библиотеки.
    try {
        $u = one('SELECT * FROM users WHERE id=?', [$userId]);
        if ($u) generate_week($u, $quest, 1);
    } catch (Throwable $e) { error_log('[180kun planner] ' . $e->getMessage()); }

    return $quest;
}

/* ИИ добавляет персональные задания. Не обязателен. */
function ai_enrich_plan(int $questId, array $a, string $lang): void {
    if (!ai_enabled()) return;

    $langName = $lang === 'uz' ? "o'zbek tilida (lotin alifbosida)" : 'на русском языке';
    $sys = "Ты составляешь персональный план изменений на 180 дней для приложения LEVEL 180.
Задания должны быть: конкретные, выполнимые за отведённое время, проверяемые, без медицинских назначений
(нельзя указывать калораж, дозировки, препараты, диеты по граммам). Формулировка — действие, а не намерение.
Пиши {$langName}.";

    $facts = json_encode($a, JSON_UNESCAPED_UNICODE);
    $user = "Данные интервью: {$facts}

Составь 24 персональных задания под эту цель — по 4 на каждый из 6 этапов (этапы: 1 START, 2 FOUNDATION, 3 MOMENTUM, 4 TRANSFORMATION, 5 CONSOLIDATION, 6 NEW SELF).
Сложность должна расти от этапа к этапу.
Верни JSON строго в формате:
{\"tasks\":[{\"stage\":1,\"title\":\"...\",\"stat\":\"health|discipline|mind|business|soul\",\"points\":12,\"verify\":\"self|number|photo|question\",\"min_int\":1}]}
points — от 6 до 24. min_int — минимальная интенсивность 1..5, при которой задание уместно.";

    $raw = ai_chat([['role' => 'user', 'content' => $user]], $sys, true, 'plan');
    $d = ai_json($raw);
    if (!$d || empty($d['tasks']) || !is_array($d['tasks'])) return;

    $n = 0;
    foreach ($d['tasks'] as $t) {
        $title = s($t['title'] ?? '', 160);
        if ($title === '') continue;
        $stat = in_array($t['stat'] ?? '', ['health','discipline','mind','business','soul'], true) ? $t['stat'] : 'discipline';
        $ver  = in_array($t['verify'] ?? '', ['self','number','photo','question'], true) ? $t['verify'] : 'self';
        q('INSERT INTO ai_tasks(quest_id,stage,title,stat,points,verify,min_int) VALUES(?,?,?,?,?,?,?)',
            [$questId, clampInt($t['stage'] ?? 1, 1, 6, 1), $title, $stat,
             clampInt($t['points'] ?? 12, 4, 30, 12), $ver, clampInt($t['min_int'] ?? 1, 1, 5, 1)]);
        if (++$n >= 40) break;
    }
    if ($n > 0) q("UPDATE quests SET source='ai' WHERE id=?", [$questId]);
}

/* Пул кандидатов на день */
function day_candidates(array $quest, int $day, string $lang): array {
    $stage = stage_of_day($day);
    $int   = (int) $quest['intensity'];
    $lib   = task_library();
    $cat   = $quest['category'];
    $pool  = [];

    foreach (($lib[$cat] ?? []) as $t) {
        [$ru, $uz, $stat, $pts, $stages, $minInt, $ver] = $t;
        if (!in_array($stage, $stages, true)) continue;
        if ($minInt > $int) continue;
        $pool[] = ['title' => $lang === 'uz' ? $uz : $ru, 'stat' => $stat,
                   'points' => (int) $pts, 'verify' => $ver, 'src' => 'lib'];
    }

    foreach (all('SELECT * FROM ai_tasks WHERE quest_id=? AND stage=? AND min_int<=?',
                 [$quest['id'], $stage, $int]) as $t) {
        $pool[] = ['title' => $t['title'], 'stat' => $t['stat'],
                   'points' => (int) $t['points'], 'verify' => $t['verify'], 'src' => 'ai'];
    }

    // подмешиваем «соседнюю» категорию, чтобы герой не был однобоким
    $neighbors = ['health' => 'spirit', 'fitness' => 'discipline', 'learning' => 'discipline',
                  'business' => 'finance', 'discipline' => 'spirit', 'relations' => 'spirit',
                  'spirit' => 'relations', 'finance' => 'discipline'];
    $nb = $neighbors[$cat] ?? 'discipline';
    foreach (($lib[$nb] ?? []) as $t) {
        [$ru, $uz, $stat, $pts, $stages, $minInt, $ver] = $t;
        if (!in_array($stage, $stages, true) || $minInt > $int) continue;
        $pool[] = ['title' => $lang === 'uz' ? $uz : $ru, 'stat' => $stat,
                   'points' => (int) round($pts * 0.8), 'verify' => $ver, 'src' => 'nb'];
    }

    return $pool;
}

/* Создаёт задачи дня, если их ещё нет. Детерминированно. */
function ensure_day_tasks(array $quest, int $day, string $lang): array {
    $exist = all('SELECT * FROM tasks WHERE quest_id=? AND day_no=? ORDER BY id', [$quest['id'], $day]);
    if ($exist) return $exist;
    if ($day < 1 || $day > (int) cfg('engine.total_days', 180)) return [];

    $int  = (int) $quest['intensity'];
    $need = tasks_count_for($int);

    // 1) базовая привычка (ротация по дням) — держит остальные характеристики живыми
    $habits = base_habits();
    $h = $habits[($day - 1) % count($habits)];
    $rows = [[
        'title' => $lang === 'uz' ? $h[1] : $h[0],
        'stat'  => $h[2], 'points' => (int) $h[3], 'verify' => $h[4], 'weight' => 1,
    ]];

    // 2) ПЕРСОНАЛЬНЫЕ задания от ИИ — основная часть дня
    $wantAi = max(0, (int) round($need * ai_share()));
    $used = [];
    foreach (ai_tasks_for_day((int) $quest['id'], $day, $wantAi) as $t) {
        if (count($rows) >= $need) break;
        if (isset($used[$t['title']])) continue;
        $used[$t['title']] = 1;
        $rows[] = ['title' => $t['title'], 'stat' => $t['stat'], 'points' => (int) $t['points'],
                   'verify' => $t['verify'], 'weight' => 2];
        q('UPDATE ai_day SET used=1 WHERE id=?', [$t['id']]);
    }

    // 3) добор из библиотеки: и как страховка, и чтобы герой не перекашивался
    $pool = day_candidates($quest, $day, $lang);
    if ($pool && count($rows) < $need) {
        // детерминированная перестановка: один и тот же день = один и тот же набор
        $seed = crc32('q' . $quest['id'] . 'd' . $day . 'i' . $int);
        mt_srand($seed);
        $idx = range(0, count($pool) - 1);
        shuffle($idx);
        mt_srand();

        foreach ($idx as $i) {
            if (count($rows) >= $need) break;
            $c = $pool[$i];
            if (isset($used[$c['title']])) continue;
            $used[$c['title']] = 1;
            $rows[] = ['title' => $c['title'], 'stat' => $c['stat'], 'points' => $c['points'],
                       'verify' => $c['verify'], 'weight' => 2];
        }
    }

    // 4) недельная рефлексия по воскресеньям программы
    if ($day % 7 === 0) {
        $rows[] = [
            'title'  => $lang === 'uz'
                ? "Hafta yakuni: nima ishladi, nima ishlamadi?"
                : 'Итог недели: что сработало, а что нет?',
            'stat' => 'discipline', 'points' => 14, 'verify' => 'question', 'weight' => 1,
        ];
    }

    // 5) контрольная точка этапа
    if (in_array($day, cfg('engine.checkpoint_days', []), true)) {
        $rows[] = [
            'title'  => $lang === 'uz'
                ? "Nazorat nuqtasi: natijalarni o'lchab yozib qo'y"
                : 'Контрольная точка: измерь и запиши свой результат',
            'stat' => 'discipline', 'points' => 20, 'verify' => 'question', 'weight' => 1,
        ];
    }

    foreach ($rows as $r) {
        q('INSERT INTO tasks(quest_id,day_no,title,stat,points,verify,weight) VALUES(?,?,?,?,?,?,?)',
            [$quest['id'], $day, $r['title'], $r['stat'], $r['points'], $r['verify'], $r['weight']]);
    }

    return all('SELECT * FROM tasks WHERE quest_id=? AND day_no=? ORDER BY id', [$quest['id'], $day]);
}

/* Карта пути: этапы + вехи (для экрана «План») */
function roadmap(array $quest, int $curDay, string $lang): array {
    $st = stages_def();
    $len = (int) cfg('engine.stage_len', 30);
    $out = [];
    for ($i = 1; $i <= 6; $i++) {
        $from = ($i - 1) * $len + 1;
        $to   = $i * $len;
        $done = (int) (one('SELECT COUNT(*) c FROM day_stats WHERE quest_id=? AND day_no BETWEEN ? AND ? AND completion>=0.5',
                    [$quest['id'], $from, $to])['c'] ?? 0);
        $out[] = [
            'stage'   => $i,
            'name'    => $st[$i][$lang] ?? $st[$i]['ru'],
            'sub'     => $st[$i][$lang . '_sub'] ?? $st[$i]['ru_sub'],
            'from'    => $from,
            'to'      => $to,
            'state'   => $curDay > $to ? 'past' : ($curDay >= $from ? 'current' : 'future'),
            'good_days' => $done,
            'crisis'  => array_values(array_filter(cfg('engine.crisis_days', []), fn($d) => $d >= $from && $d <= $to)),
            'checkpoint' => $i * $len,
        ];
    }
    return $out;
}
