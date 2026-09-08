<?php
/* ============================================================
   180 kun — панель управления.
   Донаты, метки (короны), жалобы, реклама, встречи, метрики.
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

date_default_timezone_set(cfg('timezone', 'UTC'));
session_name('l180adm');
session_start();

$err = '';
if (isset($_POST['adm_pass'])) {
    if (!rateOk('adm:' . clientIp(), 10, 900)) { $err = 'Слишком много попыток, подождите'; }
    elseif (hash_equals((string) cfg('admin_password'), (string) $_POST['adm_pass'])) {
        session_regenerate_id(true);
        $_SESSION['adm'] = 1; $_SESSION['csrf'] = bin2hex(random_bytes(16));
        header('Location: admin.php'); exit;
    } else $err = 'Неверный пароль';
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }
$authed = !empty($_SESSION['adm']);
$CSRF = $_SESSION['csrf'] ?? '';

function h($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function csrf() { global $CSRF; return '<input type="hidden" name="csrf" value="' . h($CSRF) . '">'; }

/* ---------- действия ---------- */
if ($authed && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act'])) {
    if (!hash_equals($CSRF, (string) ($_POST['csrf'] ?? ''))) { http_response_code(403); exit('csrf'); }
    $act = $_POST['act'];

    if ($act === 'confirm') {
        $d = one('SELECT * FROM donations WHERE id=?', [(int) $_POST['id']]);
        $days = clampInt($_POST['days'] ?? 30, 1, 400, 30);
        if ($d && $d['status'] === 'pending') {
            q("UPDATE donations SET status='confirmed', days=?, decided_at=? WHERE id=?", [$days, nowIso(), $d['id']]);
            grantAccess((int) $d['user_id'], $days);
            if ($days >= 90) badge_set((int) $d['user_id'], 'donor');
            if ($days >= 180) badge_set((int) $d['user_id'], 'gold');
        }
    }
    elseif ($act === 'reject') q("UPDATE donations SET status='rejected', decided_at=? WHERE id=?", [nowIso(), (int) $_POST['id']]);
    elseif ($act === 'promo') {
        $n = clampInt($_POST['count'] ?? 1, 1, 200, 1);
        $made = [];
        for ($i = 0; $i < $n; $i++) {
            do { $c = '180-' . randCode(5); } while (one('SELECT code FROM promo_codes WHERE code=?', [$c]));
            q('INSERT INTO promo_codes(code,days,max_uses,note,created_at) VALUES(?,?,?,?,?)',
              [$c, clampInt($_POST['days'] ?? 30, 1, 400, 30), clampInt($_POST['uses'] ?? 1, 1, 1000, 1),
               s($_POST['note'] ?? '', 120), nowIso()]);
            $made[] = $c;
        }
        $_SESSION['made'] = $made;
    }
    elseif ($act === 'delpromo') q('DELETE FROM promo_codes WHERE code=?', [(string) $_POST['code']]);
    elseif ($act === 'grant')    grantAccess((int) $_POST['uid'], clampInt($_POST['days'] ?? 30, 1, 400, 30));
    elseif ($act === 'badge')    badge_set((int) $_POST['uid'], s($_POST['badge'] ?? '', 20), s($_POST['note'] ?? '', 60));
    elseif ($act === 'role') {
        $r = in_array($_POST['role'] ?? '', ['user', 'leader', 'moder', 'admin'], true) ? $_POST['role'] : 'user';
        q('UPDATE users SET role=? WHERE id=?', [$r, (int) $_POST['uid']]);
        if ($r === 'moder') badge_set((int) $_POST['uid'], 'moder');
    }
    elseif ($act === 'status') {
        $st = in_array($_POST['status'] ?? '', ['active', 'shadow', 'banned'], true) ? $_POST['status'] : 'active';
        q('UPDATE users SET status=? WHERE id=?', [$st, (int) $_POST['uid']]);
    }
    elseif ($act === 'report') {
        $id = (int) $_POST['id'];
        $r  = one('SELECT * FROM reports WHERE id=?', [$id]);
        $do = (string) ($_POST['do'] ?? '');
        if ($r) {
            if ($do === 'hide') {
                if ($r['target_type'] === 'message') q('UPDATE squad_msgs SET hidden=1 WHERE id=?', [$r['target_id']]);
                if ($r['target_type'] === 'photo')   q("UPDATE photos SET status='hidden' WHERE id=?", [$r['target_id']]);
                if ($r['target_type'] === 'user')    q("UPDATE users SET status='shadow' WHERE id=?", [$r['target_id']]);
            } elseif ($do === 'ban' && $r['target_type'] === 'user') {
                q("UPDATE users SET status='banned' WHERE id=? AND role='user'", [$r['target_id']]);
            } elseif ($do === 'restore') {
                if ($r['target_type'] === 'message') q('UPDATE squad_msgs SET hidden=0 WHERE id=?', [$r['target_id']]);
                if ($r['target_type'] === 'photo')   q("UPDATE photos SET status='ok' WHERE id=?", [$r['target_id']]);
                if ($r['target_type'] === 'user')    q("UPDATE users SET status='active' WHERE id=?", [$r['target_id']]);
            }
            q("UPDATE reports SET status=?, action=?, resolved_at=? WHERE id=?",
              [$do === 'skip' ? 'rejected' : 'resolved', $do, nowIso(), $id]);
        }
    }
    elseif ($act === 'ad_save') {
        $id = (int) ($_POST['id'] ?? 0);
        $f = [s($_POST['slot'] ?? 'today', 20), s($_POST['title'] ?? '', 90), s($_POST['text'] ?? '', 220),
              s($_POST['cta'] ?? '', 40), s($_POST['url'] ?? '', 300), s($_POST['category'] ?? '', 20),
              s($_POST['lang'] ?? '', 5), s($_POST['city'] ?? '', 40), clampInt($_POST['weight'] ?? 1, 1, 100, 1),
              isset($_POST['active']) ? 1 : 0];
        if ($id) { $f[] = $id;
            q('UPDATE ads SET slot=?,title=?,text=?,cta=?,url=?,category=?,lang=?,city=?,weight=?,active=? WHERE id=?', $f); }
        else { $f[] = nowIso();
            q('INSERT INTO ads(slot,title,text,cta,url,category,lang,city,weight,active,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)', $f); }
    }
    elseif ($act === 'ad_del') q('DELETE FROM ads WHERE id=?', [(int) $_POST['id']]);
    elseif ($act === 'photo')  q('UPDATE photos SET status=? WHERE id=?',
        [in_array($_POST['st'] ?? '', ['ok','hidden','removed'], true) ? $_POST['st'] : 'ok', (int) $_POST['id']]);
    elseif ($act === 'broadcast') {
        $txt = s($_POST['text'] ?? '', 800);
        if ($txt !== '') {
            $n = 0;
            foreach (all("SELECT id FROM users WHERE status='active'") as $uu) { coach_say((int) $uu['id'], $txt, 'milestone'); $n++; }
            $_SESSION['flash'] = "Отправлено {$n} участникам";
        }
    }

    header('Location: admin.php' . (isset($_POST['tab']) ? '?tab=' . urlencode((string) $_POST['tab']) : '')); exit;
}

