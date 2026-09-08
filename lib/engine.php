<?php
/* ============================================================
   Движок: день программы, очки, герой, серия, срыв, адаптация,
   возврат, достижения.

   Правила очков (важно для честности рейтингов):
     очки дня = интенсивность × выполнение × 20
   Поэтому снизить план ради очков бессмысленно: чем ниже
   интенсивность, тем ниже потолок дня. Зато выполнять
   облегчённый план полностью — выгоднее, чем сорвать тяжёлый.
   ============================================================ */

function current_day(array $quest): int {
    $d = daysBetween($quest['start_date'], today()) + 1;
    return max(1, min((int) cfg('engine.total_days', 180), $d));
}

function get_quest(int $userId): ?array {
    return one("SELECT * FROM quests WHERE user_id=? AND status!='done' ORDER BY id DESC LIMIT 1", [$userId]);
}

/* Пересчитать статистику одного дня */
function recalc_day(array $quest, int $day): array {
    $rows = all('SELECT status, weight FROM tasks WHERE quest_id=? AND day_no=?', [$quest['id'], $day]);
    $total = 0; $done = 0;
    foreach ($rows as $r) {
        $w = max(1, (int) $r['weight']);
        $total += $w;
        if ($r['status'] === 'done') $done += $w;
    }
    $completion = $total > 0 ? round($done / $total, 4) : 0.0;
    $int = (int) $quest['intensity'];
    $points = (int) round($completion * $int * 20);
    $date = dateAdd($quest['start_date'], $day - 1);

    q('INSERT INTO day_stats(quest_id,day_no,date,done,total,completion,intensity,points)
       VALUES(?,?,?,?,?,?,?,?)
       ON CONFLICT(quest_id,day_no) DO UPDATE SET
         done=excluded.done, total=excluded.total, completion=excluded.completion,
         intensity=excluded.intensity, points=excluded.points',
        [$quest['id'], $day, $date, $done, $total, $completion, $int, $points]);

    return ['day' => $day, 'done' => $done, 'total' => $total, 'completion' => $completion, 'points' => $points];
}

/* Заполнить пропущенные прошлые дни нулями, чтобы серия и срывы считались честно */
function sync_days(array $quest): void {
    $cur = current_day($quest);
    $have = [];
    foreach (all('SELECT day_no FROM day_stats WHERE quest_id=?', [$quest['id']]) as $r) $have[(int) $r['day_no']] = 1;
    for ($d = 1; $d < $cur; $d++) {
        if (isset($have[$d])) continue;
        q('INSERT OR IGNORE INTO day_stats(quest_id,day_no,date,done,total,completion,intensity,points)
           VALUES(?,?,?,0,0,0,?,0)',
            [$quest['id'], $d, dateAdd($quest['start_date'], $d - 1), (int) $quest['intensity']]);
    }
}

/* Серия: сколько дней подряд (включая сегодня, если уже есть 50%) */
function calc_streak(array $quest): array {
    $cur = current_day($quest);
    $map = [];
    foreach (all('SELECT day_no, completion FROM day_stats WHERE quest_id=?', [$quest['id']]) as $r)
        $map[(int) $r['day_no']] = (float) $r['completion'];

    $streak = 0;
    for ($d = $cur; $d >= 1; $d--) {
        if (($map[$d] ?? 0) >= 0.5) $streak++;
        elseif ($d === $cur) continue;   // сегодня ещё не закрыт — не рвём серию
        else break;
    }

    // текущий разрыв: сколько подряд «пустых» дней перед сегодня
    $gap = 0;
    for ($d = $cur - 1; $d >= 1; $d--) {
        if (($map[$d] ?? 0) < 0.5) $gap++;
        else break;
    }

    $best = 0; $run = 0;
    for ($d = 1; $d <= $cur; $d++) {
        if (($map[$d] ?? 0) >= 0.5) { $run++; $best = max($best, $run); } else $run = 0;
    }
    return ['streak' => $streak, 'gap' => $gap, 'best' => $best];
}

