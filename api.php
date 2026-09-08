<?php
/* ============================================================
   Единая точка API: /api.php?a=<действие>
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
require __DIR__ . '/lib/squad.php';
require __DIR__ . '/lib/social.php';
require __DIR__ . '/lib/world.php';
require __DIR__ . '/lib/rpg.php';
require __DIR__ . '/lib/media.php';
require __DIR__ . '/lib/sms.php';

date_default_timezone_set(cfg('timezone', 'UTC'));
header('Cache-Control: no-store');

$a  = $_GET['a'] ?? '';
$in = body();

/* Общий лимит на API, чтобы один клиент не положил хостинг */
if (!rateOk('api:' . clientIp(), (int) cfg('limits.api_per_min', 120), 60)) fail('slow_down', 429);

try {
    switch ($a) {

    /* ================= ВХОД ЧЕРЕЗ TELEGRAM ================= */
    case 'tg_start': {
        if ((string) cfg('tg.token', '') === '' || (string) cfg('tg.username', '') === '') fail('tg_off');
        if (!rateOk('tg:' . clientIp(), 20, 3600)) fail('too_many', 429);
        $n = bin2hex(random_bytes(16));
        q("INSERT INTO tg_login(nonce,state,ip,created_at) VALUES(?,'wait',?,?)", [$n, clientIp(), nowIso()]);
        ok(['nonce' => $n,
            'url' => 'https://t.me/' . trim((string) cfg('tg.username'), '@') . '?start=' . $n]);
    }

    case 'tg_check': {
        $n = s($in['nonce'] ?? '', 64);
        if (!preg_match('/^[a-f0-9]{16,64}$/', $n)) fail('bad');
        $row = one('SELECT * FROM tg_login WHERE nonce=?', [$n]);
        if (!$row) fail('not_found', 404);
        if (strtotime($row['created_at']) < time() - 900) ok(['state' => 'expired']);
        if ($row['state'] !== 'ok' || !$row['user_id']) ok(['state' => $row['state']]);
        $u = one('SELECT * FROM users WHERE id=?', [$row['user_id']]);
        if (!$u || $u['status'] === 'banned') fail('blocked', 403);
        q("UPDATE tg_login SET state='used' WHERE nonce=?", [$n]);
        loginUser((int) $u['id']);
        ok(['state' => 'ok', 'new' => !get_quest((int) $u['id'])]);
    }

    /* ================= ВХОД ПО ТЕЛЕФОНУ ================= */
    case 'otp_start': {
        $phone = normPhone((string) ($in['phone'] ?? ''));
        if ($phone === '') fail('bad_phone');
        $lang = in_array($in['lang'] ?? '', ['ru', 'uz'], true) ? $in['lang'] : cfg('default_lang', 'ru');
        $r = otp_create($phone, $lang);
        if (!($r['ok'] ?? false)) fail($r['error'] ?? 'otp_failed', 429, ['wait' => $r['wait'] ?? 0]);
        $exists = (bool) one('SELECT id FROM users WHERE phone=?', [$phone]);
        ok(['sent' => true, 'wait' => $r['wait'], 'ttl' => $r['ttl'],
            'exists' => $exists, 'masked' => maskPhone($phone),
            'dev_code' => $r['debug']]);
    }

    case 'otp_verify': {
        $phone = normPhone((string) ($in['phone'] ?? ''));
        if ($phone === '') fail('bad_phone');
        if (!otp_check($phone, (string) ($in['code'] ?? ''))) fail('bad_code');

        $u = one('SELECT * FROM users WHERE phone=?', [$phone]);
        if (!$u) {
            $name = s($in['name'] ?? '', 40);
            if ($name === '') $name = 'Участник';
            $lang = in_array($in['lang'] ?? '', ['ru', 'uz'], true) ? $in['lang'] : cfg('default_lang', 'ru');
            $isAdminPhone = normPhone((string) cfg('admin_phone', '')) === $phone;
            $trial = (int) cfg('trial_days', 3);

            q('INSERT INTO users(email,phone,pass_hash,name,lang,role,access_until,created_at)
               VALUES(?,?,?,?,?,?,?,?)',
              ['u' . $phone . '@180kun.local', $phone, '', $name, $lang,
               $isAdminPhone ? 'admin' : 'user',
               $trial > 0 ? dateAdd(today(), $trial) : null, nowIso()]);
            $id = lastId();
            q('INSERT OR IGNORE INTO hero(user_id) VALUES(?)', [$id]);
            q('INSERT OR IGNORE INTO onboarding(user_id,updated_at) VALUES(?,?)', [$id, nowIso()]);
            q('INSERT OR IGNORE INTO world_user(user_id,tiles,updated_at) VALUES(?,?,?)', [$id, '{}', nowIso()]);
            $u = one('SELECT * FROM users WHERE id=?', [$id]);
        }
        if ($u['status'] === 'banned') fail('blocked', 403);
        loginUser((int) $u['id']);
        // Имя спрашиваем только у того, у кого его действительно нет.
        // Всё остальное решает route() на клиенте.
        ok(['new' => trim((string) $u['name']) === '' || $u['name'] === 'Участник']);
    }

    case 'logout': logoutUser(); ok();

    /* ================= ПРОФИЛЬ ================= */
    case 'me': {
        $u = currentUser();
        if (!$u) ok(['auth' => false, 'stats' => public_stats(), 'lang' => cfg('default_lang', 'ru'),
                     'tg' => (string) cfg('tg.username', '') !== '' && (string) cfg('tg.token', '') !== '',
                     'cities' => cfg('cities', [])]);
        $quest = get_quest((int) $u['id']);
        $day   = $quest ? current_day($quest) : 0;
        $ob    = $quest ? onboard_state($u, $day)
                        : ['tabs' => ['today'], 'pending' => null, 'locked' => []];
        $unread = (int) (one('SELECT COUNT(*) c FROM coach_msgs WHERE user_id=? AND role=\'coach\' AND seen=0', [$u['id']])['c'] ?? 0);

        ok([
            'auth' => true,
            'user' => [
                'id' => (int) $u['id'], 'name' => $u['name'], 'phone' => maskPhone((string) $u['phone']),
                'lang' => $u['lang'], 'role' => $u['role'], 'city' => $u['city'], 'bio' => $u['bio'],
                'avatar' => $u['avatar'] ? 'uploads/avatars/' . $u['avatar'] : '',
                'initial' => mb_strtoupper(mb_substr($u['name'] ?: '?', 0, 1)),
                'badge' => badge_view((string) $u['badge'], $u['lang']),
                'access_until' => $u['access_until'],
            ],
            'access'    => hasAccess($u),
            'has_quest' => (bool) $quest,
            'day'       => $day,
            'tabs'      => $ob['tabs'],
            'locked'    => $ob['locked'],
            'tab_why'   => tab_why_all($u['lang']),
            'unlock'    => $ob['pending'],
            'unread'    => $unread,
            'donate'    => cfg('donate'),
            'ai'        => ai_enabled(),
            'tg'        => (string) cfg('tg.username', '') !== '' && (string) cfg('tg.token', '') !== '',
            'stats'     => public_stats(),
        ]);
    }

    case 'cities': {
        $rows = all("SELECT city, COUNT(*) c FROM users WHERE city<>'' AND status='active'
                     GROUP BY city ORDER BY c DESC LIMIT 40");
        ok(['cities' => array_map(fn($r) => ['city' => $r['city'], 'n' => (int) $r['c']], $rows),
            'suggest' => cfg('cities', [])]);
    }

    case 'set_lang': {
        $u = requireUser();
        $lang = in_array($in['lang'] ?? '', ['ru', 'uz'], true) ? $in['lang'] : 'ru';
        q('UPDATE users SET lang=? WHERE id=?', [$lang, $u['id']]);
        ok(['lang' => $lang]);
    }

    case 'profile_save': {
        $u = requireUser();
        $name = s($in['name'] ?? $u['name'], 40);
        if ($name === '') $name = $u['name'];
        $lang = in_array($in['lang'] ?? '', ['ru', 'uz'], true) ? $in['lang'] : $u['lang'];
        q('UPDATE users SET name=?, bio=?, city=?, lang=? WHERE id=?',
          [$name, s($in['bio'] ?? '', 160), s($in['city'] ?? '', 40), $lang, $u['id']]);
        if (($in['city'] ?? '') !== '') factSet((int) $u['id'], 'city', s($in['city'], 40));
        ok(['lang' => $lang]);
    }

    /* Смена языка на ходу. Язык хранится у человека в базе, а не в браузере:
       на нём же коуч пишет и на нём же ИИ формулирует задания. */
    case 'lang_set': {
        $u = requireUser();
        $l = (string) ($in['lang'] ?? '');
        if (!in_array($l, ['ru', 'uz'], true)) fail('bad_lang');
        q('UPDATE users SET lang=? WHERE id=?', [$l, $u['id']]);
        ok(['lang' => $l]);
    }

    case 'avatar': {
        $u = requireUser();
        if (empty($_FILES['file'])) fail('no_file');
        ok(['url' => avatar_set($u, $_FILES['file'])]);
    }

    /* ================= ДОСТУП ================= */
    case 'promo': {
        $u = requireUser();
        if (!rateOk('promo:' . $u['id'], 10, 3600)) fail('too_many', 429);
        $code = strtoupper(s($in['code'] ?? '', 32));
        $p = one('SELECT * FROM promo_codes WHERE code=?', [$code]);
        if (!$p) fail('promo_not_found', 404);
        if ((int) $p['uses'] >= (int) $p['max_uses']) fail('promo_used');
        q('UPDATE promo_codes SET uses=uses+1 WHERE code=?', [$code]);
        ok(['access_until' => grantAccess((int) $u['id'], (int) $p['days']), 'days' => (int) $p['days']]);
    }

    case 'donate_claim': {
        $u = requireUser();
        if (!rateOk('don:' . $u['id'], 5, 3600)) fail('too_many', 429);
        q('INSERT INTO donations(user_id,amount,method,note,status,days,created_at) VALUES(?,?,?,?,?,?,?)',
          [$u['id'], s($in['amount'] ?? '', 40), s($in['method'] ?? '', 40),
           s($in['note'] ?? '', 500), 'pending', (int) cfg('donate.period_days', 30), nowIso()]);
        ok(['id' => lastId()]);
    }

    case 'donations_mine': {
        $u = requireUser();
        ok(['items' => all('SELECT id,amount,method,status,created_at,days FROM donations WHERE user_id=? ORDER BY id DESC LIMIT 20', [$u['id']])]);
    }

    /* ================= СТАРТ ПУТИ (3 вопроса) ================= */
    case 'start_options': {
        $u = requireUser();
        $cats = [];
        foreach (categories_def() as $k => $c) $cats[] = ['value' => $k, 'label' => $c['icon'] . ' ' . ($c[$u['lang']] ?? $c['ru'])];
        ok(['categories' => $cats, 'cities' => cfg('cities', []), 'city' => $u['city'],
            'minutes' => [
                ['value' => '15', 'label' => $u['lang'] === 'uz' ? '15 daqiqa' : '15 минут'],
                ['value' => '30', 'label' => $u['lang'] === 'uz' ? '30 daqiqa' : '30 минут'],
                ['value' => '60', 'label' => $u['lang'] === 'uz' ? '1 soat'    : '1 час'],
                ['value' => '120','label' => $u['lang'] === 'uz' ? '2 soat +'  : '2 часа и больше'],
            ]]);
    }

    case 'start': {
        $u = requireUser();
        if (get_quest((int) $u['id'])) fail('quest_exists');
        $goal = s($in['goal'] ?? '', 200);
        $cat  = (string) ($in['category'] ?? 'discipline');
        $city = s($in['city'] ?? '', 40);
        if ($city !== '') { q('UPDATE users SET city=? WHERE id=?', [$city, $u['id']]); $u['city'] = $city;
                            factSet((int) $u['id'], 'city', $city); }
        if ($goal === '') fail('need_goal');
        if (!isset(categories_def()[$cat])) $cat = 'discipline';

        $answers = [
            ['id' => 'goal', 'value' => $goal],
            ['id' => 'category', 'value' => $cat],
            ['id' => 'minutes', 'value' => (string) clampInt($in['minutes'] ?? 30, 5, 300, 30)],
        ];
        $quest = create_quest((int) $u['id'], $answers, $u['lang']);
        q('UPDATE quests SET track=? WHERE id=?', [track_of($cat), $quest['id']]);
        q('UPDATE users SET onboard_step=1 WHERE id=?', [$u['id']]);

        coach_memory_add((int) $u['id'], 'Цель: ' . $goal . ' (' . $cat . ')');
        feed_add((int) $u['id'], 'joined');
        // первое сообщение коуча — прямо сейчас, чтобы экран не был пустым
        proactive_run((int) $u['id']);

        ok(['quest' => ['id' => (int) $quest['id'], 'title' => $quest['title']]]);
    }

    /* Анкета трека — показывается один раз, на 2-й день */
    case 'intake': {
        $u = requireUser();
        $quest = get_quest((int) $u['id']);
        if (!$quest) fail('no_quest', 409);
        $track = $quest['track'] ?: track_of($quest['category']);
        // В первый день — никаких анкет. Человек только пришёл, ему нужно
        // закрыть одно дело, а не заполнять формы. Спрашиваем со 2-го дня.
        if (current_day($quest) < 2) ok(['form' => null]);
        if ($track === '' || factGet((int) $u['id'], 'intake_' . $track) !== '') ok(['form' => null]);
        ok(['form' => track_intake($track, $u['lang']), 'track' => $track]);
    }

    case 'intake_save': {
        $u = requireUser();
        $quest = get_quest((int) $u['id']);
        if (!$quest) fail('no_quest', 409);
        $track = $quest['track'] ?: track_of($quest['category']);
        foreach ((array) ($in['values'] ?? []) as $k => $v) factSet((int) $u['id'], s((string) $k, 30), s((string) $v, 300));
        factSet((int) $u['id'], 'intake_' . $track, today(), 'system');
        coach_memory_add((int) $u['id'], 'Заполнил анкету трека ' . $track);
        // пересобрать будущие дни с учётом новых данных
        q('DELETE FROM tasks WHERE quest_id=? AND day_no > ?', [$quest['id'], current_day($quest)]);
        ok();
    }

    case 'onboard_seen': {
        $u = requireUser();
        onboard_open((int) $u['id'], s($in['unlock'] ?? '', 20));
        ok();
    }

    case 'drip_answer': {
        $u = requireUser();
        $key = s($in['key'] ?? '', 30);
        $val = s($in['value'] ?? '', 400);
        if ($key === '') fail('bad');
        if ($val !== '') factSet((int) $u['id'], $key, $val);
        drip_mark((int) $u['id'], $key);
        q('INSERT INTO coach_msgs(user_id,role,text,kind,created_at) VALUES(?,?,?,?,?)',
          [$u['id'], 'user', $val ?: '—', 'drip', nowIso()]);
        coach_memory_add((int) $u['id'], $key . ': ' . mb_substr($val, 0, 120));
        ok();
    }

    /* ================= СЕГОДНЯ ================= */
    case 'today': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        $quest = get_quest((int) $u['id']);
        if (!$quest) fail('no_quest', 409);

        $day   = current_day($quest);
        $tasks = ensure_day_tasks($quest, $day, $u['lang']);
        recalc_day($quest, $day);
        after_day_change($u, $quest);

        $stats  = quest_stats($u, $quest);
        $crisis = $stats['is_crisis'] ? crisis_text($day, $u['lang']) : null;
        $yTasks = $day > 1 ? all('SELECT * FROM tasks WHERE quest_id=? AND day_no=?', [$quest['id'], $day - 1]) : [];
        $ob     = onboard_state($u, $day);
        $last   = one("SELECT text,kind,created_at FROM coach_msgs WHERE user_id=? AND role='coach' ORDER BY id DESC LIMIT 1", [$u['id']]);
        $drip   = drip_next($u, $day);

        ok([
            'quest'   => ['id' => (int) $quest['id'], 'title' => $quest['title'],
                          'category' => $quest['category'], 'track' => $quest['track'],
                          'intensity' => (int) $quest['intensity'], 'why' => $quest['why'],
                          'future_self' => $quest['future_self'], 'start_date' => $quest['start_date'],
                          'direction' => direction_of($quest['category'], $u['lang'])],
            'stats'     => $stats,
            'tasks'     => array_map('task_pub', $tasks),
            'yesterday' => array_map('task_pub', $yTasks),
            'crisis'    => $crisis,
            'checkin'   => needs_checkin($quest),
            'hero'      => hero_pub((int) $u['id']),
            'history'   => history($quest, 30),
            'unlock'    => $ob['pending'],
            'tabs'      => $ob['tabs'],
            'locked'    => $ob['locked'],
            'coach_last'=> $last ? ['text' => $last['text'], 'kind' => $last['kind'],
                                    'ago' => ago($last['created_at'], $u['lang'])] : null,
            'drip'      => $drip,
            'ad'        => ad_pick($u, 'today'),
            'week'      => week_card($quest, $u['lang']),
        ]);
    }

    case 'task_toggle': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        $quest = get_quest((int) $u['id']);
        if (!$quest) fail('no_quest', 409);
        $st = toggle_task($u, $quest, (int) ($in['id'] ?? 0), (bool) ($in['done'] ?? false), (string) ($in['answer'] ?? ''));
        ok(['day' => $st, 'hero' => hero_pub((int) $u['id']),
            'stats' => quest_stats($u, get_quest((int) $u['id']))]);
    }

    case 'plan': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        $quest = get_quest((int) $u['id']);
        if (!$quest) fail('no_quest', 409);
        $day = current_day($quest);
        ok(['roadmap' => roadmap($quest, $day, $u['lang']), 'day' => $day,
            'intensity' => (int) $quest['intensity'], 'history' => history($quest, 180),
            'week' => week_card($quest, $u['lang'])]);
    }

    /* ================= КОУЧ ================= */
    case 'coach': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        $quest = get_quest((int) $u['id']);
        $msgs = all('SELECT id,role,text,kind,options,created_at FROM coach_msgs WHERE user_id=? ORDER BY id DESC LIMIT 60', [$u['id']]);
        q("UPDATE coach_msgs SET seen=1 WHERE user_id=? AND role='coach'", [$u['id']]);
        $day = $quest ? current_day($quest) : 0;
        ok(['messages' => array_reverse($msgs),
            'checkin'  => $quest ? needs_checkin($quest) : false,
            'reasons'  => slip_reasons($u['lang']),
            'drip'     => $quest ? drip_next($u, $day) : null,
            'ai'       => ai_enabled()]);
    }

    case 'coach_send': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        $quest = get_quest((int) $u['id']);
        if (!$quest) fail('no_quest', 409);
        if (!rateOk('cs:' . $u['id'], (int) cfg('limits.coach_per_10min', 12), 600)) fail('slow_down', 429);
        $text = s($in['text'] ?? '', 1200);
        if ($text === '') fail('empty');

        q('INSERT INTO coach_msgs(user_id,role,text,kind,created_at) VALUES(?,?,?,?,?)',
          [$u['id'], 'user', $text, 'chat', nowIso()]);

        // 1) фильтр на входе: офтоп и попытки достать промпт не идут в модель
        $guard = coach_guard($text);
        if ($guard !== '') {
            $reply = coach_guard_reply($guard, $u);
            q('UPDATE coach_state SET offtopic=offtopic+1 WHERE user_id=?', [$u['id']]);
            coach_say((int) $u['id'], $reply);
            ok(['reply' => $reply, 'guard' => $guard]);
        }

        $reply = null;
        if (ai_enabled() && ai_quota_ok((int) $u['id'])) {
            $stats = quest_stats($u, $quest);
            $sys = coach_system_prompt($u, $quest, $stats, coach_memory_text((int) $u['id']), facts_block((int) $u['id']));
            $hist = [];
            foreach (array_reverse(all("SELECT role,text FROM coach_msgs WHERE user_id=? AND kind IN ('chat','adapt','drip') ORDER BY id DESC LIMIT 12", [$u['id']])) as $m) {
                $hist[] = ['role' => $m['role'] === 'user' ? 'user' : 'assistant', 'content' => $m['text']];
            }
            $reply = ai_chat($hist, $sys);
            if ($reply) { ai_quota_use((int) $u['id']); $reply = coach_postfilter($reply, $u); }
        }
        if (!$reply) $reply = coach_fallback($u, $quest, $text);

        coach_say((int) $u['id'], $reply);
        ok(['reply' => $reply]);
    }

    case 'coach_reason': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        $quest = get_quest((int) $u['id']);
        if (!$quest) fail('no_quest', 409);
        ok(adapt_plan($u, $quest, s($in['reason'] ?? '', 32)));
    }

    /* ================= ГЕРОЙ ================= */
    case 'hero': {
        $u = requireUser();
        ok(['hero' => hero_pub((int) $u['id']),
            'achievements' => all('SELECT key,title,created_at FROM achievements WHERE user_id=? ORDER BY id DESC', [$u['id']]),
            'me' => user_card((int) $u['id'], $u['lang'], $u)]);
    }

    /* ================= МИР ================= */
    case 'world': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        $sq = squad_of((int) $u['id']);
        $quest = get_quest((int) $u['id']);
        ok(['world' => world_state((int) $u['id'], $u['lang']),
            'rpg' => rpg_state($u, $quest),
            'expedition' => $sq ? expedition_current((int) $sq['id'], $u['lang']) : null,
            'camp' => $sq ? camp_state((int) $sq['id'], $u['lang']) : null,
            'ad' => ad_pick($u, 'world')]);
    }

    case 'world_build': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        ok(['world' => world_build((int) $u['id'], (int) ($in['x'] ?? -1), (int) ($in['y'] ?? -1),
                                   s($in['b'] ?? '', 20), $u['lang'])]);
    }

    case 'rpg_move': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        ok(rpg_move($u, s($in['place'] ?? '', 24)));
    }

    /* ================= ГРУППА ================= */
    case 'squad': {
        $u = requireUser();
        $v = squad_of((int) $u['id']) ? squad_view($u) : null;
        ok(['squad' => $v,
            'chat' => $v ? squad_chat($v['id'], $u['lang']) : [],
            'meetings' => $v ? meetings_list($v['id'], $u) : [],
            'expedition' => $v ? expedition_current($v['id'], $u['lang']) : null,
            'places' => $v ? rpg_squad_places($v['id'], $u['lang']) : [],
            'ad' => ad_pick($u, 'squad')]);
    }
    case 'squad_create': { $u = requireUser(); squad_create($u, (string) ($in['name'] ?? '')); ok(); }
    case 'squad_join':   { $u = requireUser(); squad_join($u, (string) ($in['code'] ?? '')); ok(); }
    case 'squad_auto':   { $u = requireUser(); squad_auto($u); ok(); }
    case 'squad_leave':  { $u = requireUser(); squad_leave($u); ok(); }
    case 'squad_post':   {
        $u = requireUser(); $sq = squad_of((int) $u['id']);
        if (!$sq) fail('no_squad');
        if ($u['status'] === 'shadow') ok();   // теневой режим: пишет только себе
        squad_post((int) $sq['id'], (int) $u['id'], (string) ($in['text'] ?? ''));
        ok();
    }
    case 'squad_nudge': {
        $u = requireUser(); $sq = squad_of((int) $u['id']);
        if (!$sq) fail('no_squad');
        if (!rateOk('nudge:' . $u['id'], 5, 3600)) fail('too_many', 429);
        $t = one('SELECT u.name FROM users u JOIN squad_members m ON m.user_id=u.id WHERE m.squad_id=? AND u.id=?',
                 [$sq['id'], (int) ($in['user_id'] ?? 0)]);
        if (!$t) fail('not_member');
        squad_post((int) $sq['id'], (int) $u['id'],
            $u['lang'] === 'uz' ? "{$t['name']}, seni kutyapmiz. Bugun bitta vazifa ham yetarli."
                                : "{$t['name']}, мы тебя ждём. Сегодня хватит одного задания.", 'system');
        ok();
    }
    case 'squad_board': { requireUser(); ok(['board' => squad_board()]); }

    /* ---- встречи ---- */
    case 'rsvp': {
        $u = requireUser();
        meeting_rsvp((int) ($in['id'] ?? 0), (int) $u['id'], s($in['answer'] ?? 'maybe', 8));
        ok();
    }
    case 'meet_checkin': {
        $u = requireUser();
        ok(meeting_checkin((int) ($in['id'] ?? 0), $u));
    }
    case 'meet_edit': {
        $u = requireUser();
        $sq = squad_of((int) $u['id']);
        if (!$sq || (int) $sq['leader_id'] !== (int) $u['id']) fail('forbidden', 403);
        $id = (int) ($in['id'] ?? 0);
        $m = one('SELECT * FROM meetings WHERE id=? AND squad_id=?', [$id, $sq['id']]);
        if (!$m) fail('not_found', 404);
        q('UPDATE meetings SET kind=?, place=?, link=? WHERE id=?',
          [in_array($in['kind'] ?? '', ['online', 'offline'], true) ? $in['kind'] : $m['kind'],
           s($in['place'] ?? '', 120), s($in['link'] ?? '', 200), $id]);
        ok();
    }

    /* ================= ФОТО ================= */
    case 'photos': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        ok(['photos' => photos_mine((int) $u['id'])]);
    }
    case 'photo_add': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        if (empty($_FILES['file'])) fail('no_file');
        ok(photo_add($u, $_FILES['file'], (int) ($_POST['task_id'] ?? 0),
                     (string) ($_POST['caption'] ?? ''), (string) ($_POST['visibility'] ?? 'squad')));
    }
    case 'photo_delete': { $u = requireUser(); photo_delete($u, (int) ($in['id'] ?? 0)); ok(); }
    case 'photos_review': {
        $u = requireUser();
        if (!hasAccess($u)) fail('no_access', 402);
        if (!rateOk('phrev:' . $u['id'], 2, 86400)) fail('too_many', 429);
        $t = photos_review((int) $u['id']);
        if ($t) coach_say((int) $u['id'], $t, 'milestone');
        ok(['text' => $t]);
    }

    /* ================= ЛЮДИ ================= */
    case 'people': {
        $u = requireUser();
        ok(['people' => people_list($u, s($in['filter'] ?? 'active', 12)),
            'stats' => public_stats()]);
    }
    case 'feed': {
        $u = requireUser();
        ok(['feed' => feed_list($u, 40, (int) ($in['before'] ?? 0))]);
    }
    case 'cheer': { $u = requireUser(); ok(cheer((int) $u['id'], (int) ($in['feed_id'] ?? 0))); }
    case 'profile': {
        $u = requireUser();
        $p = user_profile((int) ($in['id'] ?? 0), $u['lang'], $u);
        if (!$p) fail('not_found', 404);
        ok(['profile' => $p, 'reasons' => report_reasons($u['lang']), 'ad' => ad_pick($u, 'profile')]);
    }
    case 'report': {
        $u = requireUser();
        report_create($u, s($in['type'] ?? '', 12), (int) ($in['id'] ?? 0),
                      s($in['reason'] ?? '', 20), (string) ($in['note'] ?? ''));
        ok();
    }

    /* ================= КОНСТИТУЦИЯ И ШЕРИНГ ================= */
    case 'constitution': {
        $u = currentUser();
        $lang = $u ? $u['lang'] : (in_array($in['lang'] ?? '', ['ru', 'uz'], true) ? $in['lang'] : cfg('default_lang', 'ru'));
        ok(['c' => constitution($lang), 'stats' => public_stats(),
            'tg' => (string) cfg('tg.username', '') !== '' && (string) cfg('tg.token', '') !== '']);
    }

    case 'share': {
        $u = requireUser();
        $quest = get_quest((int) $u['id']);
        if (!$quest) fail('no_quest', 409);
        $st = quest_stats($u, $quest);
        $sq = squad_of((int) $u['id']);
        ok(['card' => [
            'day' => $st['day'], 'total' => $st['total_days'], 'tasks_done' => $st['tasks_done'],
            'comebacks' => $st['comebacks'], 'streak' => $st['best_streak'], 'points' => $st['points'],
            'squad' => $sq['name'] ?? '', 'name' => $u['name'],
            'direction' => direction_of($quest['category'], $u['lang'])['label'],
        ]]);
    }

    default: fail('unknown_action', 404);
    }
} catch (Throwable $e) {
    error_log('[180kun] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    fail(cfg('debug', false) ? ('server_error: ' . $e->getMessage()) : 'server_error', 500);
}

/* ---------------- вспомогательные ---------------- */
function task_pub(array $t): array {
    return [
        'id' => (int) $t['id'], 'day' => (int) $t['day_no'], 'title' => $t['title'],
        'stat' => $t['stat'], 'points' => (int) $t['points'], 'verify' => $t['verify'],
        'done' => $t['status'] === 'done', 'answer' => $t['answer'],
    ];
}

function hero_pub(int $userId): array {
    $h = hero_of($userId);
    $xp = (int) $h['xp'];
    return [
        'health' => (int) $h['health'], 'discipline' => (int) $h['discipline'],
        'mind' => (int) $h['mind'], 'business' => (int) $h['business'], 'soul' => (int) $h['soul'],
        'xp' => $xp, 'level' => hero_level($xp), 'next' => hero_next($xp),
        'streak' => (int) $h['streak'], 'best_streak' => (int) $h['best_streak'],
        'comebacks' => (int) $h['comebacks'],
    ];
}

function coach_fallback(array $u, array $quest, string $text): string {
    $st  = quest_stats($u, $quest);
    $uz  = $u['lang'] === 'uz';
    $low = mb_strtolower($text);

    $red = ['не хочу жить', 'покончить', 'суицид', 'себе навредить', 'убить себя',
            "o'zimni o'ldir", 'yashagim kelmayapti', 'смысла нет жить'];
    foreach ($red as $w) if (mb_strpos($low, $w) !== false) {
        return $uz
            ? "Bu dasturdan muhimroq. Iltimos, hozir yolg'iz qolma: yaqin insonga yoki mutaxassisga murojaat qil. Men shu yerdaman, lekin bunday holatda senga tirik odamning yordami kerak."
            : "Это важнее любой программы. Пожалуйста, не оставайся сейчас один: свяжись с близким человеком или со специалистом. Я рядом, но в таком состоянии нужна помощь живого человека.";
    }
    if ($st['gap'] >= 3) {
        return $uz
            ? "Sen {$st['gap']} kun yo'q eding — va qaytding. Aynan shu muhim. Bugun bitta vazifani bajar."
            : "Тебя не было {$st['gap']} дн. — и ты вернулся. Важно именно это. Сегодня сделай одно задание, этого достаточно.";
    }
    if ($st['week_pct'] < 40) {
        return $uz
            ? "Bu haftada {$st['week_pct']}% bajarilgan. Bu reja og'irligining signali. Sababni tanlasang, rejani yengillashtiraman."
            : "За неделю выполнено {$st['week_pct']}%. Это сигнал о тяжести плана, а не о твоей слабости. Выбери причину — я упрощу план.";
    }
    if ($st['streak'] >= 7) {
        return $uz
            ? "{$st['streak']} kun ketma-ket — bu allaqachon odat. Asosiy xavf: rejani o'zing og'irlashtirish."
            : "{$st['streak']} дн. подряд — это уже привычка. Главный риск сейчас: самому усложнить план.";
    }
    return $uz
        ? "Bugun {$st['day']}-kun, bosqich {$st['stage_name']}. Haftalik bajarish {$st['week_pct']}%. Nima qiyinligini yoz — rejani moslayman."
        : "Сегодня день {$st['day']}, этап {$st['stage_name']}. Выполнение за неделю — {$st['week_pct']}%. Напиши, что мешает, и я подстрою план.";
}
