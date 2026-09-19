<?php
/** @var array<string, mixed> $booking */
/** @var array<int, array<string, mixed>> $propertyImages */

$statusLabels = [
    'pending' => __('reservations.status.pending'),
    'confirmed' => __('reservations.status.confirmed'),
    'cancelled' => __('reservations.status.cancelled'),
    'completed' => __('reservations.status.completed'),
    'rejected' => __('reservations.status.rejected'),
];

$propertyId = (int) $booking['property_id'];
$bookingId = (int) $booking['id'];
$guestId = (int) ($booking['user_id'] ?? 0);
$propertyUrl = url('/properties/' . $propertyId . '?city=' . urlencode((string) ($booking['city'] ?? '')));
$title = translated((string) ($booking['title'] ?? ''));
$propertyType = (string) ($booking['type'] ?? 'autre');
$coverImage = property_image($booking['primary_image'] ?? null, $propertyType);

$status = (string) ($booking['status'] ?? 'confirmed');
$displayStatus = $status === 'pending' ? 'confirmed' : $status;
$statusLabel = $statusLabels[$displayStatus] ?? ($statusLabels[$status] ?? ucfirst($status));
$isCancelled = in_array($status, ['cancelled', 'rejected'], true);
$canReject = (new \App\Models\Booking())->canRejectForHost($booking);

$guestName = booking_guest_name($booking);
$guestEmail = trim((string) ($booking['guest_email'] ?? ''));
$guestPhone = trim((string) ($booking['guest_phone'] ?? ''));
$guestPhoneHref = $guestPhone !== '' ? 'tel:' . preg_replace('/\s+/', '', $guestPhone) : '';

$bookingReference = booking_reference($bookingId);
$nights = (int) ($booking['nights'] ?? 0);
$guests = (int) ($booking['guests'] ?? 0);
$totalAmount = money($booking['total_amount'], (string) ($booking['currency'] ?? 'XOF'));
$pricePerUnit = (float) ($booking['price_per_unit'] ?? 0);
$subtotal = (float) ($booking['subtotal'] ?? 0);
$commissionRate = (float) ($booking['commission_rate'] ?? 0);
$commissionAmount = (float) ($booking['commission_amount'] ?? 0);
$currency = (string) ($booking['currency'] ?? 'XOF');
$hasCommission = $commissionAmount > 0;
$netAmount = max(0, $subtotal - $commissionAmount);