function week_pct(array $quest): int {
    $cur = current_day($quest);
    $from = max(1, $cur - 6);
    $r = one('SELECT AVG(completion) a FROM day_stats WHERE quest_id=? AND day_no BETWEEN ? AND ?',
             [$quest['id'], $from, $cur]);
    return (int) round(((float) ($r['a'] ?? 0)) * 100);
}

function hero_of(int $userId): array {
    q('INSERT OR IGNORE INTO hero(user_id) VALUES(?)', [$userId]);
    $h = one('SELECT * FROM hero WHERE user_id=?', [$userId]);
    $h['level'] = hero_level((int) $h['xp']);
    return $h;
}
function hero_level(int $xp): int { return (int) floor(sqrt(max(0, $xp) / 60)) + 1; }
function hero_next(int $xp): int { $l = hero_level($xp); return (int) (pow($l, 2) * 60); }

/* Отметить/снять задачу */
function toggle_task(array $u, array $quest, int $taskId, bool $done, string $answer = ''): array {
    $t = one('SELECT * FROM tasks WHERE id=? AND quest_id=?', [$taskId, $quest['id']]);
    if (!$t) fail('task_not_found', 404);

    $cur = current_day($quest);
    if ((int) $t['day_no'] > $cur) fail('future_day');
    // прошлые дни можно закрыть в течение 2 суток — «догнать», но не переписать историю целиком
    if ((int) $t['day_no'] < $cur - 1) fail('too_late');

    $wasDone = $t['status'] === 'done';
    if ($done && !$wasDone) {
        q("UPDATE tasks SET status='done', done_at=?, answer=? WHERE id=?", [nowIso(), s($answer, 2000), $taskId]);
        hero_add($u['id'], $t['stat'], (int) $t['points']);
    } elseif (!$done && $wasDone) {
        q("UPDATE tasks SET status='open', done_at=NULL WHERE id=?", [$taskId]);
        hero_add($u['id'], $t['stat'], -(int) $t['points']);
    }

    $st = recalc_day($quest, (int) $t['day_no']);
    after_day_change($u, $quest);
    return $st;
}

function hero_add(int $userId, string $stat, int $pts): void {
    $allowed = ['health', 'discipline', 'mind', 'business', 'soul'];
    if (!in_array($stat, $allowed, true)) $stat = 'discipline';
    q("UPDATE hero SET {$stat} = MAX(0, {$stat} + ?), xp = MAX(0, xp + ?) WHERE user_id=?", [$pts, $pts, $userId]);
}

/* Всё, что должно случиться после изменения дня: серия, возврат, ачивки */
function after_day_change(array $u, array $quest): void {
    sync_days($quest);
    $s = calc_streak($quest);
    q('UPDATE hero SET streak=?, best_streak=MAX(best_streak, ?) WHERE user_id=?',
       [$s['streak'], $s['best'], $u['id']]);

    $cur = current_day($quest);
    $todayRow = one('SELECT completion FROM day_stats WHERE quest_id=? AND day_no=?', [$quest['id'], $cur]);
    $todayOk  = ((float) ($todayRow['completion'] ?? 0)) >= 0.5;

    // ВОЗВРАТ: был разрыв >= N дней, и сегодня человек снова вышел на 50%
    $gapMin = (int) cfg('engine.comeback_gap', 3);
    if ($todayOk && $s['gap'] >= $gapMin) {
        $h = one('SELECT comebacks, last_comeback FROM hero WHERE user_id=?', [$u['id']]);
        $cool = (int) cfg('engine.comeback_cooldown', 14);
        $okCool = empty($h['last_comeback']) || daysBetween($h['last_comeback'], today()) >= $cool;
        if ($okCool) {
            q('UPDATE hero SET comebacks=comebacks+1, last_comeback=?, xp=xp+40 WHERE user_id=?', [today(), $u['id']]);
            unlock($u, 'comeback_' . ((int) $h['comebacks'] + 1),
                $u['lang'] === 'uz' ? "Qaytish #" . ((int) $h['comebacks'] + 1) : 'Возвращение #' . ((int) $h['comebacks'] + 1));
            coach_say($u['id'],
                $u['lang'] === 'uz'
                    ? "Sen {$s['gap']} kunlik tanaffusdan keyin qaytding. Buni ko'pchilik qilmaydi — aynan shu joyda odamlar tashlab ketadi. Uzilish emas, qaytish hisoblanadi."
                    : "Ты вернулся после перерыва в {$s['gap']} дн. Большинство здесь и останавливается. Считается не срыв, а возвращение.",
                'milestone');
        }
    }

    check_achievements($u, $quest, $s, $cur);

    // вклад в недельную экспедицию группы
    $pct = (int) round(((float) ($todayRow['completion'] ?? 0)) * 100);
    if (function_exists('expedition_contribute')) expedition_contribute((int) $u['id'], $pct);

    // события в общую ленту (без целей и цифр — только факт)
    if ($todayOk) {
        $mark = 'fd:day:' . $u['id'] . ':' . $cur;
        if (rateOk($mark, 1, 86400 * 30)) {
            feed_add((int) $u['id'], 'day', ['n' => $cur]);
            if (($todayRow['completion'] ?? 0) >= 0.999) feed_add((int) $u['id'], 'perfect', []);
        }
    }
    if (in_array($s['streak'], [7, 21, 50, 100], true)) {
        if (rateOk('fd:st:' . $u['id'] . ':' . $s['streak'], 1, 86400 * 60))
            feed_add((int) $u['id'], 'streak', ['n' => $s['streak']]);
    }
}

