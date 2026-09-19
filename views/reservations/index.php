<?php
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $bookings */
/** @var array<int, array<string, mixed>> $hostBookings */
/** @var list<int> $unreadBookingIds */
/** @var int $unreadReservationsCount */
/** @var bool $isHost */
/** @var string $mode */

$statusLabels = [
    'pending' => __('reservations.status.pending'),
    'confirmed' => __('reservations.status.confirmed'),
    'cancelled' => __('reservations.status.cancelled'),
    'completed' => __('reservations.status.completed'),
    'rejected' => __('reservations.status.rejected'),
];

$statusTabs = [
    'active' => __('reservations.tab.active'),
    'past' => __('reservations.tab.past'),
    'cancelled' => __('reservations.tab.cancelled'),
];

$modeTabs = [
    'guest' => __('reservations.mode.guest'),
    'received' => __('reservations.mode.received'),
];

/** @var array<string, list<array<string, mixed>>> $bookingsByTab */
$bookingsByTab = ['active' => [], 'past' => [], 'cancelled' => []];
foreach ($bookings as $booking) {
    $bookingsByTab[booking_reservation_tab($booking)][] = $booking;
}

/** @var array<string, list<array<string, mixed>>> $hostBookingsByTab */
$hostBookingsByTab = ['active' => [], 'past' => [], 'cancelled' => []];
foreach ($hostBookings as $booking) {
    $hostBookingsByTab[booking_reservation_tab($booking)][] = $booking;
}

$activeList = $mode === 'received' ? $hostBookings : $bookings;
$activeBookingsByTab = $mode === 'received' ? $hostBookingsByTab : $bookingsByTab;

$defaultTab = 'active';
if ($activeBookingsByTab['active'] === [] && $activeBookingsByTab['past'] !== []) {
    $defaultTab = 'past';
} elseif ($activeBookingsByTab['active'] === [] && $activeBookingsByTab['past'] === [] && $activeBookingsByTab['cancelled'] !== []) {
    $defaultTab = 'cancelled';
}

