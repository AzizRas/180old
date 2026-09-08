<?php
/* ============================================================
   RPG-СЛОЙ

   Это полноценная ролевая обвязка, но с одним жёстким правилом:
   НИ ОДНО игровое действие нельзя выполнить внутри игры.
   Локация открывается не «за прохождение уровня», а за реальный
   результат: серию дней, характеристику героя, явку на встречи,
   возвращение после срыва.

   Поэтому здесь нет ни боёв, ни кликеров, ни энергии за просмотр
   рекламы. Игра — это язык, на котором приложение рассказывает
   человеку, что с ним происходит в жизни.
   ============================================================ */

/* ---------- Классы ---------- */
function rpg_classes(string $lang = 'ru'): array {
    $c = [
        'athlete' => ['icon' => '🥊', 'ru' => 'Атлет',     'uz' => 'Atlet',
                      'ru_d' => 'Растёт от тела и режима', 'uz_d' => 'Tana va tartibdan o\'sadi', 'stat' => 'health'],
        'thinker' => ['icon' => '📖', 'ru' => 'Мыслитель', 'uz' => 'Mutafakkir',
                      'ru_d' => 'Растёт от знаний',        'uz_d' => 'Bilimdan o\'sadi',          'stat' => 'mind'],
        'builder' => ['icon' => '⚒', 'ru' => 'Строитель',  'uz' => 'Quruvchi',
                      'ru_d' => 'Растёт от дела',          'uz_d' => 'Ishdan o\'sadi',            'stat' => 'business'],
        'wanderer'=> ['icon' => '🕯', 'ru' => 'Странник',  'uz' => 'Sayyoh',
                      'ru_d' => 'Растёт от внутренней работы', 'uz_d' => 'Ichki ishdan o\'sadi',  'stat' => 'soul'],
        'keeper'  => ['icon' => '🛡', 'ru' => 'Хранитель', 'uz' => 'Qo\'riqchi',
                      'ru_d' => 'Растёт от дисциплины',    'uz_d' => 'Intizomdan o\'sadi',        'stat' => 'discipline'],
    ];
    foreach ($c as $k => $v) { $c[$k]['label'] = $v[$lang] ?? $v['ru']; $c[$k]['desc'] = $v[$lang . '_d'] ?? $v['ru_d']; }
    return $c;
}

function rpg_class_of(?array $quest): string {
    if (!$quest) return 'keeper';
    return match ($quest['track'] ?: track_of($quest['category'])) {
        'body'  => 'athlete',
        'mind'  => 'thinker',
        'work'  => 'builder',
        'faith' => 'wanderer',
        default => 'keeper',
    };
}

/* ---------- Карта: 6 регионов по этапам, в каждом 3 локации ----------
   cond: day | streak | stat:имя | meets | comebacks | perfect  */
