<?php
/* SQLite: подключение + схема. Все изменения схемы идемпотентны,
   поэтому обновление поверх старой базы безопасно. */

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $dir = defined('L180_DATA_DIR') ? L180_DATA_DIR : __DIR__ . '/../data';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $file = $dir . '/level180.sqlite';

    $pdo = new PDO('sqlite:' . $file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode=WAL');       // читатели не блокируют писателей
    $pdo->exec('PRAGMA synchronous=NORMAL');
    $pdo->exec('PRAGMA foreign_keys=ON');
    $pdo->exec('PRAGMA busy_timeout=8000');
    $pdo->exec('PRAGMA cache_size=-8000');

    db_migrate($pdo);
    return $pdo;
}

function db_migrate(PDO $p): void {
    $p->exec("
    /* ---------- ЛЮДИ ---------- */
    CREATE TABLE IF NOT EXISTS users (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        email         TEXT UNIQUE,
        phone         TEXT UNIQUE,
        pass_hash     TEXT NOT NULL DEFAULT '',
        name          TEXT NOT NULL DEFAULT '',
        lang          TEXT NOT NULL DEFAULT 'ru',
        city          TEXT NOT NULL DEFAULT '',
        bio           TEXT NOT NULL DEFAULT '',
        avatar        TEXT NOT NULL DEFAULT '',
        role          TEXT NOT NULL DEFAULT 'user',      -- user | leader | moder | admin
        badge         TEXT NOT NULL DEFAULT '',          -- ключ метки из badge_catalog
        badge_note    TEXT NOT NULL DEFAULT '',
        access_until  TEXT,
        onboard_step  INTEGER NOT NULL DEFAULT 0,
        created_at    TEXT NOT NULL,
        last_seen     TEXT,
        status        TEXT NOT NULL DEFAULT 'active'     -- active | banned | shadow
    );
    CREATE INDEX IF NOT EXISTS idx_users_seen ON users(last_seen);

    /* Одноразовые коды подтверждения телефона */
    CREATE TABLE IF NOT EXISTS otp (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        phone       TEXT NOT NULL,
        code_hash   TEXT NOT NULL,
        purpose     TEXT NOT NULL DEFAULT 'login',
        attempts    INTEGER NOT NULL DEFAULT 0,
        sent_via    TEXT NOT NULL DEFAULT '',
        ip          TEXT NOT NULL DEFAULT '',
        expires_at  TEXT NOT NULL,
        used_at     TEXT,
        created_at  TEXT NOT NULL
    );
    CREATE INDEX IF NOT EXISTS idx_otp_phone ON otp(phone, created_at);

    /* Вход через Telegram-бот: одноразовые ссылки */
    CREATE TABLE IF NOT EXISTS tg_login (
        nonce       TEXT PRIMARY KEY,
        user_id     INTEGER,
        phone       TEXT NOT NULL DEFAULT '',
        chat_id     TEXT NOT NULL DEFAULT '',
        state       TEXT NOT NULL DEFAULT 'wait',   -- wait | ok | used
        ip          TEXT NOT NULL DEFAULT '',
        created_at  TEXT NOT NULL
    );

    /* Универсальный счётчик для лимитов (OTP, API, загрузки) */
    CREATE TABLE IF NOT EXISTS rate (
        bucket    TEXT PRIMARY KEY,
        hits      INTEGER NOT NULL DEFAULT 0,
        reset_at  TEXT NOT NULL
    );

    /* ---------- ДОСТУП ---------- */
    CREATE TABLE IF NOT EXISTS donations (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        amount      TEXT NOT NULL DEFAULT '',
        method      TEXT NOT NULL DEFAULT '',
        note        TEXT NOT NULL DEFAULT '',
        proof       TEXT NOT NULL DEFAULT '',
        status      TEXT NOT NULL DEFAULT 'pending',
        days        INTEGER NOT NULL DEFAULT 30,
        created_at  TEXT NOT NULL,
        decided_at  TEXT
    );

    CREATE TABLE IF NOT EXISTS promo_codes (
        code        TEXT PRIMARY KEY,
        days        INTEGER NOT NULL DEFAULT 30,
        max_uses    INTEGER NOT NULL DEFAULT 1,
        uses        INTEGER NOT NULL DEFAULT 0,
        note        TEXT NOT NULL DEFAULT '',
        created_at  TEXT NOT NULL
    );

    /* ---------- ПУТЬ ---------- */
    CREATE TABLE IF NOT EXISTS quests (
        id             INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id        INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        title          TEXT NOT NULL,
        category       TEXT NOT NULL,
        track          TEXT NOT NULL DEFAULT '',
        why            TEXT NOT NULL DEFAULT '',
        obstacle       TEXT NOT NULL DEFAULT '',
        future_self    TEXT NOT NULL DEFAULT '',
        minutes_day    INTEGER NOT NULL DEFAULT 30,
        intensity      INTEGER NOT NULL DEFAULT 3,
        start_date     TEXT NOT NULL,
        status         TEXT NOT NULL DEFAULT 'active',
        source         TEXT NOT NULL DEFAULT 'template',
        created_at     TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS tasks (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        quest_id    INTEGER NOT NULL REFERENCES quests(id) ON DELETE CASCADE,
        day_no      INTEGER NOT NULL,
        title       TEXT NOT NULL,
        stat        TEXT NOT NULL DEFAULT 'discipline',
        points      INTEGER NOT NULL DEFAULT 10,
        verify      TEXT NOT NULL DEFAULT 'self',
        payload     TEXT NOT NULL DEFAULT '',
        weight      INTEGER NOT NULL DEFAULT 1,
        status      TEXT NOT NULL DEFAULT 'open',
        done_at     TEXT,
        answer      TEXT NOT NULL DEFAULT ''
    );
    CREATE INDEX IF NOT EXISTS idx_tasks_day ON tasks(quest_id, day_no);

    CREATE TABLE IF NOT EXISTS ai_tasks (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        quest_id    INTEGER NOT NULL REFERENCES quests(id) ON DELETE CASCADE,
        stage       INTEGER NOT NULL DEFAULT 1,
        title       TEXT NOT NULL,
        stat        TEXT NOT NULL DEFAULT 'discipline',
        points      INTEGER NOT NULL DEFAULT 12,
        verify      TEXT NOT NULL DEFAULT 'self',
        min_int     INTEGER NOT NULL DEFAULT 1
    );
    CREATE INDEX IF NOT EXISTS idx_aitasks ON ai_tasks(quest_id, stage);

    /* Недельный пакет заданий, сгенерированный ИИ персонально */
    CREATE TABLE IF NOT EXISTS ai_week (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        quest_id    INTEGER NOT NULL REFERENCES quests(id) ON DELETE CASCADE,
        week_no     INTEGER NOT NULL,
        day_from    INTEGER NOT NULL,
        day_to      INTEGER NOT NULL,
        focus       TEXT NOT NULL DEFAULT '',
        note        TEXT NOT NULL DEFAULT '',
        source      TEXT NOT NULL DEFAULT 'ai',
        created_at  TEXT NOT NULL,
        UNIQUE(quest_id, week_no)
    );

    CREATE TABLE IF NOT EXISTS ai_day (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        quest_id    INTEGER NOT NULL REFERENCES quests(id) ON DELETE CASCADE,
        week_no     INTEGER NOT NULL,
        day_no      INTEGER NOT NULL,
        title       TEXT NOT NULL,
        stat        TEXT NOT NULL DEFAULT 'discipline',
        points      INTEGER NOT NULL DEFAULT 12,
        verify      TEXT NOT NULL DEFAULT 'self',
        used        INTEGER NOT NULL DEFAULT 0
    );
    CREATE INDEX IF NOT EXISTS idx_aiday ON ai_day(quest_id, day_no);

    CREATE TABLE IF NOT EXISTS day_stats (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        quest_id      INTEGER NOT NULL REFERENCES quests(id) ON DELETE CASCADE,
        day_no        INTEGER NOT NULL,
        date          TEXT NOT NULL,
        done          INTEGER NOT NULL DEFAULT 0,
        total         INTEGER NOT NULL DEFAULT 0,
        completion    REAL NOT NULL DEFAULT 0,
        intensity     INTEGER NOT NULL DEFAULT 3,
        points        INTEGER NOT NULL DEFAULT 0,
        UNIQUE(quest_id, day_no)
    );

    CREATE TABLE IF NOT EXISTS hero (
        user_id     INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
        health      INTEGER NOT NULL DEFAULT 0,
        discipline  INTEGER NOT NULL DEFAULT 0,
        mind        INTEGER NOT NULL DEFAULT 0,
        business    INTEGER NOT NULL DEFAULT 0,
        soul        INTEGER NOT NULL DEFAULT 0,
        xp          INTEGER NOT NULL DEFAULT 0,
        streak      INTEGER NOT NULL DEFAULT 0,
        best_streak INTEGER NOT NULL DEFAULT 0,
        comebacks   INTEGER NOT NULL DEFAULT 0,
        last_comeback TEXT,
        skin        TEXT NOT NULL DEFAULT 'default'
    );

    /* Факты о человеке, которые собираются ПОСТЕПЕННО, а не анкетой на входе */
    CREATE TABLE IF NOT EXISTS facts (
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        key         TEXT NOT NULL,
        value       TEXT NOT NULL DEFAULT '',
        source      TEXT NOT NULL DEFAULT 'user',   -- user | ai | system
        updated_at  TEXT NOT NULL,
        PRIMARY KEY (user_id, key)
    );

    /* ---------- КОУЧ ---------- */
    CREATE TABLE IF NOT EXISTS coach_msgs (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        role        TEXT NOT NULL,
        text        TEXT NOT NULL,
        kind        TEXT NOT NULL DEFAULT 'chat',
        options     TEXT NOT NULL DEFAULT '',
        seen        INTEGER NOT NULL DEFAULT 0,
        created_at  TEXT NOT NULL
    );
    CREATE INDEX IF NOT EXISTS idx_coach_user ON coach_msgs(user_id, id);

    CREATE TABLE IF NOT EXISTS coach_state (
        user_id       INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
        memory        TEXT NOT NULL DEFAULT '{}',
        last_checkin  TEXT,
        last_intensity_change TEXT,
        last_proactive TEXT,
        msgs_today    INTEGER NOT NULL DEFAULT 0,
        msgs_date     TEXT,
        offtopic      INTEGER NOT NULL DEFAULT 0
    );

    /* Очередь фоновых задач ИИ — чтобы не держать веб-процесс */
    CREATE TABLE IF NOT EXISTS jobs (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        kind        TEXT NOT NULL,
        user_id     INTEGER,
        payload     TEXT NOT NULL DEFAULT '{}',
        state       TEXT NOT NULL DEFAULT 'new',    -- new | running | done | failed
        tries       INTEGER NOT NULL DEFAULT 0,
        error       TEXT NOT NULL DEFAULT '',
        run_after   TEXT NOT NULL,
        created_at  TEXT NOT NULL,
        done_at     TEXT
    );
    CREATE INDEX IF NOT EXISTS idx_jobs_state ON jobs(state, run_after);

    /* ---------- ГРУППЫ ---------- */
    CREATE TABLE IF NOT EXISTS squads (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        name        TEXT NOT NULL,
        code        TEXT UNIQUE NOT NULL,
        leader_id   INTEGER,
        city        TEXT NOT NULL DEFAULT '',
        lang        TEXT NOT NULL DEFAULT 'ru',
        motto       TEXT NOT NULL DEFAULT '',
        created_at  TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS squad_members (
        squad_id    INTEGER NOT NULL REFERENCES squads(id) ON DELETE CASCADE,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        joined_at   TEXT NOT NULL,
        PRIMARY KEY (squad_id, user_id)
    );

    CREATE TABLE IF NOT EXISTS squad_msgs (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        squad_id    INTEGER NOT NULL REFERENCES squads(id) ON DELETE CASCADE,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        text        TEXT NOT NULL,
        kind        TEXT NOT NULL DEFAULT 'chat',   -- chat | system | photo
        ref_id      INTEGER,
        hidden      INTEGER NOT NULL DEFAULT 0,
        created_at  TEXT NOT NULL
    );
    CREATE INDEX IF NOT EXISTS idx_smsg ON squad_msgs(squad_id, id);

    /* Встречи группы — ядро продукта: 2 раза в неделю */
    CREATE TABLE IF NOT EXISTS meetings (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        squad_id    INTEGER NOT NULL REFERENCES squads(id) ON DELETE CASCADE,
        starts_at   TEXT NOT NULL,
        kind        TEXT NOT NULL DEFAULT 'online',  -- online | offline
        title       TEXT NOT NULL DEFAULT '',
        place       TEXT NOT NULL DEFAULT '',
        link        TEXT NOT NULL DEFAULT '',
        agenda      TEXT NOT NULL DEFAULT '',
        state       TEXT NOT NULL DEFAULT 'planned', -- planned | done | cancelled
        auto_kind   INTEGER NOT NULL DEFAULT 1,      -- 1 = формат выбирается автоматически по городу
        created_by  INTEGER,
        created_at  TEXT NOT NULL
    );
    CREATE INDEX IF NOT EXISTS idx_meet ON meetings(squad_id, starts_at);

    CREATE TABLE IF NOT EXISTS meeting_rsvp (
        meeting_id  INTEGER NOT NULL REFERENCES meetings(id) ON DELETE CASCADE,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        answer      TEXT NOT NULL DEFAULT 'maybe',   -- yes | no | maybe
        attended    INTEGER NOT NULL DEFAULT 0,
        updated_at  TEXT NOT NULL,
        PRIMARY KEY (meeting_id, user_id)
    );

    /* ---------- ФОТО-ЖУРНАЛ ---------- */
    CREATE TABLE IF NOT EXISTS photos (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        task_id     INTEGER,
        day_no      INTEGER NOT NULL DEFAULT 0,
        file        TEXT NOT NULL,
        caption     TEXT NOT NULL DEFAULT '',
        visibility  TEXT NOT NULL DEFAULT 'squad',   -- private | squad | public
        status      TEXT NOT NULL DEFAULT 'ok',      -- ok | hidden | removed
        ai_note     TEXT NOT NULL DEFAULT '',
        created_at  TEXT NOT NULL
    );
    CREATE INDEX IF NOT EXISTS idx_photos ON photos(user_id, id);

    /* ---------- СОЦИАЛЬНОЕ ---------- */
    CREATE TABLE IF NOT EXISTS feed (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        type        TEXT NOT NULL,
        payload     TEXT NOT NULL DEFAULT '{}',
        scope       TEXT NOT NULL DEFAULT 'public',  -- public | squad
        created_at  TEXT NOT NULL
    );
    CREATE INDEX IF NOT EXISTS idx_feed ON feed(id DESC);

    CREATE TABLE IF NOT EXISTS cheers (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        from_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        to_id       INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        feed_id     INTEGER,
        created_at  TEXT NOT NULL,
        UNIQUE(from_id, feed_id)
    );

    CREATE TABLE IF NOT EXISTS reports (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        reporter_id  INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        target_type  TEXT NOT NULL,                  -- user | message | photo
        target_id    INTEGER NOT NULL,
        reason       TEXT NOT NULL,
        note         TEXT NOT NULL DEFAULT '',
        status       TEXT NOT NULL DEFAULT 'new',    -- new | resolved | rejected
        action       TEXT NOT NULL DEFAULT '',
        created_at   TEXT NOT NULL,
        resolved_at  TEXT
    );
    CREATE INDEX IF NOT EXISTS idx_reports ON reports(status, id DESC);

    CREATE TABLE IF NOT EXISTS achievements (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        key         TEXT NOT NULL,
        title       TEXT NOT NULL,
        created_at  TEXT NOT NULL,
        UNIQUE(user_id, key)
    );

    /* ---------- МИР ---------- */
    CREATE TABLE IF NOT EXISTS world_user (
        user_id     INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
        tiles       TEXT NOT NULL DEFAULT '{}',
        energy      INTEGER NOT NULL DEFAULT 0,
        spent       INTEGER NOT NULL DEFAULT 0,
        updated_at  TEXT NOT NULL DEFAULT ''
    );

    CREATE TABLE IF NOT EXISTS expeditions (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        squad_id    INTEGER NOT NULL REFERENCES squads(id) ON DELETE CASCADE,
        key         TEXT NOT NULL,
        title       TEXT NOT NULL,
        target      INTEGER NOT NULL DEFAULT 100,
        progress    INTEGER NOT NULL DEFAULT 0,
        starts_on   TEXT NOT NULL,
        ends_on     TEXT NOT NULL,
        state       TEXT NOT NULL DEFAULT 'active',  -- active | won | lost
        reward      TEXT NOT NULL DEFAULT '',
        created_at  TEXT NOT NULL
    );
    CREATE INDEX IF NOT EXISTS idx_exp ON expeditions(squad_id, state);

    CREATE TABLE IF NOT EXISTS expedition_log (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        exp_id      INTEGER NOT NULL REFERENCES expeditions(id) ON DELETE CASCADE,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        amount      INTEGER NOT NULL DEFAULT 0,
        created_at  TEXT NOT NULL
    );

    /* ---------- RPG ---------- */
    CREATE TABLE IF NOT EXISTS rpg (
        user_id     INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
        class       TEXT NOT NULL DEFAULT '',
        places      TEXT NOT NULL DEFAULT '[]',   -- открытые локации
        items       TEXT NOT NULL DEFAULT '[]',   -- артефакты
        at_place    TEXT NOT NULL DEFAULT '',     -- где стоит персонаж
        updated_at  TEXT NOT NULL DEFAULT ''
    );

    /* ---------- РЕКЛАМА ---------- */
    CREATE TABLE IF NOT EXISTS ads (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        slot        TEXT NOT NULL,                  -- today | world | squad | profile
        title       TEXT NOT NULL,
        text        TEXT NOT NULL DEFAULT '',
        cta         TEXT NOT NULL DEFAULT '',
        url         TEXT NOT NULL DEFAULT '',
        image       TEXT NOT NULL DEFAULT '',
        category    TEXT NOT NULL DEFAULT '',       -- пусто = всем
        lang        TEXT NOT NULL DEFAULT '',
        city        TEXT NOT NULL DEFAULT '',
        weight      INTEGER NOT NULL DEFAULT 1,
        active      INTEGER NOT NULL DEFAULT 1,
        shows       INTEGER NOT NULL DEFAULT 0,
        clicks      INTEGER NOT NULL DEFAULT 0,
        created_at  TEXT NOT NULL
    );

    /* ---------- ОНБОРДИНГ ---------- */
    CREATE TABLE IF NOT EXISTS onboarding (
        user_id     INTEGER PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
        opened      TEXT NOT NULL DEFAULT '[]',     -- какие «открытия» первых 7 дней уже показаны
        asked       TEXT NOT NULL DEFAULT '[]',     -- какие вопросы уже заданы по ходу
        updated_at  TEXT NOT NULL
    );
    ");

    db_add_columns($p);

    // Индексы, которые опираются на колонки из db_add_columns.
    // NULL в SQLite уникальности не нарушает — у кого нет Telegram, тому всё равно.
    $p->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_users_tg ON users(tg_id)');
}

/* Добавление колонок в уже существующие базы (обновление без потери данных) */
function db_add_columns(PDO $p): void {
    $want = [
        'users' => [
            'phone' => 'TEXT', 'bio' => "TEXT NOT NULL DEFAULT ''",
            'avatar' => "TEXT NOT NULL DEFAULT ''", 'badge' => "TEXT NOT NULL DEFAULT ''",
            'badge_note' => "TEXT NOT NULL DEFAULT ''", 'onboard_step' => 'INTEGER NOT NULL DEFAULT 0',
            'city' => "TEXT NOT NULL DEFAULT ''",
            'tg_id' => 'TEXT',        // вход через Telegram за одно нажатие
        ],
        'quests'      => ['track' => "TEXT NOT NULL DEFAULT ''"],
        'hero'        => ['skin' => "TEXT NOT NULL DEFAULT 'default'"],
        'coach_msgs'  => ['seen' => 'INTEGER NOT NULL DEFAULT 0'],
        'coach_state' => ['last_proactive' => 'TEXT', 'offtopic' => 'INTEGER NOT NULL DEFAULT 0'],
        'squad_msgs'  => ['kind' => "TEXT NOT NULL DEFAULT 'chat'", 'ref_id' => 'INTEGER',
                          'hidden' => 'INTEGER NOT NULL DEFAULT 0'],
        'squads'      => ['city' => "TEXT NOT NULL DEFAULT ''", 'lang' => "TEXT NOT NULL DEFAULT 'ru'",
                          'motto' => "TEXT NOT NULL DEFAULT ''"],
        'meetings'    => ['auto_kind' => 'INTEGER NOT NULL DEFAULT 1'],
    ];
    foreach ($want as $table => $cols) {
        try { $have = $p->query("PRAGMA table_info({$table})")->fetchAll(); }
        catch (Throwable $e) { continue; }
        if (!$have) continue;
        $names = array_column($have, 'name');
        foreach ($cols as $c => $decl) {
            if (in_array($c, $names, true)) continue;
            try { $p->exec("ALTER TABLE {$table} ADD COLUMN {$c} {$decl}"); } catch (Throwable $e) {}
        }
    }
    // email больше не обязателен — старые базы имели NOT NULL
    try { $p->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_users_phone ON users(phone) WHERE phone IS NOT NULL'); }
    catch (Throwable $e) {}
}

/* --- помощники --- */
function q(string $sql, array $args = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st;
}
function one(string $sql, array $args = []) { $r = q($sql, $args)->fetch(); return $r === false ? null : $r; }
function all(string $sql, array $args = []): array { return q($sql, $args)->fetchAll(); }
function lastId(): int { return (int) db()->lastInsertId(); }
function tx(callable $fn) {
    $p = db();
    $p->beginTransaction();
    try { $r = $fn(); $p->commit(); return $r; }
    catch (Throwable $e) { $p->rollBack(); throw $e; }
}