$dateRange = booking_date_range((string) $booking['check_in'], (string) $booking['check_out']);
$stayLabel = booking_stay_range_label((string) $booking['check_in'], (string) $booking['check_out']);
$bookedAt = format_date((string) ($booking['created_at'] ?? ''), 'datetime');
$city = trim((string) ($booking['city'] ?? ''));
$messagesUrl = messages_start_url($propertyId, null, $guestId);
?>
<section class="account-page reservation-host-page" data-reservation-detail-id="<?= $bookingId ?>">
    <div class="reservation-host-shell">
        <header class="reservation-host-nav">
            <a href="<?= url('/reservations?mode=received') ?>" class="reservation-host-back">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                <?= e(__('reservations.host.detail.back')) ?>
            </a>
            <span class="reservation-host-brand"><?= e(config('app', 'name')) ?></span>
            <span class="reservation-host-nav-spacer" aria-hidden="true"></span>
        </header>

        <?php if ($reservationSuccess = flash('reservation_success')): ?>
            <div class="reservation-host-alert reservation-host-alert-success" role="status">
                <?= e((string) $reservationSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if ($reservationError = flash('reservation_error')): ?>
            <div class="reservation-host-alert reservation-host-alert-error" role="alert">
                <?= e((string) $reservationError) ?>
            </div>
        <?php endif; ?>

        <div class="reservation-host-hero">
            <div class="reservation-host-hero-top">
                <span class="reservation-host-status reservation-host-status-<?= e($displayStatus) ?>"><?= e($statusLabel) ?></span>
            </div>
            <h1 class="reservation-host-title"><?= e(__('reservations.host.detail.title')) ?></h1>
            <p class="reservation-host-ref"><?= e(__('reservations.detail.reference')) ?> · <?= e($bookingReference) ?></p>
        </div>

        <?php if ($isCancelled): ?>
            <div class="reservation-host-alert reservation-host-alert-cancelled" role="status">
                <?= e(__('reservations.cancelled_banner')) ?>
            </div>
        <?php endif; ?>

        <article class="reservation-host-card reservation-host-property">
            <a href="<?= e($propertyUrl) ?>" class="reservation-host-property-link">
                <div class="reservation-host-property-thumb">
                    <img src="<?= e($coverImage) ?>" alt="" loading="lazy">
                </div>
                <div class="reservation-host-property-body">
                    <p class="reservation-host-card-kicker"><?= e(__('reservations.host.detail.listing')) ?></p>
                    <h2 class="reservation-host-property-title"><?= e($title) ?></h2>
                    <?php if ($city !== ''): ?>
                        <p class="reservation-host-property-meta"><i class="bi bi-geo-alt-fill" aria-hidden="true"></i> <?= e($city) ?></p>
                    <?php endif; ?>
                </div>
                <i class="bi bi-chevron-right reservation-host-card-arrow" aria-hidden="true"></i>
            </a>
        </article>

        <article class="reservation-host-card reservation-host-guest">
            <div class="reservation-host-guest-head">
                <span class="reservation-host-guest-avatar" aria-hidden="true"><?= e(user_initials([
                    'first_name' => $booking['guest_first_name'] ?? '',
                    'last_name' => $booking['guest_last_name'] ?? '',
                ])) ?></span>
                <div>
                    <p class="reservation-host-card-kicker"><?= e(__('reservations.host.guest')) ?></p>
                    <h2 class="reservation-host-guest-name"><?= e($guestName) ?></h2>
                </div>
            </div>
            <dl class="reservation-host-info-grid">
                <?php if ($guestEmail !== ''): ?>
                    <div class="reservation-host-info-item">
                        <dt><i class="bi bi-envelope" aria-hidden="true"></i> <?= e(__('reservations.host.detail.email')) ?></dt>
                        <dd><a href="mailto:<?= e($guestEmail) ?>"><?= e($guestEmail) ?></a></dd>
                    </div>
                <?php endif; ?>
                <?php if ($guestPhone !== ''): ?>
                    <div class="reservation-host-info-item">
                        <dt><i class="bi bi-telephone" aria-hidden="true"></i> <?= e(__('reservations.host.detail.phone')) ?></dt>
                        <dd><a href="<?= e($guestPhoneHref) ?>"><?= e($guestPhone) ?></a></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </article>

        <section class="reservation-host-card reservation-host-summary" aria-label="<?= e(__('reservations.host.detail.stay_title')) ?>">
            <h2 class="reservation-host-section-title"><?= e(__('reservations.host.detail.stay_title')) ?></h2>
            <div class="reservation-host-metrics">
                <div class="reservation-host-metric">
                    <span class="reservation-host-metric-label"><?= e(__('reservations.host.detail.dates')) ?></span>
                    <strong class="reservation-host-metric-value"><?= e($stayLabel) ?></strong>
                    <span class="reservation-host-metric-sub"><?= e($dateRange) ?></span>
                </div>
                <div class="reservation-host-metric">
                    <span class="reservation-host-metric-label"><?= e(__('reservations.detail.nights')) ?></span>
                    <strong class="reservation-host-metric-value"><?= $nights ?></strong>
                </div>
                <div class="reservation-host-metric">
                    <span class="reservation-host-metric-label"><?= e(__('reservations.detail.guests')) ?></span>
                    <strong class="reservation-host-metric-value"><?= $guests ?></strong>
                </div>
                <div class="reservation-host-metric reservation-host-metric-highlight">
                    <span class="reservation-host-metric-label"><?= e(__('reservations.detail.total')) ?></span>
                    <strong class="reservation-host-metric-value"><?= e($totalAmount) ?></strong>
                </div>
            </div>
        </section>

        <section class="reservation-host-card reservation-host-payment" aria-label="<?= e(__('reservations.host.detail.payment_title')) ?>">
            <h2 class="reservation-host-section-title"><?= e(__('reservations.host.detail.payment_title')) ?></h2>
            <dl class="reservation-host-payment-list">
                <?php if ($pricePerUnit > 0): ?>
                    <div class="reservation-host-payment-row">
                        <dt><?= e(__('reservations.host.detail.price_per_night')) ?></dt>
                        <dd><?= e(money($pricePerUnit, $currency)) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ($subtotal > 0): ?>
                    <div class="reservation-host-payment-row">
                        <dt><?= e(__('reservations.host.detail.subtotal', ['nights' => $nights])) ?></dt>
                        <dd><?= e(money($subtotal, $currency)) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if ($hasCommission): ?>
                    <div class="reservation-host-payment-row reservation-host-payment-row-muted">
                        <dt><?= e(__('reservations.host.detail.commission', ['rate' => rtrim(rtrim(number_format($commissionRate, 2, '.', ''), '0'), '.')])) ?></dt>
                        <dd>- <?= e(money($commissionAmount, $currency)) ?></dd>
                    </div>
                    <div class="reservation-host-payment-row reservation-host-payment-row-total">
                        <dt><?= e(__('reservations.host.detail.net')) ?></dt>
                        <dd><?= e(money($netAmount, $currency)) ?></dd>
                    </div>
                <?php else: ?>
                    <div class="reservation-host-payment-row reservation-host-payment-row-total">
                        <dt><?= e(__('reservations.detail.total')) ?></dt>
                        <dd><?= e($totalAmount) ?></dd>
                    </div>
                <?php endif; ?>
                <div class="reservation-host-payment-row reservation-host-payment-row-muted">
                    <dt><?= e(__('reservations.detail.booked_at')) ?></dt>
                    <dd><?= e($bookedAt) ?></dd>
                </div>
            </dl>
        </section>

        <div class="reservation-host-actions">
            <?php if (!$isCancelled): ?>
                <a href="<?= e($messagesUrl) ?>" class="reservation-host-btn reservation-host-btn-primary">
                    <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
                    <?= e(__('reservations.host.detail.contact_guest')) ?>
                </a>
            <?php endif; ?>
            <?php if ($canReject): ?>
                <form method="post"
                      action="<?= url('/host/bookings/' . $bookingId . '/reject') ?>"
                      class="reservation-host-reject-form"
                      data-confirm="<?= e(__('reservations.host.detail.reject_confirm')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="reservation-host-btn reservation-host-btn-danger">
                        <i class="bi bi-x-circle" aria-hidden="true"></i>
                        <?= e(__('reservations.host.detail.reject')) ?>
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?= e($propertyUrl) ?>" class="reservation-host-btn reservation-host-btn-outline">
                <i class="bi bi-house-door" aria-hidden="true"></i>
                <?= e(__('reservations.host.detail.view_listing')) ?>
            </a>
            <a href="<?= url('/host#host-bookings') ?>" class="reservation-host-btn reservation-host-btn-ghost">
                <?= e(__('reservations.host.detail.manage')) ?>
            </a>
        </div>
    </div>
</section>
<script>
document.querySelectorAll('.reservation-host-reject-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        var message = form.getAttribute('data-confirm') || '';
        if (message !== '' && !window.confirm(message)) {
            event.preventDefault();
        }
    });
});
</script>
