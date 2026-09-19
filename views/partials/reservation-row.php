<?php
/** @var array<string, mixed> $booking */
/** @var array<string, string> $statusLabels */
/** @var list<int>|null $unreadBookingIds */

$unreadBookingIds = $unreadBookingIds ?? [];
$propertyId = (int) $booking['property_id'];
$bookingId = (int) $booking['id'];
$isUnread = in_array($bookingId, $unreadBookingIds, true);
$detailUrl = url('/reservations/' . $bookingId);
$propertyUrl = url('/properties/' . $propertyId . '?city=' . urlencode((string) ($booking['city'] ?? '')));
$status = (string) ($booking['status'] ?? 'pending');
$statusLabel = $statusLabels[$status] ?? ucfirst($status);
$dateRange = booking_date_range((string) $booking['check_in'], (string) $booking['check_out']);
$price = money($booking['total_amount'], (string) ($booking['currency'] ?? 'XOF'));
$tab = booking_reservation_tab($booking);
?>
<article class="reservation-row<?= $isUnread ? ' is-unread' : '' ?>" data-booking-id="<?= $bookingId ?>" data-booking-tab="<?= e($tab) ?>">
    <a href="<?= e($detailUrl) ?>" class="reservation-row-link">
        <div class="reservation-row-thumb">
            <img src="<?= e(property_image($booking['primary_image'] ?? null, (string) ($booking['type'] ?? 'autre'))) ?>"
                 alt=""
                 loading="lazy">
        </div>
        <div class="reservation-row-body">
            <h3 class="reservation-row-title"><?= e(translated((string) $booking['title'])) ?></h3>
            <p class="reservation-row-meta"><?= e($dateRange) ?> · <?= e($price) ?></p>
            <p class="reservation-row-status"><?= e($statusLabel) ?></p>
        </div>
    </a>
    <?php if ($isUnread): ?>
        <span class="reservations-unread-dot" aria-hidden="true">1</span>
    <?php endif; ?>
    <div class="reservation-row-actions">
        <a href="<?= e($propertyUrl) ?>"
           class="reservation-row-menu"
           aria-label="<?= e(__('reservations.view_listing')) ?>">
            <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
        </a>
    </div>
</article>
