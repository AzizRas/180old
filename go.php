<?php
/* Переход по рекламной ссылке со счётчиком кликов */
require __DIR__ . '/lib/util.php';
require __DIR__ . '/lib/db.php';
$id = (int) ($_GET['ad'] ?? 0);
$ad = one('SELECT url FROM ads WHERE id=? AND active=1', [$id]);
if (!$ad || !$ad['url']) { header('Location: index.php'); exit; }
q('UPDATE ads SET clicks=clicks+1 WHERE id=?', [$id]);
$u = (string) $ad['url'];
if (!preg_match('#^https?://#i', $u)) { header('Location: index.php'); exit; }
header('Location: ' . $u, true, 302);