function rpg_map(string $lang = 'ru'): array {
    $ru = [
      1 => ['name' => 'Пустошь',      'icon' => '🏜', 'sub' => 'Здесь всё начинается', 'places' => [
            ['k' => 'camp',    'i' => '⛺', 'n' => 'Первый привал', 'c' => ['day' => 1],
             'd' => 'Ты пришёл. Этого уже достаточно, чтобы поставить палатку.', 'item' => 'flint'],
            ['k' => 'well',    'i' => '🕳', 'n' => 'Старый колодец', 'c' => ['perfect' => 1],
             'd' => 'Первый день, закрытый полностью.', 'item' => 'flask'],
            ['k' => 'path',    'i' => '🥾', 'n' => 'Тропа',         'c' => ['streak' => 3],
             'd' => 'Три дня подряд — это уже направление, а не случайность.', 'item' => 'boots'],
      ]],
      2 => ['name' => 'Каменный кряж','icon' => '⛰', 'sub' => 'Тут закладывают основу', 'places' => [
            ['k' => 'quarry',  'i' => '🪨', 'n' => 'Каменоломня',   'c' => ['day' => 31],
             'd' => 'Месяц позади. Дальше нужен фундамент, а не рывки.', 'item' => 'hammer'],
            ['k' => 'forge',   'i' => '🔥', 'n' => 'Кузница',       'c' => ['streak' => 7],
             'd' => 'Неделя без разрывов. Привычка начала твердеть.', 'item' => 'ring'],
            ['k' => 'bridge',  'i' => '🌉', 'n' => 'Первый мост',   'c' => ['meets' => 2],
             'd' => 'Ты дважды пришёл к своим. Мост строят вдвоём.', 'item' => 'rope'],
      ]],
      3 => ['name' => 'Река',        'icon' => '🌊', 'sub' => 'Течение набирает силу', 'places' => [
            ['k' => 'ford',    'i' => '💧', 'n' => 'Брод',          'c' => ['day' => 61],
             'd' => 'Два месяца. Здесь обычно уже никого нет.', 'item' => 'compass'],
            ['k' => 'mill',    'i' => '🌾', 'n' => 'Мельница',      'c' => ['stat:discipline' => 200],
             'd' => 'Дисциплина стала механизмом, который работает без тебя.', 'item' => 'grain'],
            ['k' => 'return',  'i' => '🔁', 'n' => 'Камень возврата','c' => ['comebacks' => 1],
             'd' => 'Ты сорвался и вернулся. Это главный камень на всей карте.', 'item' => 'ember'],
      ]],
      4 => ['name' => 'Перевал',     'icon' => '🏔', 'sub' => 'Самый крутой участок', 'places' => [
            ['k' => 'climb',   'i' => '🧗', 'n' => 'Подъём',        'c' => ['day' => 91],
             'd' => 'Экватор пройден. Обратно теперь дальше, чем вперёд.', 'item' => 'pick'],
            ['k' => 'shelter', 'i' => '🏚', 'n' => 'Приют',         'c' => ['streak' => 21],
             'd' => 'Три недели подряд. Тут можно перевести дух.', 'item' => 'lantern'],
            ['k' => 'peak',    'i' => '🚩', 'n' => 'Отметка',       'c' => ['perfect' => 30],
             'd' => 'Тридцать полностью закрытых дней.', 'item' => 'flag'],
      ]],
      5 => ['name' => 'Долина',      'icon' => '🌲', 'sub' => 'Здесь закрепляют', 'places' => [
            ['k' => 'garden',  'i' => '🌳', 'n' => 'Сад',           'c' => ['day' => 121],
             'd' => 'То, что посажено в начале, уже приносит плоды.', 'item' => 'seed'],
            ['k' => 'house',   'i' => '🏠', 'n' => 'Дом',           'c' => ['stat:health' => 400],
             'd' => 'Тело выдерживает нагрузку, которую раньше не выдерживало.', 'item' => 'key'],
            ['k' => 'square',  'i' => '🔔', 'n' => 'Площадь',       'c' => ['meets' => 10],
             'd' => 'Десять встреч. Ты стал частью группы, а не гостем.', 'item' => 'bell'],
      ]],
      6 => ['name' => 'Новая земля', 'icon' => '🌅', 'sub' => 'Тот, кем ты стал', 'places' => [
            ['k' => 'gate',    'i' => '🚪', 'n' => 'Ворота',        'c' => ['day' => 151],
             'd' => 'Последний месяц. Осталось меньше, чем ты уже прошёл.', 'item' => 'seal'],
            ['k' => 'tower',   'i' => '🗼', 'n' => 'Башня',         'c' => ['stat:mind' => 400],
             'd' => 'С этой высоты видно весь путь.', 'item' => 'scroll'],
            ['k' => 'finish',  'i' => '🏛', 'n' => 'Финал',         'c' => ['day' => 180],
             'd' => '180 дней. Ты дошёл.', 'item' => 'crown'],
      ]],
    ];
    $uz = [
      1 => ['name' => "Sahro",       'icon' => '🏜', 'sub' => 'Hammasi shu yerdan boshlanadi'],
      2 => ['name' => "Tosh qiya",   'icon' => '⛰', 'sub' => 'Bu yerda poydevor quyiladi'],
      3 => ['name' => "Daryo",       'icon' => '🌊', 'sub' => 'Oqim kuch to\'playdi'],
      4 => ['name' => "Dovon",       'icon' => '🏔', 'sub' => 'Eng tik joy'],
      5 => ['name' => "Vodiy",       'icon' => '🌲', 'sub' => 'Bu yerda mustahkamlanadi'],
      6 => ['name' => "Yangi yer",   'icon' => '🌅', 'sub' => 'Sen aylangan odam'],
    ];
    if ($lang === 'uz') foreach ($uz as $k => $v) { $ru[$k]['name'] = $v['name']; $ru[$k]['sub'] = $v['sub']; }
    return $ru;
}

