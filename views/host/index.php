<?php
/** @var array<string, mixed> $establishment */
/** @var array<string, int|float> $stats */
/** @var array<int, array<string, mixed>> $recentBookings */
/** @var array<string, mixed>|null $user */

$activeNav = 'dashboard';
$navBadges = [
    'listings' => (int) ($stats['listings_draft'] ?? 0) + (int) ($stats['listings_pending'] ?? 0),
    'bookings' => (int) ($stats['bookings_upcoming'] ?? 0),
    'messages' => (int) ($navBadges['messages'] ?? unread_messages_count()),
];
include base_path('views/host/partials/header.php');

$bookingStatusLabels = [
    'pending' => __('reservations.status.confirmed'),
    'confirmed' => __('reservations.status.confirmed'),
    'cancelled' => __('reservations.status.cancelled'),
    'completed' => __('reservations.status.completed'),
    'rejected' => __('reservations.status.rejected'),
];

$revenue = money($stats['revenue'] ?? 0, 'XOF');
$upcomingBookings = (int) ($stats['bookings_upcoming'] ?? 0);
$pendingVisits = (int) ($stats['visits_pending'] ?? 0);
$publicHomeUrl = url('/');
?>
<?php
$heroLabel = __('host.dashboard_revenue');
$heroValue = $revenue;
$heroBadge = __('host.dashboard_verified');
$heroActions = [
    ['href' => url('/host/properties'), 'label' => __('host.dashboard_my_listings'), 'icon' => 'houses-fill'],
    ['href' => $publicHomeUrl, 'label' => __('host.dashboard_view_shop'), 'icon' => 'shop-window', 'ghost' => true],
    ['href' => url('/host/properties/new'), 'label' => __('host.dashboard_publish'), 'icon' => 'plus-lg', 'ghost' => true],
];
include base_path('views/host/partials/admin-hero.php');
?>

<div class="host-dash-stats">
    <article class="host-dash-stat">
        <span class="host-dash-stat-icon host-dash-stat-icon-blue" aria-hidden="true"><i class="bi bi-houses-fill"></i></span>
        <div>
            <p class="host-dash-stat-label"><?= e(__('host.stat_listings_total')) ?></p>
            <p class="host-dash-stat-value"><?= (int) ($stats['listings_total'] ?? 0) ?></p>
        </div>
    </article>
    <article class="host-dash-stat">
        <span class="host-dash-stat-icon host-dash-stat-icon-green" aria-hidden="true"><i class="bi bi-calendar-check-fill"></i></span>
        <div>
            <p class="host-dash-stat-label"><?= e(__('host.stat_bookings_upcoming')) ?></p>
            <p class="host-dash-stat-value"><?= $upcomingBookings ?></p>
        </div>
    </article>
    <article class="host-dash-stat">
        <span class="host-dash-stat-icon host-dash-stat-icon-orange" aria-hidden="true"><i class="bi bi-check-circle-fill"></i></span>
        <div>
            <p class="host-dash-stat-label"><?= e(__('host.stat_listings_published')) ?></p>
            <p class="host-dash-stat-value"><?= (int) ($stats['listings_published'] ?? 0) ?></p>
        </div>
    </article>
    <article class="host-dash-stat">
        <span class="host-dash-stat-icon host-dash-stat-icon-yellow" aria-hidden="true"><i class="bi bi-receipt"></i></span>
        <div>
            <p class="host-dash-stat-label"><?= e(__('host.stat_bookings_total')) ?></p>
            <p class="host-dash-stat-value"><?= (int) ($stats['bookings_total'] ?? 0) ?></p>
        </div>
    </article>
</div>

<?php if ($pendingVisits > 0): ?>
    <div class="host-dash-alert" role="status">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
        <p><?= e(__('host.dashboard_alert_visits', ['count' => $pendingVisits])) ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="host-flash host-flash-success" role="status"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="host-flash host-flash-error" role="alert"><?= e((string) $error) ?></div>