/* ---------- ОНБОРДИНГ ПЕРВЫХ 7 ДНЕЙ ---------- */
function onboard_state(array $u, int $day): array {
    $row = one('SELECT opened FROM onboarding WHERE user_id=?', [$u['id']]);
    if (!$row) {
        q('INSERT OR IGNORE INTO onboarding(user_id,opened,asked,updated_at) VALUES(?,?,?,?)',
          [$u['id'], '[]', '[]', nowIso()]);
        $row = ['opened' => '[]'];
    }
    $opened = json_decode($row['opened'] ?: '[]', true) ?: [];
    $arc = onboarding_arc($u['lang']);

    $pending = null;
    for ($d = 1; $d <= min(7, $day); $d++) {
        if (!isset($arc[$d])) continue;
        if (in_array($arc[$d]['unlock'], $opened, true)) continue;
        $pending = ['day' => $d] + $arc[$d];
        break;
    }
    $all = isStaff($u);
    return ['opened' => $opened, 'pending' => $pending,
            'tabs'   => unlocked_tabs($day, $all),
            'locked' => locked_tabs($day, $all)];
}

function onboard_open(int $uid, string $unlock): void {
    $row = one('SELECT opened FROM onboarding WHERE user_id=?', [$uid]);
    $o = json_decode($row['opened'] ?? '[]', true) ?: [];
    if (!in_array($unlock, $o, true)) $o[] = $unlock;
    q('INSERT INTO onboarding(user_id,opened,updated_at) VALUES(?,?,?)
       ON CONFLICT(user_id) DO UPDATE SET opened=excluded.opened, updated_at=excluded.updated_at',
      [$uid, json_encode($o, JSON_UNESCAPED_UNICODE), nowIso()]);
    q('UPDATE hero SET xp=xp+15 WHERE user_id=?', [$uid]);
}

function unlock(array $u, string $key, string $title): void {
    q('INSERT OR IGNORE INTO achievements(user_id,key,title,created_at) VALUES(?,?,?,?)',
       [$u['id'], $key, $title, nowIso()]);
}

function check_achievements(array $u, array $quest, array $s, int $cur): void {
    $uz = $u['lang'] === 'uz';
    foreach ([7, 30, 90, 180] as $d) {
        if ($cur >= $d) unlock($u, 'day' . $d, ($uz ? "{$d}-kun" : "День {$d}"));
    }
    foreach ([7, 21, 50, 100] as $n) {
        if ($s['best'] >= $n) unlock($u, 'streak' . $n, $uz ? "{$n} kun ketma-ket" : "Серия: {$n}");
    }
    $good = (int) (one('SELECT COUNT(*) c FROM day_stats WHERE quest_id=? AND completion>=0.99', [$quest['id']])['c'] ?? 0);
    foreach ([1, 10, 50, 100] as $n) {
        if ($good >= $n) unlock($u, 'perfect' . $n, $uz ? "{$n} ta to'liq kun" : "Полных дней: {$n}");
    }
}