/* ---------- Артефакты ---------- */
function rpg_items(string $lang = 'ru'): array {
    $m = [
        'flint'  => ['🪵', 'Огниво',        'Chaqmoqtosh'],
        'flask'  => ['🫗', 'Фляга',          'Idish'],
        'boots'  => ['🥾', 'Разношенные сапоги', 'Etik'],
        'hammer' => ['🔨', 'Молот',          'Bolg\'a'],
        'ring'   => ['💍', 'Кольцо привычки','Odat uzugi'],
        'rope'   => ['🪢', 'Верёвка',        'Arqon'],
        'compass'=> ['🧭', 'Компас',         'Kompas'],
        'grain'  => ['🌾', 'Мера зерна',     'Don o\'lchovi'],
        'ember'  => ['🔥', 'Уголёк возврата','Qaytish cho\'g\'i'],
        'pick'   => ['⛏', 'Ледоруб',        'Cho\'kich'],
        'lantern'=> ['🏮', 'Фонарь',         'Fonar'],
        'flag'   => ['🚩', 'Флаг на отметке','Bayroq'],
        'seed'   => ['🌱', 'Семя',           'Urug\''],
        'key'    => ['🗝', 'Ключ от дома',   'Uy kaliti'],
        'bell'   => ['🔔', 'Колокол',        'Qo\'ng\'iroq'],
        'seal'   => ['🕯', 'Печать',         'Muhr'],
        'scroll' => ['📜', 'Свиток пути',    'Yo\'l o\'rami'],
        'crown'  => ['👑', 'Венец 180',      '180 toji'],
    ];
    $o = [];
    foreach ($m as $k => $v) $o[$k] = ['key' => $k, 'icon' => $v[0], 'label' => $lang === 'uz' ? $v[2] : $v[1]];
    return $o;
}

/* ---------- Состояние ---------- */
function rpg_row(int $uid): array {
    $r = one('SELECT * FROM rpg WHERE user_id=?', [$uid]);
    if (!$r) {
        q('INSERT OR IGNORE INTO rpg(user_id,updated_at) VALUES(?,?)', [$uid, nowIso()]);
        $r = one('SELECT * FROM rpg WHERE user_id=?', [$uid]);
    }
    return $r;
}

/* Проверка условия локации по реальным данным */
function rpg_meets_cond(array $cond, array $ctx): bool {
    foreach ($cond as $k => $v) {
        if ($k === 'day'       && $ctx['day']       < $v) return false;
        if ($k === 'streak'    && $ctx['best']      < $v) return false;
        if ($k === 'meets'     && $ctx['meets']     < $v) return false;
        if ($k === 'comebacks' && $ctx['comebacks'] < $v) return false;
        if ($k === 'perfect'   && $ctx['perfect']   < $v) return false;
        if (str_starts_with($k, 'stat:')) {
            $st = substr($k, 5);
            if (($ctx['stats'][$st] ?? 0) < $v) return false;
        }
    }
    return true;
}

