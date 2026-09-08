<?php
require __DIR__ . '/lib/util.php';
$name = htmlspecialchars((string) cfg('app_name', '180 kun'), ENT_QUOTES);
$v = '2.0.' . (int) @filemtime(__DIR__ . '/assets/app.js');
?><!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,user-scalable=no">
<meta name="theme-color" content="#0B0E14">
<meta name="description" content="180 дней. Один путь. Ты не проигрываешь, когда падаешь — только когда перестаёшь возвращаться.">
<meta property="og:title" content="<?= $name ?>">
<meta property="og:description" content="Приложение, которое не даёт тебе исчезнуть. 180 дней, персональный план и четыре человека рядом.">
<meta property="og:type" content="website">
<title><?= $name ?> — вернуться важнее</title>
<link rel="manifest" href="assets/manifest.json">
<link rel="icon" href="assets/mark.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="assets/mark.svg">
<link rel="stylesheet" href="assets/app.css?v=<?= $v ?>">
</head>
<body>
<div id="app"><div class="boot"><img src="assets/mark.svg" width="64" height="64" alt=""></div></div>
<script src="assets/app.js?v=<?= $v ?>"></script>
</body>
</html>
