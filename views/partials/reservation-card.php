<?php
/** @var array<string, mixed> $booking */
/** @var array<string, string> $statusLabels */
/** @var array<string, string> $statusClasses */

$propertyId = (int) $booking['property_id'];
$propertyUrl = url('/properties/' . $propertyId);
$status = (string) ($booking['status'] ?? 'pending');
$statusLabel = $statusLabels[$status] ?? ucfirst($status);
$statusClass = $statusClasses[$status] ?? 'reservation-status-pending';
$nights = (int) ($booking['nights'] ?? 0);
?>
<a href="<?= e($propertyUrl) ?>" class="reservation-card">
    <div class="reservation-card-image">
        <img src="<?= e(property_image($booking['primary_image'] ?? null, (string) ($booking['type'] ?? 'autre'))) ?>"
             alt="<?= e(translated((string) $booking['title'])) ?>"
             loading="lazy">
    </div>
    <div class="reservation-card-body">
        <div class="reservation-card-head">
            <h2><?= e(translated((string) $booking['title'])) ?></h2>
            <span class="reservation-status <?= e($statusClass) ?>"><?= e($statusLabel) ?></span>
        </div>
        <p class="reservation-location">
            <i class="bi bi-geo-alt"></i>
            <?= e(location_line($booking)) ?>
        </p>
        <p class="reservation-dates">
            <i class="bi bi-calendar3"></i>
            <?= e(format_date((string) $booking['check_in'], 'd/m/Y')) ?>
            →
            <?= e(format_date((string) $booking['check_out'], 'd/m/Y')) ?>
            · <?= $nights ?> <?= $nights > 1 ? e(__('reservations.nights')) : e(__('reservations.night')) ?>
        </p>
        <p class="reservation-meta">
            <span><i class="bi bi-people"></i> <?= (int) $booking['guests'] ?> <?= e(__('reservations.guests')) ?></span>
            <span class="reservation-price"><?= e(money($booking['total_amount'], (string) ($booking['currency'] ?? 'XOF'))) ?></span>
        </p>
    </div>
</a>
