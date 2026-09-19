<?php
/** @var array<string, mixed> $booking */
/** @var array<string, string> $statusLabels */
/** @var array<string, string> $statusClasses */

$bookingId = (int) $booking['id'];
$propertyId = (int) $booking['property_id'];
$propertyUrl = url('/properties/' . $propertyId);
$status = (string) ($booking['status'] ?? 'pending');
$statusLabel = $statusLabels[$status] ?? ucfirst($status);
$statusClass = $statusClasses[$status] ?? 'reservation-status-pending';
$reference = booking_reference($bookingId);
$city = (string) ($booking['city'] ?? '');
$district = trim((string) ($booking['district'] ?? ''));
$destinationLabel = $district !== '' ? $district : translated((string) ($booking['title'] ?? ''));
$nights = (int) ($booking['nights'] ?? 0);
?>
<article class="reservation-ticket">
    <a href="<?= e($propertyUrl) ?>"
       class="reservation-ticket-info"
       aria-label="<?= e(__('reservations.ticket.info')) ?>">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
    </a>

    <div class="reservation-ticket-top">
        <div class="reservation-ticket-qr-wrap">
            <img src="<?= e(booking_qr_url($reference)) ?>"
                 alt="<?= e(__('reservations.ticket.qr_alt')) ?>"
                 class="reservation-ticket-qr"
                 width="168"
                 height="168"
                 loading="lazy"
                 decoding="async">
            <p class="reservation-ticket-ref"><?= e($reference) ?></p>
        </div>
    </div>

    <div class="reservation-ticket-divider" aria-hidden="true"></div>

    <a href="<?= e($propertyUrl) ?>" class="reservation-ticket-body">
        <div class="reservation-ticket-header">
            <span class="reservation-ticket-brand" aria-hidden="true">Z</span>
            <span class="reservation-ticket-date"><?= e(format_date((string) $booking['check_in'], 'ticket')) ?></span>
            <span class="reservation-ticket-route">
                <?= e(city_abbrev($city)) ?> · <?= e(booking_status_abbrev($status)) ?>
            </span>
        </div>

        <div class="reservation-ticket-journey">
            <div class="reservation-ticket-stop">
                <span class="reservation-ticket-stop-label"><?= e($city) ?></span>
                <span class="reservation-ticket-stop-code"><?= e(format_date((string) $booking['check_in'], 'd/m')) ?></span>
            </div>
            <span class="reservation-ticket-arrow" aria-hidden="true">→</span>
            <div class="reservation-ticket-stop reservation-ticket-stop-end">
                <span class="reservation-ticket-stop-label"><?= e($destinationLabel) ?></span>
                <span class="reservation-ticket-stop-code"><?= e(format_date((string) $booking['check_out'], 'd/m')) ?></span>
            </div>
        </div>

        <div class="reservation-ticket-grid">
            <div class="reservation-ticket-field">
                <span class="reservation-ticket-label"><?= e(__('reservations.ticket.type')) ?></span>
                <span class="reservation-ticket-value"><?= e(property_type((string) ($booking['type'] ?? 'autre'))) ?></span>
            </div>
            <div class="reservation-ticket-field">
                <span class="reservation-ticket-label"><?= e(__('reservations.ticket.total')) ?></span>
                <span class="reservation-ticket-value"><?= e(money($booking['total_amount'], (string) ($booking['currency'] ?? 'XOF'))) ?></span>
            </div>
            <div class="reservation-ticket-field reservation-ticket-field-sm">
                <span class="reservation-ticket-label"><?= e(__('reservations.ticket.arrival')) ?></span>
                <span class="reservation-ticket-value"><?= e(format_date((string) $booking['check_in'], 'd/m/Y')) ?></span>
            </div>
            <div class="reservation-ticket-field reservation-ticket-field-sm">
                <span class="reservation-ticket-label"><?= e(__('reservations.ticket.departure')) ?></span>
                <span class="reservation-ticket-value"><?= e(format_date((string) $booking['check_out'], 'd/m/Y')) ?></span>
            </div>
            <div class="reservation-ticket-field reservation-ticket-field-sm">
                <span class="reservation-ticket-label"><?= e(__('reservations.ticket.guests')) ?></span>
                <span class="reservation-ticket-value"><?= (int) $booking['guests'] ?></span>
            </div>
        </div>

        <div class="reservation-ticket-meta">
            <span class="reservation-status <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
            <span class="reservation-ticket-nights">
                <?= $nights ?> <?= $nights > 1 ? e(__('reservations.nights')) : e(__('reservations.night')) ?>
            </span>
        </div>

        <footer class="reservation-ticket-footer">
            <span class="reservation-ticket-label"><?= e(__('reservations.ticket.purchased')) ?></span>
            <span class="reservation-ticket-value"><?= e(format_date((string) ($booking['created_at'] ?? ''), 'datetime')) ?></span>
        </footer>
    </a>
</article>