function rpg_cond_text(array $cond, string $lang, array $ctx): string {
    $uz = $lang === 'uz';
    foreach ($cond as $k => $v) {
        if ($k === 'day')       return $uz ? "{$v}-kundan" : "С дня {$v}";
        if ($k === 'streak')    return $uz ? "{$v} kun ketma-ket" : "Серия {$v} " . plural((int) $v, 'день', 'дня', 'дней');
        if ($k === 'meets')     return $uz ? "{$v} ta uchrashuv" : "{$v} " . plural((int) $v, 'встреча', 'встречи', 'встреч');
        if ($k === 'comebacks') return $uz ? "{$v} marta qaytish" : "Вернуться после срыва";
        if ($k === 'perfect')   return $uz ? "{$v} to'liq kun" : "{$v} полных " . plural((int) $v, 'день', 'дня', 'дней');
        if (str_starts_with($k, 'stat:')) {
            $st = substr($k, 5);
            $names = ['health' => 'Здоровье', 'discipline' => 'Дисциплина', 'mind' => 'Разум',
                      'business' => 'Дело', 'soul' => 'Душа'];
            $have = (int) ($ctx['stats'][$st] ?? 0);
            return ($names[$st] ?? $st) . ": {$have} / {$v}";
        }
    }
    return '';
}

function rpg_state(array $u, ?array $quest): array {
    $uid  = (int) $u['id'];
    $lang = $u['lang'];
    $row  = rpg_row($uid);

    $cls = $row['class'] ?: rpg_class_of($quest);
    if ($cls !== $row['class']) { q('UPDATE rpg SET class=? WHERE user_id=?', [$cls, $uid]); $row['class'] = $cls; }

    $h = one('SELECT * FROM hero WHERE user_id=?', [$uid]) ?: [];
    $ctx = [
        'day'       => $quest ? current_day($quest) : 0,
        'best'      => (int) ($h['best_streak'] ?? 0),
        'comebacks' => (int) ($h['comebacks'] ?? 0),
        'meets'     => (int) (one('SELECT COUNT(*) c FROM meeting_rsvp WHERE user_id=? AND attended=1', [$uid])['c'] ?? 0),
        'perfect'   => $quest ? (int) (one('SELECT COUNT(*) c FROM day_stats WHERE quest_id=? AND completion>=0.99',
                                           [$quest['id']])['c'] ?? 0) : 0,
        'stats'     => ['health' => (int) ($h['health'] ?? 0), 'discipline' => (int) ($h['discipline'] ?? 0),
                        'mind' => (int) ($h['mind'] ?? 0), 'business' => (int) ($h['business'] ?? 0),
                        'soul' => (int) ($h['soul'] ?? 0)],
    ];

    $open  = json_decode($row['places'] ?: '[]', true) ?: [];
    $items = json_decode($row['items'] ?: '[]', true) ?: [];
    $newly = [];

    $map = rpg_map($lang);
    $itemDefs = rpg_items($lang);
    $regions = [];
    $curStage = $quest ? stage_of_day($ctx['day']) : 1;

    foreach ($map as $ri => $reg) {
        $places = [];
        foreach ($reg['places'] as $p) {
            $unlocked = rpg_meets_cond($p['c'], $ctx);
            if ($unlocked && !in_array($p['k'], $open, true)) {
                $open[] = $p['k'];
                if (!in_array($p['item'], $items, true)) { $items[] = $p['item']; }
                $newly[] = ['key' => $p['k'], 'icon' => $p['i'], 'name' => $p['n'], 'text' => $p['d'],
                            'item' => $itemDefs[$p['item']] ?? null];
            }
            $places[] = [
                'key'      => $p['k'],
                'icon'     => $p['i'],
                'name'     => $p['n'],
                'desc'     => $p['d'],
                'open'     => $unlocked,
                'cond'     => rpg_cond_text($p['c'], $lang, $ctx),
                'item'     => $itemDefs[$p['item']] ?? null,
                'here'     => $row['at_place'] === $p['k'],
            ];
        }
        $regions[] = [
            'n'      => $ri,
            'name'   => $reg['name'],
            'icon'   => $reg['icon'],
            'sub'    => $reg['sub'],
            'state'  => $ri < $curStage ? 'past' : ($ri === $curStage ? 'current' : 'future'),
            'places' => $places,
            'open'   => count(array_filter($places, fn($x) => $x['open'])),
        ];
    }

    if ($newly) {
        q('UPDATE rpg SET places=?, items=?, updated_at=? WHERE user_id=?',
          [json_encode($open, JSON_UNESCAPED_UNICODE), json_encode($items, JSON_UNESCAPED_UNICODE), nowIso(), $uid]);
        foreach ($newly as $n) {
            q('INSERT OR IGNORE INTO achievements(user_id,key,title,created_at) VALUES(?,?,?,?)',
              [$uid, 'place_' . $n['key'], $n['icon'] . ' ' . $n['name'], nowIso()]);
        }
        q('UPDATE hero SET xp=xp+' . (25 * count($newly)) . ' WHERE user_id=?', [$uid]);
    }

    $classes = rpg_classes($lang);
    return [
        'class'    => ['key' => $cls] + ($classes[$cls] ?? []),
        'classes'  => $classes,
        'regions'  => $regions,
        'items'    => array_values(array_map(fn($k) => $itemDefs[$k] ?? null, $items)),
        'at_place' => $row['at_place'],
        'newly'    => $newly,
        'quests'   => rpg_quests($u, $quest, $lang),
        'progress' => ['open' => count($open), 'total' => 18],
    ];
}

