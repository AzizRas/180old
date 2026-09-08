<?php
/* ============================================================
   ПЕРСОНАЛЬНЫЙ ГЕНЕРАТОР ЗАДАНИЙ

   Раз в неделю ИИ делает ОДИН вызов на человека и собирает пакет
   заданий на следующие 7 дней лично под него: цель, анкета трека,
   что он реально выполнял и что систематически пропускал,
   интенсивность, этап, город, ответы коучу.

   Дальше день собирается смесью: ~70% заданий из этого пакета,
   ~30% из встроенной библиотеки и базовых привычек. Библиотека
   нужна не «для экономии», а чтобы герой не перекашивался в одну
   характеристику и чтобы при недоступном ИИ день не оказался пустым.

   Один вызов в неделю на человека — это предсказуемые деньги
   (около 1500–2500 токенов) и ноль задержки в вебе: генерация
   идёт в cron.
   ============================================================ */

/* Нормализованный «ключ» задания: первые значимые слова без цифр,
   скобок и хвостов. Нужен, чтобы узнавать одно и то же задание,
   даже если модель переформулировала его иначе. */
function task_key(string $title): string {
    $t = mb_strtolower($title);
    $t = preg_replace('/\([^)]*\)/u', ' ', $t);          // скобки
    $t = preg_replace('/[0-9]+/u', ' ', $t);              // цифры
    $t = preg_replace('/[^\p{L}\s]+/u', ' ', $t);         // знаки
    $words = preg_split('/\s+/u', trim($t), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $words = array_values(array_filter($words, fn($w) => mb_strlen($w) > 2));
    return implode(' ', array_slice($words, 0, 3));
}

function week_of_day(int $day): int { return (int) ceil(max(1, $day) / 7); }
function week_range(int $week): array { return [($week - 1) * 7 + 1, $week * 7]; }

/* Доля заданий от ИИ в дне (остальное — библиотека и базовые привычки) */
function ai_share(): float {
    $v = (float) cfg('planner.ai_share', 0.7);
    return max(0, min(1, $v));
}

/* ---------- Разбор прошедшей недели ----------
   Это и есть «корректировка»: ИИ видит, что не пошло. */
function week_review(array $quest, int $week): array {
    [$from, $to] = week_range($week);

    $days = all('SELECT day_no, completion FROM day_stats WHERE quest_id=? AND day_no BETWEEN ? AND ? ORDER BY day_no',
                [$quest['id'], $from, $to]);
    $avg = 0; $n = 0;
    foreach ($days as $d) { $avg += (float) $d['completion']; $n++; }
    $avg = $n ? $avg / $n : 0;

    // Что делалось стабильно и что игнорировалось.
    // Сравниваем не по точному тексту: ИИ каждый раз формулирует
    // немного иначе, поэтому «Приготовить обед дома» и «Приготовить
    // обед дома вместо доставки» должны считаться одним и тем же.
    $rows = all("SELECT title, status FROM tasks WHERE quest_id=? AND day_no BETWEEN ? AND ?",
                [$quest['id'], $from, $to]);
    $stat = [];
    foreach ($rows as $r) {
        $k = task_key($r['title']);
        if ($k === '') continue;
        $stat[$k] = $stat[$k] ?? ['done' => 0, 'skip' => 0, 'title' => $r['title']];
        if ($r['status'] === 'done') $stat[$k]['done']++;
        else $stat[$k]['skip']++;
    }
    $good = []; $bad = [];
    foreach ($stat as $s) {
        $n = $s['done'] + $s['skip'];
        if ($n >= 2 && $s['done'] >= 2 && $s['done'] > $s['skip']) $good[] = $s['title'];
        // достаточно двух пропусков подряд, либо одного при полном игноре
        if ($s['skip'] >= 2 && $s['skip'] > $s['done']) $bad[] = $s['title'];
    }
    // Если неделя провалена, а «повторяющихся» не нашлось — отдаём просто
    // всё, что осталось незакрытым: модели это тоже полезно.
    if (!$bad && $avg < 0.6) {
        foreach ($stat as $s) if ($s['skip'] > 0 && $s['done'] === 0) $bad[] = $s['title'];
    }

    // подписи к фото за неделю — доказательства того, что человек делает на самом деле
    $ph = all("SELECT caption FROM photos WHERE user_id=? AND day_no BETWEEN ? AND ? AND caption<>'' LIMIT 8",
              [$quest['user_id'], $from, $to]);

    return [
        'avg'      => (int) round($avg * 100),
        'days'     => array_map(fn($d) => (int) round((float) $d['completion'] * 100), $days),
        'good'     => array_slice($good, 0, 8),
        'bad'      => array_slice($bad, 0, 8),
        'photos'   => array_column($ph, 'caption'),
        'has_data' => $n > 0,
    ];
}

/* ---------- Генерация пакета на неделю ---------- */
function generate_week(array $u, array $quest, int $week): bool {
    $total = (int) cfg('engine.total_days', 180);
    [$from, $to] = week_range($week);
    if ($from > $total) return false;
    $to = min($to, $total);

    if (one('SELECT id FROM ai_week WHERE quest_id=? AND week_no=?', [$quest['id'], $week])) return false;

    // Сколько заданий от ИИ нужно на день
    $perDay = max(1, (int) round(tasks_count_for((int) $quest['intensity']) * ai_share()));
    $need   = $perDay * ($to - $from + 1);

    $pack = ai_enabled() ? ai_week_pack($u, $quest, $week, $from, $to, $perDay) : null;

    if (!$pack || empty($pack['tasks'])) {
        // ИИ недоступен — помечаем неделю как шаблонную, день соберётся из библиотеки
        q('INSERT OR IGNORE INTO ai_week(quest_id,week_no,day_from,day_to,focus,note,source,created_at)
           VALUES(?,?,?,?,?,?,?,?)',
          [$quest['id'], $week, $from, $to, '', '', 'template', nowIso()]);
        return false;
    }

    q('INSERT OR IGNORE INTO ai_week(quest_id,week_no,day_from,day_to,focus,note,source,created_at)
       VALUES(?,?,?,?,?,?,?,?)',
      [$quest['id'], $week, $from, $to, s($pack['focus'] ?? '', 200), s($pack['note'] ?? '', 500), 'ai', nowIso()]);

    $cnt = 0;
    foreach ($pack['tasks'] as $t) {
        $day = clampInt($t['day'] ?? 0, $from, $to, $from);
        $title = s($t['title'] ?? '', 160);
        if ($title === '') continue;
        $stat = in_array($t['stat'] ?? '', ['health','discipline','mind','business','soul'], true) ? $t['stat'] : 'discipline';
        $ver  = in_array($t['verify'] ?? '', ['self','number','photo','question'], true) ? $t['verify'] : 'self';
        q('INSERT INTO ai_day(quest_id,week_no,day_no,title,stat,points,verify) VALUES(?,?,?,?,?,?,?)',
          [$quest['id'], $week, $day, $title, $stat, clampInt($t['points'] ?? 12, 5, 30, 12), $ver]);
        if (++$cnt >= $need + 10) break;
    }

    // будущие дни этой недели пересобираются с новыми заданиями
    q('DELETE FROM tasks WHERE quest_id=? AND day_no BETWEEN ? AND ? AND status=\'open\'',
      [$quest['id'], max($from, current_day($quest) + 1), $to]);

    if (!empty($pack['note'])) coach_memory_add((int) $u['id'], 'Неделя ' . $week . ': ' . mb_substr($pack['note'], 0, 160));
    return $cnt > 0;
}

/* Один вызов модели: весь пакет на неделю сразу */
function ai_week_pack(array $u, array $quest, int $week, int $from, int $to, int $perDay): ?array {
    $stage = stage_of_day($from);
    $st    = stages_def()[$stage];
    $rev   = $week > 1 ? week_review($quest, $week - 1) : ['has_data' => false];
    $cat   = categories_def()[$quest['category']] ?? [];
    $lang  = $u['lang'] === 'uz' ? "o'zbek tilida (lotin alifbosida)" : 'на русском языке';
    $facts = facts_block((int) $u['id']);
    $mins  = (int) $quest['minutes_day'];

    $history = '';
    if (!empty($rev['has_data'])) {
        $history  = "\nКАК ПРОШЛА ПРОШЛАЯ НЕДЕЛЯ:\n";
        $history .= "Среднее выполнение: {$rev['avg']}%\n";
        $history .= 'По дням: ' . implode('% ', $rev['days']) . "%\n";
        if ($rev['good']) $history .= "Делал стабильно: " . implode('; ', $rev['good']) . "\n";
        if ($rev['bad'])  $history .= "СИСТЕМАТИЧЕСКИ ПРОПУСКАЛ (не повторяй это в том же виде, замени или упрости): "
                                    . implode('; ', $rev['bad']) . "\n";
        if ($rev['photos']) $history .= "Присылал фото с подписями: " . implode('; ', array_slice($rev['photos'], 0, 5)) . "\n";
    }

    $sys = <<<TXT
Ты составляешь персональные ежедневные задания для приложения «180 kun».
Пиши {$lang}.

ЖЁСТКИЕ ПРАВИЛА:
- Задание — это КОНКРЕТНОЕ действие, которое можно выполнить сегодня и однозначно сказать «сделал / не сделал».
  Плохо: «работать над собой», «питаться правильно». Хорошо: «Приготовить обед дома вместо доставки».
- Задание должно укладываться в {$mins} минут в день суммарно со всеми остальными.
- Никаких медицинских назначений: не указывай калорийность, граммы, дозировки, препараты, схемы лечения,
  темп похудения. Про питание можно говорить только на уровне действий и привычек.
- Не повторяй одну и ту же формулировку несколько раз за неделю. Разнообразь.
- Сложность растёт от начала к концу недели, но не скачком.
- Учитывай, что человек живой: один день в неделю делай заметно легче.
- Ответ строго в JSON.
TXT;

    $ask = <<<TXT
ЧЕЛОВЕК:
Имя: {$u['name']}
Цель: {$quest['title']}
Направление: {$cat['ru']}
Зачем: {$quest['why']}
Город: {$u['city']}
Реально может уделять: {$mins} минут в день
Текущая интенсивность плана: {$quest['intensity']} из 5
{$facts}
{$history}

СЕЙЧАС:
Неделя программы №{$week}. Дни с {$from} по {$to} из 180.
Этап {$stage} — {$st['ru']} ({$st['ru_sub']}).

ЗАДАЧА:
Составь задания на каждый день с {$from} по {$to}: ровно по {$perDay} задания на день.
Если прошлая неделя провалена (ниже 40%) — сделай неделю ЛЕГЧЕ, а не тяжелее.
Если прошлая неделя выполнена выше 80% — добавь одно новое, более сложное действие.

Верни JSON:
{"focus":"одна фраза: на чём фокус этой недели",
 "note":"одна фраза для памяти коуча: что изменил и почему",
 "tasks":[{"day":{$from},"title":"...","stat":"health|discipline|mind|business|soul","points":12,"verify":"self|number|photo|question"}]}
points 5–30 по сложности. verify: number если задание про количество, photo если результат видно на фото,
question если нужен короткий письменный ответ, иначе self.
TXT;

    $raw = ai_chat([['role' => 'user', 'content' => $ask]], $sys, true, 'plan');
    $d = ai_json($raw);
    if (!$d || empty($d['tasks']) || !is_array($d['tasks'])) return null;
    return $d;
}

/* Досоздаёт недели: текущую и следующую. Вызывается из cron и при старте пути. */
function ensure_weeks(array $u, array $quest, int $ahead = 1): int {
    $cur = week_of_day(current_day($quest));
    $made = 0;
    for ($w = $cur; $w <= $cur + $ahead; $w++) {
        if (generate_week($u, $quest, $w)) $made++;
    }
    return $made;
}

/* Задания ИИ, назначенные на конкретный день */
function ai_tasks_for_day(int $questId, int $day, int $limit): array {
    return all('SELECT * FROM ai_day WHERE quest_id=? AND day_no=? ORDER BY id LIMIT ?', [$questId, $day, $limit]);
}

/* Сводка недели для экрана «План» */
function week_card(array $quest, string $lang): ?array {
    $w = week_of_day(current_day($quest));
    $row = one('SELECT * FROM ai_week WHERE quest_id=? AND week_no=?', [$quest['id'], $w]);
    if (!$row) return null;
    [$from, $to] = week_range($w);
    $done = (int) (one('SELECT COUNT(*) c FROM day_stats WHERE quest_id=? AND day_no BETWEEN ? AND ? AND completion>=0.5',
                       [$quest['id'], $from, $to])['c'] ?? 0);
    return [
        'week'   => $w,
        'focus'  => $row['focus'],
        'source' => $row['source'],
        'from'   => $from, 'to' => min($to, (int) cfg('engine.total_days', 180)),
        'done'   => $done,
    ];
}