$tab = $_GET['tab'] ?? 'dash';
?><!doctype html>
<html lang="ru"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>180 kun · панель</title>
<link rel="icon" href="assets/mark.svg">
<style>
:root{--bg:#0B0E14;--card:#151C2C;--line:#243049;--txt:#E9EEF8;--dim:#8A97B4;--dim2:#5E6B87;
--acc:#FF5A1F;--ok:#28C486;--bad:#FF4D5E;--warn:#FFB020;--info:#4C9AFF}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--txt);font:14px/1.55 -apple-system,Segoe UI,Roboto,Arial}
.w{max-width:1120px;margin:0 auto;padding:18px 14px 70px}
h1{font-size:19px;font-weight:300;letter-spacing:.05em}h1 b{font-weight:800}
h2{font-size:15px;margin:20px 0 10px}
a{color:var(--acc);text-decoration:none}
.card{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px;margin-bottom:12px}
table{width:100%;border-collapse:collapse;font-size:13px}
th{text-align:left;color:var(--dim);font-weight:600;font-size:10.5px;text-transform:uppercase;
letter-spacing:.07em;padding:8px 6px;border-bottom:1px solid var(--line)}
td{padding:9px 6px;border-bottom:1px solid var(--line);vertical-align:middle}
input,select,textarea,button{font:inherit;color:inherit}
input,select,textarea{background:#0F1520;border:1px solid var(--line);border-radius:8px;padding:8px 10px;max-width:100%}
textarea{width:100%;min-height:60px}
button{background:var(--acc);color:#160B04;border:0;border-radius:8px;padding:8px 13px;font-weight:700;cursor:pointer}
button.sec{background:transparent;border:1px solid var(--line);color:var(--txt)}
button.bad{background:transparent;border:1px solid rgba(255,77,94,.5);color:var(--bad)}
button.ok{background:var(--ok);color:#06231a}
.tabs{display:flex;gap:7px;margin:14px 0 18px;flex-wrap:wrap}
.tabs a{padding:7px 13px;border:1px solid var(--line);border-radius:999px;color:var(--dim);font-weight:600;font-size:13px}
.tabs a.on{background:var(--acc);border-color:var(--acc);color:#160B04}
.pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:10.5px;font-weight:700}
.p-pending,.p-new{background:rgba(255,176,32,.18);color:var(--warn)}
.p-confirmed,.p-resolved{background:rgba(40,196,134,.18);color:var(--ok)}
.p-rejected{background:rgba(255,77,94,.18);color:var(--bad)}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px}
.kpi{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:13px}
.kpi b{display:block;font-size:25px;font-weight:800}
.kpi span{color:var(--dim);font-size:10.5px;text-transform:uppercase;letter-spacing:.06em}
code{background:#0F1520;padding:2px 7px;border-radius:6px;font-size:12.5px}
.muted{color:var(--dim)}.small{font-size:12.5px}
form.inline{display:inline}
.flash{background:rgba(40,196,134,.14);border:1px solid rgba(40,196,134,.4);border-radius:10px;padding:10px 13px;margin-bottom:12px}
.hint{color:var(--dim2);font-size:12.5px;line-height:1.5}
.thumb{width:54px;height:54px;object-fit:cover;border-radius:8px;display:block}
</style></head><body><div class="w">

<?php if (!$authed): ?>
  <h1>180 <b>kun</b> · панель</h1>
  <div class="card" style="max-width:340px;margin-top:30px">
    <?php if ($err): ?><p style="color:var(--bad)"><?= h($err) ?></p><?php endif; ?>
    <form method="post">
      <input type="password" name="adm_pass" placeholder="Пароль" style="width:100%;margin-bottom:10px" autofocus>
      <button style="width:100%">Войти</button>
    </form>
  </div>
<?php else:
  $k = [
    'users'  => (int) one("SELECT COUNT(*) c FROM users WHERE status!='banned'")['c'],
    'active' => (int) one('SELECT COUNT(*) c FROM users WHERE access_until>=?', [today()])['c'],
    'today'  => (int) one('SELECT COUNT(*) c FROM users WHERE last_seen>=?', [today() . ' 00:00:00'])['c'],
    'quests' => (int) one("SELECT COUNT(*) c FROM quests WHERE status='active'")['c'],
    'pend'   => (int) one("SELECT COUNT(*) c FROM donations WHERE status='pending'")['c'],
    'rep'    => (int) one("SELECT COUNT(*) c FROM reports WHERE status='new'")['c'],
    'squads' => (int) one('SELECT COUNT(*) c FROM squads')['c'],
    'photos' => (int) one("SELECT COUNT(*) c FROM photos WHERE status='ok'")['c'],
  ];
?>
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h1>180 <b>kun</b> · панель</h1><a href="?logout=1">выйти</a></div>
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash"><?= h($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <div class="grid">
    <div class="kpi"><b><?= $k['users'] ?></b><span>участников</span></div>
    <div class="kpi"><b><?= $k['today'] ?></b><span>заходили сегодня</span></div>
    <div class="kpi"><b><?= $k['active'] ?></b><span>с доступом</span></div>
    <div class="kpi"><b><?= $k['quests'] ?></b><span>идут путь</span></div>
    <div class="kpi"><b style="color:<?= $k['pend'] ? 'var(--warn)' : 'inherit' ?>"><?= $k['pend'] ?></b><span>донатов ждут</span></div>
    <div class="kpi"><b style="color:<?= $k['rep'] ? 'var(--bad)' : 'inherit' ?>"><?= $k['rep'] ?></b><span>жалоб</span></div>
    <div class="kpi"><b><?= $k['squads'] ?></b><span>групп</span></div>
    <div class="kpi"><b><?= $k['photos'] ?></b><span>фото</span></div>
  </div>

  <div class="tabs">
    <?php foreach (['dash'=>'Метрики','donations'=>'Донаты','users'=>'Участники','reports'=>'Жалобы'.($k['rep']?' •':''),
                    'squads'=>'Группы','photos'=>'Фото','ads'=>'Реклама','promo'=>'Промокоды','tools'=>'Инструменты'] as $tk => $tn): ?>
      <a href="?tab=<?= $tk ?>" class="<?= $tab === $tk ? 'on' : '' ?>"><?= h($tn) ?></a>
    <?php endforeach; ?>
  </div>

<?php if ($tab === 'dash'):
  /* Return Rate — главная метрика продукта */
  $qs = all('SELECT id, start_date FROM quests');
  $b = ['d1'=>0,'d7'=>0,'d30'=>0,'d90'=>0,'d180'=>0]; $slip=0; $ret=0; $tot=count($qs); $sumDay=0;
  foreach ($qs as $qq) {
    $days = daysBetween($qq['start_date'], today()) + 1; $sumDay += min($days,180);
    $map = [];
    foreach (all('SELECT day_no, completion FROM day_stats WHERE quest_id=?', [$qq['id']]) as $g)
      $map[(int) $g['day_no']] = (float) $g['completion'];
    foreach ([1,7,30,90,180] as $d) if ($days >= $d && ($map[$d] ?? 0) >= .5) $b['d'.$d]++;
    $gap=0; $had=false; $came=false;
    for ($d=1; $d<=min($days,180); $d++) {
      if (($map[$d] ?? 0) < .5) { $gap++; if ($gap>=3) $had=true; }
      else { if ($had && $gap>=3) $came=true; $gap=0; }
    }
    if ($had) { $slip++; if ($came) $ret++; }
  }
  $rr = $slip ? round($ret/$slip*100) : 0;
  $meetAtt = (int) one('SELECT COUNT(*) c FROM meeting_rsvp WHERE attended=1')['c'];
  $meetTot = (int) one("SELECT COUNT(*) c FROM meetings WHERE state='done'")['c'];
?>
  <div class="card">
    <h2 style="margin-top:0">Return Rate — главная метрика</h2>
    <p class="hint">Доля тех, кто сорвался на 3+ дня и всё-таки вернулся. Именно она проверяет
      гипотезу продукта. Если ниже 35% — чинить надо диалог адаптации, а не задания.</p>
    <div style="font-size:50px;font-weight:800;color:var(--acc)"><?= $rr ?>%</div>
    <p class="muted small">Срывались: <?= $slip ?> из <?= $tot ?>. Вернулись: <?= $ret ?>.</p>
  </div>
  <div class="card">
    <h2 style="margin-top:0">Воронка удержания</h2>
    <table><tr><th>День</th><th>Закрыли день (≥50%)</th><th>%</th></tr>
      <?php foreach ($b as $kk => $v): ?>
        <tr><td><?= h(strtoupper($kk)) ?></td><td><b><?= $v ?></b> из <?= $tot ?></td>
        <td><?= $tot ? round($v/$tot*100) : 0 ?>%</td></tr>
      <?php endforeach; ?>
    </table>
  </div>
  <div class="card">
    <h2 style="margin-top:0">Встречи</h2>
    <p>Проведено встреч: <b><?= $meetTot ?></b> · отметок присутствия: <b><?= $meetAtt ?></b></p>
    <p class="hint">Явка на встречи — второй по важности показатель после Return Rate.
      Группа, где никто не приходит, разваливается за 2–3 недели.</p>
  </div>

<?php elseif ($tab === 'donations'):
  $rows = all("SELECT d.*, u.name, u.phone FROM donations d JOIN users u ON u.id=d.user_id
               ORDER BY (d.status='pending') DESC, d.id DESC LIMIT 200"); ?>
  <div class="card"><table>
    <tr><th>#</th><th>Кто</th><th>Сумма</th><th>Комментарий</th><th>Когда</th><th>Статус</th><th></th></tr>
    <?php foreach ($rows as $r): ?>
    <tr><td><?= (int) $r['id'] ?></td>
      <td><b><?= h($r['name']) ?></b><br><span class="muted small"><?= h(prettyPhone((string) $r['phone'])) ?></span></td>
      <td><?= h($r['amount']) ?><br><span class="muted small"><?= h($r['method']) ?></span></td>
      <td style="max-width:200px"><?= h($r['note']) ?></td>
      <td class="muted small"><?= h(substr((string) $r['created_at'], 0, 16)) ?></td>
      <td><span class="pill p-<?= h($r['status']) ?>"><?= h($r['status']) ?></span></td>
      <td><?php if ($r['status'] === 'pending'): ?>
        <form method="post" class="inline"><?= csrf() ?>
          <input type="hidden" name="act" value="confirm"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
          <input type="hidden" name="tab" value="donations">
          <select name="days" style="padding:5px"><option>30</option><option>90</option><option>180</option></select>
          <button>Открыть</button></form>
        <form method="post" class="inline"><?= csrf() ?>
          <input type="hidden" name="act" value="reject"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
          <input type="hidden" name="tab" value="donations"><button class="bad">Нет</button></form>
      <?php endif; ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="muted">Пока пусто</td></tr><?php endif; ?>
  </table></div>
  <p class="hint">Доступ на 90 дней автоматически ставит метку «Меценат», на 180 — корону «Золотой участник».</p>

<?php elseif ($tab === 'users'):
  $qs = trim((string) ($_GET['q'] ?? ''));
  $where = ''; $args = [];
  if ($qs !== '') { $where = 'WHERE u.name LIKE ? OR u.phone LIKE ?'; $args = ['%'.$qs.'%', '%'.$qs.'%']; }
  $rows = all("SELECT u.*, q.title goal, q.start_date sd, q.category
               FROM users u LEFT JOIN quests q ON q.id=(SELECT id FROM quests WHERE user_id=u.id ORDER BY id DESC LIMIT 1)
               {$where} ORDER BY u.id DESC LIMIT 300", $args);
  $cat = badge_catalog(); ?>
  <div class="card">
    <form method="get" class="inline"><input type="hidden" name="tab" value="users">
      <input name="q" value="<?= h($qs) ?>" placeholder="Имя или телефон"><button class="sec">Найти</button></form>
  </div>
  <div class="card"><table>
    <tr><th>#</th><th>Кто</th><th>Направление</th><th>День</th><th>Доступ</th><th>Метка</th><th>Роль</th><th></th></tr>
    <?php foreach ($rows as $r):
      $day = $r['sd'] ? min(180, daysBetween($r['sd'], today()) + 1) : 0;
      $b = badge_view((string) $r['badge']); ?>
      <tr>
        <td><?= (int) $r['id'] ?></td>
        <td><b><?= h($r['name']) ?></b> <?= $b ? $b['icon'] : '' ?>
          <?php if ($r['status'] !== 'active'): ?><span class="pill p-rejected"><?= h($r['status']) ?></span><?php endif; ?>
          <br><span class="muted small"><?= h(prettyPhone((string) $r['phone'])) ?></span></td>
        <td class="small"><?php $d = $r['category'] ? direction_of($r['category']) : null;
          echo $d ? h($d['icon'] . ' ' . $d['label']) : '—'; ?></td>
        <td><?= $day ?: '—' ?></td>
        <td class="<?= (!empty($r['access_until']) && $r['access_until'] >= today()) ? '' : 'muted' ?>">
          <?= h((string) $r['access_until']) ?: '—' ?></td>
        <td>
          <form method="post" class="inline"><?= csrf() ?>
            <input type="hidden" name="act" value="badge"><input type="hidden" name="tab" value="users">
            <input type="hidden" name="uid" value="<?= (int) $r['id'] ?>">
            <select name="badge" style="padding:4px;font-size:12px">
              <option value="">— нет —</option>
              <?php foreach ($cat as $bk => $bv): ?>
                <option value="<?= h($bk) ?>" <?= $r['badge'] === $bk ? 'selected' : '' ?>><?= h($bv['icon'] . ' ' . $bv['ru']) ?></option>
              <?php endforeach; ?>
            </select>
            <input name="note" value="<?= h($r['badge_note']) ?>" placeholder="подпись" style="width:88px;padding:4px;font-size:12px">
            <button class="sec" style="padding:5px 9px">✓</button></form></td>
        <td>
          <form method="post" class="inline"><?= csrf() ?>
            <input type="hidden" name="act" value="role"><input type="hidden" name="tab" value="users">
            <input type="hidden" name="uid" value="<?= (int) $r['id'] ?>">
            <select name="role" style="padding:4px;font-size:12px" onchange="this.form.submit()">
              <?php foreach (['user'=>'участник','leader'=>'лидер','moder'=>'модератор','admin'=>'админ'] as $rk => $rn): ?>
                <option value="<?= $rk ?>" <?= $r['role'] === $rk ? 'selected' : '' ?>><?= h($rn) ?></option>
              <?php endforeach; ?>
            </select></form></td>
        <td>
          <form method="post" class="inline"><?= csrf() ?>
            <input type="hidden" name="act" value="grant"><input type="hidden" name="tab" value="users">
            <input type="hidden" name="uid" value="<?= (int) $r['id'] ?>">
            <select name="days" style="padding:4px;font-size:12px"><option>30</option><option>90</option><option>180</option></select>
            <button class="sec" style="padding:5px 9px">+дни</button></form>
          <form method="post" class="inline"><?= csrf() ?>
            <input type="hidden" name="act" value="status"><input type="hidden" name="tab" value="users">
            <input type="hidden" name="uid" value="<?= (int) $r['id'] ?>">
            <select name="status" style="padding:4px;font-size:12px" onchange="this.form.submit()">
              <?php foreach (['active'=>'ок','shadow'=>'тень','banned'=>'бан'] as $sk => $sn): ?>
                <option value="<?= $sk ?>" <?= $r['status'] === $sk ? 'selected' : '' ?>><?= h($sn) ?></option>
              <?php endforeach; ?>
            </select></form></td>
      </tr>
    <?php endforeach; ?>
  </table></div>
  <p class="hint">«Тень» — человек продолжает пользоваться приложением, но его сообщения не видит группа.
    Мягкая мера против спамеров, которая не провоцирует создание новых аккаунтов.</p>

<?php elseif ($tab === 'reports'):
  $rows = all("SELECT r.*, u.name rn FROM reports r JOIN users u ON u.id=r.reporter_id
               ORDER BY (r.status='new') DESC, r.id DESC LIMIT 200");
  $rr = report_reasons('ru'); ?>
  <div class="card"><table>
    <tr><th>#</th><th>На что</th><th>Причина</th><th>Кто пожаловался</th><th>Комментарий</th><th>Статус</th><th></th></tr>
    <?php foreach ($rows as $r):
      $target = '';
      if ($r['target_type'] === 'user') { $tu = one('SELECT name FROM users WHERE id=?', [$r['target_id']]); $target = 'Участник: ' . ($tu['name'] ?? '—'); }
      if ($r['target_type'] === 'message') { $tm = one('SELECT text FROM squad_msgs WHERE id=?', [$r['target_id']]); $target = 'Сообщение: ' . mb_substr($tm['text'] ?? '—', 0, 60); }
      if ($r['target_type'] === 'photo') { $target = 'Фото #' . $r['target_id']; } ?>
      <tr><td><?= (int) $r['id'] ?></td>
        <td class="small"><?= h($target) ?></td>
        <td><b><?= h($rr[$r['reason']] ?? $r['reason']) ?></b></td>
        <td class="small muted"><?= h($r['rn']) ?><br><?= h(substr((string) $r['created_at'], 0, 16)) ?></td>
        <td class="small" style="max-width:200px"><?= h($r['note']) ?></td>
        <td><span class="pill p-<?= h($r['status']) ?>"><?= h($r['status']) ?></span>
          <?= $r['action'] ? '<br><span class="muted small">' . h($r['action']) . '</span>' : '' ?></td>
        <td><?php if ($r['status'] === 'new'): ?>
          <?php foreach (['hide'=>['Скрыть','sec'],'ban'=>['Бан','bad'],'restore'=>['Вернуть','ok'],'skip'=>['Отклонить','sec']] as $dk => $dv): ?>
            <form method="post" class="inline"><?= csrf() ?>
              <input type="hidden" name="act" value="report"><input type="hidden" name="tab" value="reports">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="do" value="<?= $dk ?>">
              <button class="<?= $dv[1] ?>" style="padding:5px 8px;font-size:12px"><?= $dv[0] ?></button></form>
          <?php endforeach; ?>
        <?php endif; ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="muted">Жалоб нет</td></tr><?php endif; ?>
  </table></div>
  <p class="hint">Три жалобы от разных людей на один объект автоматически скрывают его до вашего решения.</p>

<?php elseif ($tab === 'squads'): ?>
  <div class="card"><table>
    <tr><th>Группа</th><th>Код</th><th>Людей</th><th>Средний %</th><th>Возвраты</th><th>Ближайшая встреча</th></tr>
    <?php foreach (squad_board(100) as $b):
      $sq = one('SELECT code FROM squads WHERE id=?', [$b['id']]);
      $mt = one("SELECT starts_at,title FROM meetings WHERE squad_id=? AND starts_at>=? ORDER BY starts_at LIMIT 1",
                [$b['id'], date('Y-m-d H:i:s')]); ?>
      <tr><td><b><?= h($b['name']) ?></b></td>
      <td><code><?= h($sq['code'] ?? '') ?></code></td>
      <td><?= $b['members'] ?> / <?= (int) cfg('squad.size', 5) ?></td>
      <td><b><?= $b['pct'] ?>%</b></td><td><?= $b['comebacks'] ?></td>
      <td class="small muted"><?= $mt ? h(substr($mt['starts_at'], 0, 16) . ' · ' . $mt['title']) : '—' ?></td></tr>
    <?php endforeach; ?>
  </table></div>

<?php elseif ($tab === 'photos'):
  $rows = all("SELECT p.*, u.name FROM photos p JOIN users u ON u.id=p.user_id
               WHERE p.status!='removed' ORDER BY p.id DESC LIMIT 120"); ?>
  <div class="card"><table>
    <tr><th></th><th>Кто</th><th>День</th><th>Подпись</th><th>Видно</th><th>Статус</th><th></th></tr>
    <?php foreach ($rows as $r): ?>
      <tr><td><img class="thumb" src="uploads/photos/<?= h($r['file']) ?>" alt=""></td>
        <td class="small"><?= h($r['name']) ?></td><td><?= (int) $r['day_no'] ?></td>
        <td class="small" style="max-width:220px"><?= h($r['caption']) ?></td>
        <td class="small muted"><?= h($r['visibility']) ?></td>
        <td><span class="pill <?= $r['status'] === 'ok' ? 'p-confirmed' : 'p-rejected' ?>"><?= h($r['status']) ?></span></td>
        <td><form method="post" class="inline"><?= csrf() ?>
          <input type="hidden" name="act" value="photo"><input type="hidden" name="tab" value="photos">
          <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
          <select name="st" style="padding:4px;font-size:12px" onchange="this.form.submit()">
            <option value="ok" <?= $r['status']==='ok'?'selected':'' ?>>показывать</option>
            <option value="hidden" <?= $r['status']==='hidden'?'selected':'' ?>>скрыть</option>
            <option value="removed">удалить</option>
          </select></form></td></tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="muted">Пока пусто</td></tr><?php endif; ?>
  </table></div>

<?php elseif ($tab === 'ads'):
  $ads = all('SELECT * FROM ads ORDER BY id DESC'); ?>
  <div class="card">
    <h2 style="margin-top:0">Новое размещение</h2>
    <form method="post"><?= csrf() ?>
      <input type="hidden" name="act" value="ad_save"><input type="hidden" name="tab" value="ads">
      <div class="grid">
        <label>Экран<br><select name="slot">
          <option value="today">Сегодня</option><option value="world">Мир</option>
          <option value="squad">Группа</option><option value="profile">Профиль</option></select></label>
        <label>Направление<br><select name="category"><option value="">все</option>
          <?php foreach (categories_def() as $ck => $cv): ?><option value="<?= h($ck) ?>"><?= h($cv['ru']) ?></option><?php endforeach; ?>
        </select></label>
        <label>Язык<br><select name="lang"><option value="">все</option><option value="ru">ru</option><option value="uz">uz</option></select></label>
        <label>Город<br><input name="city" placeholder="все"></label>
        <label>Вес<br><input name="weight" value="1" type="number" min="1" max="100"></label>
      </div>
      <div style="margin-top:10px"><input name="title" placeholder="Заголовок" style="width:100%" required></div>
      <div style="margin-top:8px"><input name="text" placeholder="Текст" style="width:100%"></div>
      <div class="grid" style="margin-top:8px">
        <input name="cta" placeholder="Кнопка: Подробнее">
        <input name="url" placeholder="https://" required>
      </div>
      <label style="display:block;margin:10px 0"><input type="checkbox" name="active" checked> активно</label>
      <button>Сохранить</button>
    </form>
  </div>
  <div class="card"><table>
    <tr><th>Экран</th><th>Заголовок</th><th>Таргет</th><th>Показы</th><th>Клики</th><th>CTR</th><th></th></tr>
    <?php foreach ($ads as $a): ?>
      <tr><td><?= h($a['slot']) ?></td>
        <td><b><?= h($a['title']) ?></b><br><span class="muted small"><?= h($a['url']) ?></span></td>
        <td class="small muted"><?= h(($a['category'] ?: 'все') . ' / ' . ($a['lang'] ?: 'все') . ' / ' . ($a['city'] ?: 'все')) ?></td>
        <td><?= (int) $a['shows'] ?></td><td><?= (int) $a['clicks'] ?></td>
        <td><?= $a['shows'] ? round($a['clicks'] / $a['shows'] * 100, 1) : 0 ?>%</td>
        <td><form method="post" class="inline"><?= csrf() ?>
          <input type="hidden" name="act" value="ad_del"><input type="hidden" name="tab" value="ads">
          <input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><button class="bad">×</button></form></td></tr>
    <?php endforeach; ?>
  </table></div>
  <p class="hint">Правило площадки: один блок на экран, всегда подписан «Партнёр», никогда не показывается
    в коуче и в момент срыва. Таргетинг только по направлению цели, языку и городу — личные данные наружу не уходят.</p>

<?php elseif ($tab === 'promo'): ?>
  <div class="card"><form method="post"><?= csrf() ?>
    <input type="hidden" name="act" value="promo"><input type="hidden" name="tab" value="promo">
    Сколько: <input name="count" value="10" size="4">
    дней: <select name="days"><option>30</option><option>90</option><option>180</option></select>
    использований: <input name="uses" value="1" size="4">
    метка: <input name="note" placeholder="первая когорта">
    <button>Создать</button></form></div>
  <?php if (!empty($_SESSION['made'])): ?>
    <div class="card"><b>Новые коды:</b><br><br>
      <?php foreach ($_SESSION['made'] as $c): ?><code><?= h($c) ?></code> <?php endforeach; ?></div>
    <?php unset($_SESSION['made']); ?>
  <?php endif; ?>
  <div class="card"><table>
    <tr><th>Код</th><th>Дней</th><th>Использовано</th><th>Метка</th><th></th></tr>
    <?php foreach (all('SELECT * FROM promo_codes ORDER BY created_at DESC LIMIT 300') as $p): ?>
      <tr><td><code><?= h($p['code']) ?></code></td><td><?= (int) $p['days'] ?></td>
      <td><?= (int) $p['uses'] ?> / <?= (int) $p['max_uses'] ?></td>
      <td class="muted small"><?= h($p['note']) ?></td>
      <td><form method="post" class="inline"><?= csrf() ?>
        <input type="hidden" name="act" value="delpromo"><input type="hidden" name="tab" value="promo">
        <input type="hidden" name="code" value="<?= h($p['code']) ?>"><button class="bad">×</button></form></td></tr>
    <?php endforeach; ?>
  </table></div>

<?php else:
  $smsLog = @file_exists(__DIR__ . '/data/sms.log') ? array_slice(file(__DIR__ . '/data/sms.log'), -15) : []; ?>
  <div class="card">
    <h2 style="margin-top:0">Сообщение всем</h2>
    <p class="hint">Придёт в коуч каждому участнику как отдельное сообщение. Использовать редко:
      это самый быстрый способ обесценить коуча.</p>
    <form method="post"><?= csrf() ?>
      <input type="hidden" name="act" value="broadcast"><input type="hidden" name="tab" value="tools">
      <textarea name="text" placeholder="Текст…" maxlength="800"></textarea>
      <button style="margin-top:8px">Отправить</button></form>
  </div>
  <div class="card">
    <h2 style="margin-top:0">Состояние</h2>
    <table>
      <tr><td>ИИ</td><td><?= ai_enabled() ? '<b style="color:var(--ok)">включён</b> · ' . h(ai_model('chat')) : '<b style="color:var(--warn)">выключен</b> (работают шаблоны)' ?></td></tr>
      <tr><td>SMS</td><td><b><?= h(cfg('sms.driver', 'log')) ?></b><?= cfg('sms.driver') === 'log' ? ' — коды пишутся в data/sms.log' : '' ?></td></tr>
      <tr><td>Показ кода на экране</td><td><?= cfg('otp.show_code_in_dev') ? '<b style="color:var(--warn)">ВКЛЮЧЁН</b> — выключите перед боевым запуском' : 'выключен' ?></td></tr>
      <tr><td>cron</td><td><code><?= h((string) cfg('site_url')) ?>/cron.php?key=<?= h((string) cfg('cron_key')) ?></code></td></tr>
      <tr><td>Размер базы</td><td><?= round((@filesize(__DIR__ . '/data/level180.sqlite') ?: 0) / 1048576, 2) ?> МБ</td></tr>
      <tr><td>Реквизиты карт</td><td><?= strpos((string) (cfg('donate.cards.0.number') ?? ''), '0000 0000') !== false
          ? '<b style="color:var(--bad)">ЗАГЛУШКА</b> — впишите реальные в config.php' : 'заполнены' ?></td></tr>
    </table>
  </div>
  <?php if ($smsLog): ?>
  <div class="card"><h2 style="margin-top:0">Последние коды (режим log)</h2>
    <table><?php foreach (array_reverse($smsLog) as $l): ?><tr><td class="small"><?= h(trim($l)) ?></td></tr><?php endforeach; ?></table>
  </div><?php endif; ?>
<?php endif; ?>

<?php endif; ?>
</div></body></html>