/* Журнал квестов: дневной, недельный, рейд */
function rpg_quests(array $u, ?array $quest, string $lang): array {
    $out = [];
    if (!$quest) return $out;
    $day = current_day($quest);

    $t = all('SELECT status FROM tasks WHERE quest_id=? AND day_no=?', [$quest['id'], $day]);
    $done = count(array_filter($t, fn($x) => $x['status'] === 'done'));
    $out[] = ['type' => 'day', 'icon' => '📜',
              'title' => $lang === 'uz' ? "Bugungi vazifalar" : 'Задания дня',
              'progress' => $done, 'target' => max(1, count($t)),
              'note' => $lang === 'uz' ? "Kunni yopish uchun yarmi yetadi" : 'Чтобы день засчитался, хватит половины'];

    $wc = week_card($quest, $lang);
    if ($wc) {
        $out[] = ['type' => 'week', 'icon' => '🗓',
                  'title' => $wc['focus'] !== '' ? $wc['focus']
                      : ($lang === 'uz' ? "Haftaning maqsadi" : 'Цель недели'),
                  'progress' => $wc['done'], 'target' => 5,
                  'note' => $lang === 'uz' ? "7 kundan 5 tasini yopish" : 'Закрыть 5 дней из 7'];
    }

    $sq = squad_of((int) $u['id']);
    if ($sq) {
        $e = expedition_current((int) $sq['id'], $lang);
        if ($e) $out[] = ['type' => 'raid', 'icon' => '🏕', 'title' => $e['title'],
                          'progress' => $e['progress'], 'target' => $e['target'],
                          'note' => ($lang === 'uz' ? 'Jamoa reydi · ' : 'Рейд группы · ') . $e['reward']];
    }
    return $out;
}

/* Персонаж переходит в открытую локацию */
function rpg_move(array $u, string $place): array {
    $quest = get_quest((int) $u['id']);
    $st = rpg_state($u, $quest);
    $okPlace = false;
    foreach ($st['regions'] as $r) foreach ($r['places'] as $p)
        if ($p['key'] === $place && $p['open']) $okPlace = true;
    if (!$okPlace) fail('locked');
    q('UPDATE rpg SET at_place=?, updated_at=? WHERE user_id=?', [$place, nowIso(), $u['id']]);
    return ['at_place' => $place];
}

/* Где сейчас стоят люди из группы — для «сбора в мире» */
function rpg_squad_places(int $squadId, string $lang): array {
    $rows = all("SELECT u.id,u.name,u.avatar,u.badge,r.at_place,u.last_seen
                 FROM squad_members m JOIN users u ON u.id=m.user_id
                 LEFT JOIN rpg r ON r.user_id=u.id WHERE m.squad_id=?", [$squadId]);
    return array_map(fn($r) => [
        'id' => (int) $r['id'], 'name' => $r['name'],
        'initial' => mb_strtoupper(mb_substr($r['name'] ?: '?', 0, 1)),
        'avatar' => $r['avatar'] ? 'uploads/avatars/' . $r['avatar'] : '',
        'badge' => badge_view((string) $r['badge'], $lang),
        'place' => (string) ($r['at_place'] ?? ''),
        'here' => !empty($r['last_seen']) && (time() - strtotime($r['last_seen'])) < 900,
    ], $rows);
}
