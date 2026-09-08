<?php
/* ============================================================
   Фоновые задачи. Запускать раз в 10–15 минут:
     cron.php   (из панели хостинга)  или
     curl -s https://180kun.online/cron.php?key=СЕКРЕТ

   Здесь живёт всё, что не должно держать веб-процесс:
   проактивные сообщения коуча, напоминания о встречах,
   закрытие экспедиций, чистка мусора.
   ============================================================ */
declare(strict_types=1);
define('L180_CRON', 1);   // ИИ здесь можно ждать дольше, чем в вебе
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

date_default_timezone_set(cfg('timezone', 'UTC'));
$cli = PHP_SAPI === 'cli';
if (!$cli) {
    $key = (string) cfg('cron_key', '');
    if ($key === '' || ($_GET['key'] ?? '') !== $key) { http_response_code(403); exit('forbidden'); }
    header('Content-Type: text/plain; charset=utf-8');
}
@set_time_limit(110);
$started = time();
$log = [];

/* Чтобы два запуска не работали одновременно */
$lock = __DIR__ . '/data/cron.lock';
$fh = fopen($lock, 'c');
if (!$fh || !flock($fh, LOCK_EX | LOCK_NB)) { echo "busy\n"; exit; }

/* --- 1. Проактивные сообщения коуча --- */
$limit = (int) cfg('cron.proactive_per_run', 25);
$done = 0;
$rows = all("SELECT u.id FROM users u
             JOIN quests q ON q.user_id=u.id AND q.status='active'
             LEFT JOIN coach_state c ON c.user_id=u.id
             WHERE u.status='active'
               AND (u.access_until IS NULL OR u.access_until>=?)
               AND (c.last_proactive IS NULL OR substr(c.last_proactive,1,10) < ?)
             ORDER BY u.last_seen DESC LIMIT ?", [today(), today(), $limit]);
foreach ($rows as $r) {
    if (time() - $started > 80) break;
    try { if (proactive_run((int) $r['id'])) $done++; }
    catch (Throwable $e) { error_log('[cron proactive] ' . $e->getMessage()); }
}
$log[] = "proactive: {$done}";

/* --- 1б. Персональные задания на следующую неделю ---
   Один вызов модели на человека в неделю. Считается заранее,
   поэтому в вебе задержки нет вообще. */
$plans = 0;
$rows = all("SELECT u.id uid, q.id qid FROM users u
             JOIN quests q ON q.user_id=u.id AND q.status='active'
             WHERE u.status='active' AND (u.access_until IS NULL OR u.access_until>=?)
             ORDER BY u.last_seen DESC LIMIT ?",
            [today(), (int) cfg('planner.per_cron_run', 12)]);
foreach ($rows as $r) {
    if (time() - $started > 95) break;
    try {
        $uu = one('SELECT * FROM users WHERE id=?', [$r['uid']]);
        $qq = one('SELECT * FROM quests WHERE id=?', [$r['qid']]);
        if ($uu && $qq) $plans += ensure_weeks($uu, $qq, (int) cfg('planner.weeks_ahead', 1));
    } catch (Throwable $e) { error_log('[cron planner] ' . $e->getMessage()); }
}
$log[] = "week_plans: {$plans}";

/* --- 2. Напоминания о встречах (за 2 часа) --- */
$soon = all("SELECT * FROM meetings WHERE state='planned'
             AND starts_at BETWEEN ? AND ?",
            [date('Y-m-d H:i:s'), date('Y-m-d H:i:s', time() + 7200)]);
$rem = 0;
foreach ($soon as $m) {
    if (!rateOk('meetrem:' . $m['id'], 1, 86400)) continue;
    $lang = (string) (one('SELECT lang FROM squads WHERE id=?', [$m['squad_id']])['lang'] ?? 'ru');
    squad_post((int) $m['squad_id'], (int) (one('SELECT leader_id FROM squads WHERE id=?', [$m['squad_id']])['leader_id'] ?? 0),
        ($lang === 'uz' ? '📅 2 soatdan keyin uchrashuv: ' : '📅 Через 2 часа встреча: ') . $m['title'], 'system');
    $rem++;
}
$log[] = "meeting_reminders: {$rem}";

/* --- 3. Закрытие прошедших встреч и экспедиций --- */
q("UPDATE meetings SET state='done' WHERE state='planned' AND starts_at < ?", [date('Y-m-d H:i:s', time() - 7200)]);
$lost = all("SELECT * FROM expeditions WHERE state='active' AND ends_on < ?", [today()]);
foreach ($lost as $e) {
    q("UPDATE expeditions SET state=? WHERE id=?",
      [(int) $e['progress'] >= (int) $e['target'] ? 'won' : 'lost', $e['id']]);
}
$log[] = 'expeditions_closed: ' . count($lost);

/* --- 4. Досоздание встреч на следующую неделю --- */
foreach (all('SELECT id, lang FROM squads') as $sq) meetings_ensure((int) $sq['id'], $sq['lang']);

/* --- 5. Уборка --- */
q("DELETE FROM otp WHERE created_at < ?", [date('Y-m-d H:i:s', time() - 86400 * 3)]);
q("DELETE FROM rate WHERE reset_at < ?", [time() - 86400]);
q("DELETE FROM jobs WHERE state='done' AND done_at < ?", [date('Y-m-d H:i:s', time() - 86400 * 7)]);
q("DELETE FROM tg_login WHERE created_at < ?", [date('Y-m-d H:i:s', time() - 3600)]);
q("DELETE FROM ai_day WHERE used=1 AND day_no < ?", [max(1, (int) 0)]);

flock($fh, LOCK_UN); fclose($fh);
echo implode("\n", $log) . "\nok " . (time() - $started) . "s\n";