function coach_say(int $userId, string $text, string $kind = 'chat', array $options = []): void {
    q('INSERT INTO coach_msgs(user_id,role,text,kind,options,created_at) VALUES(?,?,?,?,?,?)',
       [$userId, 'coach', $text, $kind, $options ? json_encode($options, JSON_UNESCAPED_UNICODE) : '', nowIso()]);
}

/* ---------- Обнаружение отставания ---------- */
function needs_checkin(array $quest): bool {
    $cur = current_day($quest);
    $win = (int) cfg('engine.slip_window', 3);
    if ($cur <= $win) return false;

    $from = max(1, $cur - $win);
    $to   = $cur - 1;
    $r = one('SELECT AVG(completion) a, COUNT(*) c FROM day_stats WHERE quest_id=? AND day_no BETWEEN ? AND ?',
             [$quest['id'], $from, $to]);
    if ((int) ($r['c'] ?? 0) < $win) return false;
    if ((float) ($r['a'] ?? 1) >= (float) cfg('engine.slip_threshold', 0.34)) return false;

    // не спрашиваем чаще раза в 3 дня
    $st = one('SELECT last_checkin FROM coach_state WHERE user_id=?', [$quest['user_id']]);
    if (!empty($st['last_checkin']) && daysBetween($st['last_checkin'], today()) < 3) return false;

    return true;
}

