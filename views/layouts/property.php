<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title ?? config('app', 'name')) ?> — <?= e(config('app', 'name')) ?></title>
    <?php
    $extraStyles = ['css/property.css'];
    include base_path('views/partials/head-assets.php');
    ?>
    <?php if (\App\Helpers\AuthHelper::check()): ?>
    <link rel="stylesheet" href="<?= asset('css/messages.css') ?>">
    <?php endif; ?>
</head>
<body class="app-body property-page" data-auth="<?= \App\Helpers\AuthHelper::check() ? '1' : '0' ?>" data-nav-profile="<?= e(\App\Helpers\AuthHelper::bottomNavProfileKind()) ?>" data-app-url="<?= e(rtrim(url(''), '/')) ?>" data-messages-poll-url="<?= e(url('/api/messages/unread')) ?>" data-messages-notify-title="<?= e(__('messages.notify.title_short')) ?>" data-messages-notify-enable="<?= e(__('messages.notify.enable')) ?>" data-messages-notify-dismiss="<?= e(__('messages.notify.dismiss')) ?>" data-reservations-poll-url="<?= e(url('/api/reservations/unread')) ?>" data-reservations-notify-title="<?= e(__('reservations.notify.title_short')) ?>" data-reservations-notify-enable="<?= e(__('reservations.notify.enable')) ?>" data-reservations-notify-dismiss="<?= e(__('reservations.notify.dismiss')) ?>">
    <main class="property-main">
        <?= $content ?? '' ?>
    </main>

    <?php
    include base_path('views/partials/bottom-nav.php');
    ?>

    <script src="<?= asset('js/app-core.js') ?>" defer></script>
    <?php if (\App\Helpers\AuthHelper::check()): ?>
    <script src="<?= asset('js/messages-notify.js') ?>" defer></script>
    <script src="<?= asset('js/reservations-notify.js') ?>" defer></script>
    <?php endif; ?>
    <script src="<?= asset('js/property.js') ?>" defer></script>
    <script src="<?= asset('js/property-booking-calendar.js') ?>" defer></script>
    <?php include base_path('views/partials/gtranslate-scripts.php'); ?>
</body>
</html>
