<!DOCTYPE html>
<html lang="<?= e(locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title ?? config('app', 'name')) ?> — <?= e(config('app', 'name')) ?></title>
    <?php
    $extraStyles = [];
    if (!empty($isHome)) {
        $extraStyles[] = 'css/booking-home.css';
    }
    if (!empty($isDestination)) {
        $extraStyles[] = 'css/destination.css';
    }
    if (!empty($isPropertiesList) || !empty($isLandsList)) {
        $extraStyles[] = 'css/properties-list.css';
    }
    if (!empty($isAccountPage)) {
        $extraStyles[] = 'css/account.css';
    }
    if (\App\Helpers\AuthHelper::check()) {
        $extraStyles[] = 'css/messages.css';
    }
    if (!empty($isAuthPage)) {
        $extraStyles[] = 'css/auth.css';
    }
    if (!empty($isHostPage)) {
        $extraStyles[] = 'css/host.css';
        $extraStyles[] = 'css/host-admin.css';
    }
    if (!empty($isAdminPage)) {
        $extraStyles[] = 'css/admin.css';
    }
    include base_path('views/partials/head-assets.php');
    ?>
    <?php if (!empty($isHome)): ?>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <?php if (!empty($needsListingMap)): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous">
    <?php endif; ?>
</head>
<body class="app-body<?= !empty($isHome) ? ' page-home' : '' ?><?= !empty($isDestination) ? ' page-destination' : '' ?><?= !empty($isPropertiesList) || !empty($isLandsList) ? ' page-properties-list' : '' ?><?= !empty($isLandsList) ? ' page-lands-list' : '' ?><?= !empty($isAccountPage) && empty($isHostPage) && empty($isAdminPage) ? ' page-account' : '' ?><?= !empty($isHelpPage) ? ' page-help' : '' ?><?= !empty($isHostPage) ? ' page-host' : '' ?><?= !empty($isAdminPage) ? ' page-admin' : '' ?><?= !empty($isProfile) ? ' page-profile' : '' ?><?= !empty($isAuthPage) ? ' page-auth' : '' ?><?= !empty($isAuthChoosePage) ? ' page-auth-choose' : '' ?><?= !empty($isReservations) ? ' page-reservations' : '' ?><?= !empty($isReservationDetail) ? ' page-reservation-detail' : '' ?><?= !empty($isReservationHostDetail) ? ' page-reservation-host' : '' ?><?= !empty($isMessages) ? ' page-messages' : '' ?><?= !empty($isMessageThread) ? ' page-message-thread' : '' ?>"
      data-auth="<?= \App\Helpers\AuthHelper::check() ? '1' : '0' ?>"
      data-nav-profile="<?= e(\App\Helpers\AuthHelper::bottomNavProfileKind()) ?>"
      data-app-url="<?= e(rtrim(url(''), '/')) ?>"
      data-messages-poll-url="<?= e(url('/api/messages/unread')) ?>"
      data-messages-notify-title="<?= e(__('messages.notify.title_short')) ?>"
      data-messages-notify-enable="<?= e(__('messages.notify.enable')) ?>"
      data-messages-notify-dismiss="<?= e(__('messages.notify.dismiss')) ?>"
      data-reservations-poll-url="<?= e(url('/api/reservations/unread')) ?>"
      data-reservations-notify-title="<?= e(__('reservations.notify.title_short')) ?>"
      data-reservations-notify-enable="<?= e(__('reservations.notify.enable')) ?>"
      data-reservations-notify-dismiss="<?= e(__('reservations.notify.dismiss')) ?>">
    <?php if (empty($isHome) && empty($isDestination) && empty($isPropertiesList) && empty($isLandsList) && empty($isAccountPage) && empty($isHostPage) && empty($isAdminPage)): ?>
    <header class="app-header">
        <div class="container app-container">
            <div class="d-flex align-items-center justify-content-between py-3">
                <a href="<?= url('/?city=' . urlencode($currentCity ?? config('app', 'default_city', 'Ziguinchor'))) ?>" class="location-picker text-decoration-none">
                    <span class="location-label"><?= e(__('layout.casamance')) ?></span>
                    <span class="location-value">
                        <i class="bi bi-geo-alt-fill text-primary"></i>
                        <?= e($currentCity ?? config('app', 'default_city', 'Ziguinchor')) ?>
                        <i class="bi bi-chevron-down ms-1"></i>
                    </span>
                </a>
                <div class="header-actions d-flex align-items-center gap-2">
                    <?php
                    $gtranslateClass = 'gt-lang--header';
                    include base_path('views/partials/gtranslate.php');
                    ?>
                    <a href="#" class="icon-btn" aria-label="<?= e(__('nav.notifications')) ?>">
                        <i class="bi bi-bell"></i>
                        <span class="notif-dot"></span>
                    </a>
                    <a href="#" class="btn btn-primary btn-sm rounded-pill px-3 d-none d-lg-inline-flex"><?= e(__('nav.connection')) ?></a>
                </div>
            </div>
        </div>
    </header>
    <?php endif; ?>

    <main class="app-main<?= !empty($isHome) ? ' app-main-home' : '' ?><?= !empty($isDestination) ? ' app-main-destination' : '' ?><?= !empty($isPropertiesList) || !empty($isLandsList) ? ' app-main-properties-list' : '' ?>">
        <?= $content ?? '' ?>
    </main>

    <?php if (empty($isAuthPage) && empty($isHostPage) && empty($isAdminPage)): ?>
    <?php include base_path('views/partials/bottom-nav.php'); ?>
    <?php endif; ?>

    <footer class="app-footer d-none d-lg-block">
        <div class="container app-container text-center">
            <small><?= e(__('footer.copyright', [
                'year' => date('Y'),
                'name' => config('app', 'name'),
            ])) ?></small>
        </div>
    </footer>

    <script src="<?= asset('js/app-core.js') ?>" defer></script>
    <?php if (!empty($isHome)): ?>
    <script src="<?= asset('js/home.js') ?>" defer></script>
    <?php endif; ?>
    <?php if (!empty($isHostPage)): ?>
    <script src="<?= asset('js/land-plan-preview.js') ?>" defer></script>
    <script src="<?= asset('js/host-listing-form.js') ?>" defer></script>
    <script src="<?= asset('js/host-listing-gallery.js') ?>" defer></script>
    <script src="<?= asset('js/host-district.js') ?>" defer></script>
    <script src="<?= asset('js/host-geolocate.js') ?>" defer></script>
    <?php endif; ?>
    <?php if (!empty($isAuthPage)): ?>
    <script src="<?= asset('js/auth-panel.js') ?>" defer></script>
    <?php if (!empty($isAuthRegisterPage)): ?>
    <script src="<?= asset('js/auth-agency-geolocate.js') ?>" defer></script>
    <?php endif; ?>
    <?php endif; ?>
    <?php if (\App\Helpers\AuthHelper::check()): ?>
    <script src="<?= asset('js/messages-notify.js') ?>" defer></script>
    <script src="<?= asset('js/reservations-notify.js') ?>" defer></script>
    <?php endif; ?>
    <?php if (!empty($isMessages) && empty($isMessageThread)): ?>
    <script src="<?= asset('js/messages-inbox.js') ?>" defer></script>
    <?php endif; ?>
    <?php if (!empty($isReservations) && empty($isReservationDetail)): ?>
    <script src="<?= asset('js/reservations-inbox.js') ?>" defer></script>
    <?php endif; ?>
    <?php if (!empty($isMessageThread)): ?>
    <script src="<?= asset('js/messages.js') ?>" defer></script>
    <?php endif; ?>
    <?php if (!empty($needsLandPlanPreview)): ?>
    <script src="<?= asset('js/land-plan-preview.js') ?>" defer></script>
    <?php endif; ?>
    <?php if (!empty($needsListingMap)): ?>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous" defer></script>
    <script src="<?= asset('js/listing-map.js') ?>" defer></script>
    <?php endif; ?>
    <?php include base_path('views/partials/gtranslate-scripts.php'); ?>
</body>
</html>