<?php endif; ?>

<section class="host-panel host-dash-panel host-bookings-panel" id="host-bookings">
    <div class="host-panel-head host-panel-head-inline">
        <h2><?= e(__('host.recent_bookings')) ?></h2>
        <a href="<?= url('/reservations?mode=received') ?>" class="host-link"><?= e(__('host.bookings_view_all')) ?></a>
    </div>

    <?php if (($recentBookings ?? []) === []): ?>
        <div class="host-empty host-empty-compact">
            <i class="bi bi-calendar-x"></i>
            <p><?= e(__('host.no_bookings')) ?></p>
        </div>
    <?php else: ?>
        <div class="host-booking-cards">
            <?php foreach ($recentBookings as $booking): ?>
                <?php
                $bookingId = (int) ($booking['id'] ?? 0);
                $bStatus = (string) ($booking['status'] ?? 'confirmed');
                $displayStatus = $bStatus === 'pending' ? 'confirmed' : $bStatus;
                $bLabel = $bookingStatusLabels[$displayStatus] ?? ucfirst($displayStatus);
                $canReject = (new \App\Models\Booking())->canRejectForHost($booking);
                $guestName = booking_guest_name($booking);
                $dateRange = booking_date_range((string) $booking['check_in'], (string) $booking['check_out']);
                ?>
                <article class="host-booking-card host-booking-card-<?= e($displayStatus) ?>">
                    <div class="host-booking-card-top">
                        <a href="<?= url('/reservations/' . $bookingId) ?>" class="host-booking-card-thumb" aria-hidden="true" tabindex="-1">
                            <img src="<?= e(property_image($booking['primary_image'] ?? null, (string) ($booking['type'] ?? 'autre'))) ?>"
                                 alt=""
                                 loading="lazy">
                        </a>
                        <div class="host-booking-card-info">
                            <div class="host-booking-card-main">
                            <strong class="host-booking-card-title"><?= e(translated((string) ($booking['title'] ?? ''))) ?></strong>
                            <span class="host-booking-card-meta">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                <?= e((string) ($booking['city'] ?? '')) ?>
                            </span>
                            <?php if ($guestName !== ''): ?>
                                <span class="host-booking-card-guest">
                                    <i class="bi bi-person" aria-hidden="true"></i>
                                    <?= e($guestName) ?>
                                </span>
                            <?php endif; ?>
                            <span class="host-booking-card-dates">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                <?= e($dateRange) ?>
                            </span>
                        </div>
                        <div class="host-booking-card-side">
                            <span class="host-status host-status-<?= e($displayStatus) ?>"><?= e($bLabel) ?></span>
                            <span class="host-booking-card-amount"><?= e(money($booking['total_amount'], (string) ($booking['currency'] ?? 'XOF'))) ?></span>
                        </div>
                        </div>
                    </div>
                    <div class="host-booking-card-actions">
                        <a href="<?= url('/reservations/' . $bookingId) ?>" class="host-btn host-btn-sm host-btn-outline">
                            <?= e(__('host.booking_view_detail')) ?>
                        </a>
                        <?php if ($canReject): ?>
                            <form method="post"
                                  action="<?= url('/host/bookings/' . $bookingId . '/reject') ?>"
                                  class="host-inline-form host-reject-form"
                                  data-confirm="<?= e(__('host.reject_booking_confirm')) ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="host-btn host-btn-sm host-btn-danger">
                                    <i class="bi bi-x-circle" aria-hidden="true"></i>
                                    <?= e(__('host.reject_booking')) ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
document.querySelectorAll('.host-reject-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        var message = form.getAttribute('data-confirm') || '';
        if (message !== '' && !window.confirm(message)) {
            event.preventDefault();
        }
    });
});
</script>

<?php include base_path('views/host/partials/footer.php'); ?>