$hasAnyBookings = $bookings !== [] || $hostBookings !== [];
?>
<section class="account-page reservations-page" data-reservations-enable-banner>
    <div class="reservations-shell">
        <header class="reservations-head">
            <h1><?= e(__('reservations.title')) ?></h1>
            <?php if ($unreadReservationsCount > 0): ?>
                <span class="reservations-head-unread"><?= e(__('reservations.unread_badge', ['count' => $unreadReservationsCount])) ?></span>
            <?php endif; ?>
        </header>

        <?php if ($bookingSuccess = flash('booking_success')): ?>
            <div class="reservations-alert" role="status">
                <?= e((string) $bookingSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if ($reservationSuccess = flash('reservation_success')): ?>
            <div class="reservations-alert" role="status">
                <?= e((string) $reservationSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if ($reservationError = flash('reservation_error')): ?>
            <div class="reservations-alert reservations-alert-error" role="alert">
                <?= e((string) $reservationError) ?>
            </div>
        <?php endif; ?>

        <?php if ($isHost && $unreadReservationsCount > 0 && $bookings === [] && $hostBookings !== []): ?>
            <div class="reservations-host-alert" role="status">
                <i class="bi bi-calendar-check-fill" aria-hidden="true"></i>
                <span><?= e(__('reservations.host_alert', ['count' => count($hostBookings)])) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!$hasAnyBookings): ?>
            <div class="reservations-empty">
                <i class="bi bi-calendar-x" aria-hidden="true"></i>
                <h2><?= e(__('reservations.empty_title')) ?></h2>
                <p><?= e(__('reservations.empty_lead')) ?></p>
                <a href="<?= url('/properties') ?>" class="reservations-empty-btn"><?= e(__('reservations.explore')) ?></a>
            </div>
        <?php else: ?>
            <div class="reservations-list<?= $unreadReservationsCount > 0 ? ' has-unread' : '' ?>" id="reservationsList">
                <?php if ($isHost): ?>
                    <nav class="reservations-mode-tabs" aria-label="<?= e(__('reservations.mode_label')) ?>">
                        <?php foreach ($modeTabs as $modeKey => $modeLabel): ?>
                            <a href="<?= url('/reservations?mode=' . $modeKey) ?>"
                               class="reservations-mode-tab<?= $mode === $modeKey ? ' is-active' : '' ?>"
                               aria-current="<?= $mode === $modeKey ? 'page' : 'false' ?>">
                                <?= e($modeLabel) ?>
                                <?php if ($modeKey === 'received' && $hostBookings !== []): ?>
                                    <span class="reservations-mode-count"><?= count($hostBookings) ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

                <?php if ($activeList === []): ?>
                    <div class="reservations-empty reservations-empty-compact">
                        <i class="bi bi-calendar-x" aria-hidden="true"></i>
                        <h2><?= e($mode === 'received' ? __('reservations.host.empty_title') : __('reservations.empty_title')) ?></h2>
                        <p><?= e($mode === 'received' ? __('reservations.host.empty_lead') : __('reservations.empty_lead')) ?></p>
                        <?php if ($mode === 'guest'): ?>
                            <a href="<?= url('/properties') ?>" class="reservations-empty-btn"><?= e(__('reservations.explore')) ?></a>
                        <?php elseif ($isHost): ?>
                            <a href="<?= url('/host') ?>" class="reservations-empty-btn"><?= e(__('reservations.host.manage')) ?></a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <nav class="reservations-tabs" aria-label="<?= e(__('reservations.tabs_label')) ?>">
                        <?php foreach ($statusTabs as $tabKey => $tabLabel): ?>
                            <button type="button"
                                    class="reservations-tab<?= $tabKey === $defaultTab ? ' is-active' : '' ?>"
                                    data-res-tab="<?= e($tabKey) ?>"
                                    aria-selected="<?= $tabKey === $defaultTab ? 'true' : 'false' ?>">
                                <?= e($tabLabel) ?>
                            </button>
                        <?php endforeach; ?>
                    </nav>

                    <?php foreach ($statusTabs as $tabKey => $tabLabel): ?>
                        <?php $tabBookings = $activeBookingsByTab[$tabKey]; ?>
                        <div class="reservations-panel<?= $tabKey === $defaultTab ? ' is-active' : '' ?>"
                             data-res-panel="<?= e($tabKey) ?>"
                             <?= $tabKey !== $defaultTab ? 'hidden' : '' ?>>
                            <?php if ($tabBookings === []): ?>
                                <p class="reservations-panel-empty"><?= e(__('reservations.empty_tab')) ?></p>
                            <?php else: ?>
                                <?php foreach ($tabBookings as $booking): ?>
                                    <section class="reservations-group">
                                        <h2 class="reservations-group-city"><?= e((string) ($booking['city'] ?? '')) ?></h2>
                                        <p class="reservations-group-dates">
                                            <?= e(booking_date_range((string) $booking['check_in'], (string) $booking['check_out'])) ?>
                                        </p>
                                        <?php if ($mode === 'received'): ?>
                                            <?php include base_path('views/partials/reservation-host-row.php'); ?>
                                        <?php else: ?>
                                            <?php include base_path('views/partials/reservation-row.php'); ?>
                                        <?php endif; ?>
                                    </section>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($hasAnyBookings && $activeList !== []): ?>
<script>
(function () {
    var tabs = document.querySelectorAll('[data-res-tab]');
    var panels = document.querySelectorAll('[data-res-panel]');
    if (!tabs.length || !panels.length) {
        return;
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = tab.getAttribute('data-res-tab');
            tabs.forEach(function (item) {
                var active = item.getAttribute('data-res-tab') === target;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panels.forEach(function (panel) {
                var show = panel.getAttribute('data-res-panel') === target;
                panel.classList.toggle('is-active', show);
                panel.hidden = !show;
            });
        });
    });
})();
</script>
<?php endif; ?>
