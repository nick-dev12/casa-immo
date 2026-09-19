<?php
/** @var list<string> $extraStyles */
$extraStyles = $extraStyles ?? [];
$appName = (string) config('app', 'name', 'Casa Immo');
$themeColor = '#1B4D24';
?>
<link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/brand/favicon-32.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= asset('images/brand/favicon-16.png') ?>">
<link rel="icon" type="image/png" sizes="48x48" href="<?= asset('images/brand/favicon-48.png') ?>">
<link rel="icon" type="image/png" href="<?= asset('images/brand/favicon.png') ?>">
<link rel="shortcut icon" type="image/png" href="<?= asset('images/brand/favicon-32.png') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/brand/apple-touch-icon.png') ?>">
<link rel="manifest" href="<?= asset('site.webmanifest') ?>">
<meta name="msapplication-TileImage" content="<?= asset('images/brand/icon-192.png') ?>">
<meta name="msapplication-TileColor" content="<?= e($themeColor) ?>">
<meta name="theme-color" content="<?= e($themeColor) ?>">
<meta name="application-name" content="<?= e($appName) ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="<?= e($appName) ?>">
<meta name="mobile-web-app-capable" content="yes">
<meta property="og:site_name" content="<?= e($appName) ?>">
<meta property="og:title" content="<?= e(($title ?? $appName) . ' — ' . $appName) ?>">
<meta property="og:image" content="<?= asset('images/brand/icon-512.png') ?>">
<meta name="twitter:card" content="summary">
<meta name="twitter:image" content="<?= asset('images/brand/icon-512.png') ?>">
<meta name="twitter:title" content="<?= e(($title ?? $appName) . ' — ' . $appName) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preload" href="<?= asset('css/app.css') ?>" as="style">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<?php foreach ($extraStyles as $stylePath): ?>
<link rel="stylesheet" href="<?= asset($stylePath) ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
<link rel="stylesheet" href="<?= asset('css/mobile-ui.css') ?>">
<link rel="stylesheet" href="<?= asset('css/brand.css') ?>">
<link rel="stylesheet" href="<?= asset('css/gtranslate.css') ?>">
