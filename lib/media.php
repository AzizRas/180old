<?php
/* ============================================================
   Фото-журнал и рекламные места.
   ============================================================ */

function photos_dir(): string { return __DIR__ . '/../uploads/photos'; }
function avatars_dir(): string { return __DIR__ . '/../uploads/avatars'; }

/* ---------------- ФОТО ---------------- */
function photo_add(array $u, array $file, int $taskId, string $caption, string $visibility): array {
    if (!rateOk('ph:' . $u['id'], (int) cfg('uploads.per_day', 30), 86400)) fail('too_many');
    if (!in_array($visibility, ['private', 'squad', 'public'], true)) $visibility = 'squad';

    $quest = get_quest((int) $u['id']);
    $day = $quest ? current_day($quest) : 0;

    // задание должно принадлежать этому человеку
    if ($taskId > 0) {
        $t = one('SELECT t.id FROM tasks t JOIN quests q ON q.id=t.quest_id WHERE t.id=? AND q.user_id=?',
                 [$taskId, $u['id']]);
        if (!$t) $taskId = 0;
    }

    try { $name = saveImage($file, photos_dir(), (int) cfg('uploads.max_side', 1400)); }
    catch (Throwable $e) { fail('upload:' . $e->getMessage()); }

    q('INSERT INTO photos(user_id,task_id,day_no,file,caption,visibility,created_at) VALUES(?,?,?,?,?,?,?)',
      [$u['id'], $taskId ?: null, $day, $name, s($caption, 300), $visibility, nowIso()]);
    $id = lastId();

    // в общую ленту — не чаще раза в день, иначе она превращается в спам
    if ($visibility !== 'private' && rateOk('fdph:' . $u['id'] . ':' . today(), 1, 86400))
        feed_add((int) $u['id'], 'photo', []);
    if ($visibility === 'squad' || $visibility === 'public') {
        $sq = squad_of((int) $u['id']);
        if ($sq) squad_post((int) $sq['id'], (int) $u['id'], s($caption, 200) ?: '📷', 'photo', $id);
    }
    q('UPDATE hero SET xp=xp+10 WHERE user_id=?', [$u['id']]);

    $n = (int) (one('SELECT COUNT(*) c FROM photos WHERE user_id=?', [$u['id']])['c'] ?? 0);
    if ($n === 1) q('INSERT OR IGNORE INTO achievements(user_id,key,title,created_at) VALUES(?,?,?,?)',
        [$u['id'], 'photo_first', $u['lang'] === 'uz' ? '📷 Birinchi surat' : '📷 Первое фото', nowIso()]);

    return ['id' => $id, 'url' => 'uploads/photos/' . $name];
}

function photos_mine(int $uid, int $limit = 60): array {
    return array_map(fn($p) => [
        'id' => (int) $p['id'], 'url' => 'uploads/photos/' . $p['file'],
        'day' => (int) $p['day_no'], 'caption' => $p['caption'],
        'visibility' => $p['visibility'], 'status' => $p['status'],
        'ai_note' => $p['ai_note'], 'date' => substr($p['created_at'], 0, 10),
    ], all("SELECT * FROM photos WHERE user_id=? AND status!='removed' ORDER BY id DESC LIMIT ?", [$uid, $limit]));
}

function photo_delete(array $u, int $id): void {
    $p = one('SELECT * FROM photos WHERE id=?', [$id]);
    if (!$p) return;
    if ((int) $p['user_id'] !== (int) $u['id'] && !isStaff($u)) fail('forbidden', 403);
    q("UPDATE photos SET status='removed' WHERE id=?", [$id]);
    @unlink(photos_dir() . '/' . $p['file']);
    q('UPDATE squad_msgs SET hidden=1 WHERE ref_id=? AND kind=\'photo\'', [$id]);
}

function avatar_set(array $u, array $file): string {
    if (!rateOk('av:' . $u['id'], 6, 86400)) fail('too_many');
    try { $name = saveImage($file, avatars_dir(), 480); }
    catch (Throwable $e) { fail('upload:' . $e->getMessage()); }
    $old = (string) $u['avatar'];
    q('UPDATE users SET avatar=? WHERE id=?', [$name, $u['id']]);
    if ($old !== '') @unlink(avatars_dir() . '/' . $old);
    return 'uploads/avatars/' . $name;
}

/* ---------------- РЕКЛАМА ----------------
   Правила площадки, чтобы не убить доверие:
   - не больше одного блока на экран;
   - блок всегда подписан как партнёрский;
   - не показывается в коуче и внутри срыва;
   - таргетинг только по направлению цели, языку и городу —
     никаких персональных данных наружу не уходит.        */
function ad_pick(array $u, string $slot): ?array {
    if (!cfg('ads.enabled', true)) return null;
    $qq = get_quest((int) $u['id']);
    $cat = $qq['category'] ?? '';

    $rows = all("SELECT * FROM ads WHERE active=1 AND slot=?
                 AND (category='' OR category=?)
                 AND (lang='' OR lang=?)
                 AND (city='' OR city=?)", [$slot, $cat, $u['lang'], $u['city']]);
    if (!$rows) return null;

    $total = 0; foreach ($rows as $r) $total += max(1, (int) $r['weight']);
    $pick = random_int(1, $total); $acc = 0; $ad = $rows[0];
    foreach ($rows as $r) { $acc += max(1, (int) $r['weight']); if ($pick <= $acc) { $ad = $r; break; } }

    q('UPDATE ads SET shows=shows+1 WHERE id=?', [$ad['id']]);
    return [
        'id' => (int) $ad['id'], 'title' => $ad['title'], 'text' => $ad['text'],
        'cta' => $ad['cta'] ?: ($u['lang'] === 'uz' ? 'Batafsil' : 'Подробнее'),
        'url' => 'go.php?ad=' . (int) $ad['id'],
        'image' => $ad['image'],
        'label' => $u['lang'] === 'uz' ? 'Hamkor' : 'Партнёр',
    ];
}