/* ---------- Адаптация плана ---------- */
function adapt_plan(array $u, array $quest, string $reasonId): array {
    $reasons = [];
    foreach (slip_reasons($u['lang']) as $r) $reasons[$r['id']] = $r;
    $r = $reasons[$reasonId] ?? null;
    if (!$r) fail('bad_reason');

    $old = (int) $quest['intensity'];
    $new = $old;

    $st = one('SELECT last_intensity_change FROM coach_state WHERE user_id=?', [$u['id']]);
    $cooldownOk = empty($st['last_intensity_change'])
        || daysBetween($st['last_intensity_change'], today()) >= (int) cfg('engine.intensity_cooldown', 7);

    if (in_array($r['action'], ['reduce', 'pause'], true) && $cooldownOk) {
        $new = max(1, $old - ($r['action'] === 'pause' ? 2 : 1));
    }

    if ($new !== $old) {
        q('UPDATE quests SET intensity=? WHERE id=?', [$new, $quest['id']]);
        q('INSERT INTO coach_state(user_id,last_intensity_change) VALUES(?,?)
           ON CONFLICT(user_id) DO UPDATE SET last_intensity_change=excluded.last_intensity_change',
           [$u['id'], today()]);
        // будущие дни пересобираются с новой интенсивностью
        q('DELETE FROM tasks WHERE quest_id=? AND day_no > ?', [$quest['id'], current_day($quest)]);
        $quest['intensity'] = $new;
    }

    q('INSERT INTO coach_state(user_id,last_checkin) VALUES(?,?)
       ON CONFLICT(user_id) DO UPDATE SET last_checkin=excluded.last_checkin', [$u['id'], today()]);

    // Ответ: ИИ если есть, иначе правило
    $text = null;
    if (ai_enabled() && ai_quota_ok($u['id'])) {
        $stats = quest_stats($u, $quest);
        $mem   = coach_memory_text($u['id']);
        $sys   = coach_system_prompt($u, $quest, $stats, $mem);
        $ask   = "Человек отстаёт последние дни. Причина, которую он выбрал: «{$r['label']}».
Интенсивность плана изменена с {$old} на {$new} из 5.
Ответь ему: признай ситуацию без осуждения, назови КОНКРЕТНО, что именно стало легче, и дай одно минимальное действие на завтра.";
        $text = ai_chat([['role' => 'user', 'content' => $ask]], $sys);
        if ($text) ai_quota_use($u['id']);
    }
    if (!$text) $text = coach_rule_reply($reasonId, $u['lang'], $new, $old);

    q('INSERT INTO coach_msgs(user_id,role,text,kind,created_at) VALUES(?,?,?,?,?)',
       [$u['id'], 'user', $r['label'], 'checkin', nowIso()]);
    coach_say($u['id'], $text, 'adapt');

    coach_memory_add($u['id'], date('Y-m-d') . ': отставание, причина «' . $r['label'] . "», интенсивность {$old}->{$new}");

    return ['intensity' => $new, 'old' => $old, 'text' => $text];
}

/* ---------- Память коуча (компактная, чтобы не жечь токены) ---------- */
function coach_memory(int $userId): array {
    $r = one('SELECT memory FROM coach_state WHERE user_id=?', [$userId]);
    $m = json_decode($r['memory'] ?? '{}', true);
    return is_array($m) ? $m : [];
}
function coach_memory_add(int $userId, string $note): void {
    $m = coach_memory($userId);
    $m['notes'] = $m['notes'] ?? [];
    $m['notes'][] = mb_substr($note, 0, 240);
    if (count($m['notes']) > 25) $m['notes'] = array_slice($m['notes'], -25);
    q('INSERT INTO coach_state(user_id,memory) VALUES(?,?)
       ON CONFLICT(user_id) DO UPDATE SET memory=excluded.memory',
       [$userId, json_encode($m, JSON_UNESCAPED_UNICODE)]);
}
function coach_memory_text(int $userId): string {
    $m = coach_memory($userId);
    $n = $m['notes'] ?? [];
    return $n ? implode("\n", array_slice($n, -12)) : '(пока нет заметок)';
}

/* ---------- Сводка ---------- */
function quest_stats(array $u, array $quest): array {
    sync_days($quest);
    $cur = current_day($quest);
    $s   = calc_streak($quest);
    $st  = stages_def();
    $stage = stage_of_day($cur);
    $tot = one('SELECT SUM(points) p, SUM(done) d, COUNT(*) n FROM day_stats WHERE quest_id=?', [$quest['id']]);
    $doneTasks = (int) (one("SELECT COUNT(*) c FROM tasks WHERE quest_id=? AND status='done'", [$quest['id']])['c'] ?? 0);

    return [
        'day'         => $cur,
        'total_days'  => (int) cfg('engine.total_days', 180),
        'stage'       => $stage,
        'stage_name'  => $st[$stage][$u['lang']] ?? $st[$stage]['ru'],
        'stage_sub'   => $st[$stage][$u['lang'] . '_sub'] ?? $st[$stage]['ru_sub'],
        'streak'      => $s['streak'],
        'best_streak' => $s['best'],
        'gap'         => $s['gap'],
        'week_pct'    => week_pct($quest),
        'points'      => (int) ($tot['p'] ?? 0),
        'tasks_done'  => $doneTasks,
        'comebacks'   => (int) (one('SELECT comebacks FROM hero WHERE user_id=?', [$u['id']])['comebacks'] ?? 0),
        'is_crisis'   => in_array($cur, cfg('engine.crisis_days', []), true),
        'is_checkpoint' => in_array($cur, cfg('engine.checkpoint_days', []), true),
    ];
}

/* История последних N дней для графика */
function history(array $quest, int $n = 30): array {
    $cur = current_day($quest);
    $from = max(1, $cur - $n + 1);
    $rows = all('SELECT day_no, completion, points FROM day_stats WHERE quest_id=? AND day_no BETWEEN ? AND ? ORDER BY day_no',
                [$quest['id'], $from, $cur]);
    $map = [];
    foreach ($rows as $r) $map[(int) $r['day_no']] = $r;
    $out = [];
    for ($d = $from; $d <= $cur; $d++) {
        $out[] = ['day' => $d,
                  'c' => round((float) ($map[$d]['completion'] ?? 0), 2),
                  'p' => (int) ($map[$d]['points'] ?? 0)];
    }
    return $out;
}
