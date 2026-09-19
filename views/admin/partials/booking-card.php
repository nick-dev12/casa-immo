<?php
/** @var array<string, mixed> $item */

$bookingId = (int) ($item['id'] ?? 0);
$status = (string) ($item['status'] ?? 'pending');
$guestName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? ''));
$imageCount = (int) ($item['image_count'] ?? 0);
$propertyType = (string) ($item['type'] ?? 'autre');
$propertyId = (int) ($item['property_id'] ?? 0);
$detailUrl = $propertyId > 0
    ? url('/admin/properties/' . $propertyId)
    : url('/reservations/' . $bookingId);
$dateRange = booking_date_range((string) ($item['check_in'] ?? ''), (string) ($item['check_out'] ?? ''));
$currency = (string) ($item['currency'] ?? 'XOF');
$totalAmount = (float) ($item['total_amount'] ?? 0);
$commissionAmount = (float) ($item['commission_amount'] ?? 0);
$commissionRate = (float) ($item['commission_rate'] ?? 0);
$ownerNet = max(0, (float) ($item['subtotal'] ?? $totalAmount) - $commissionAmount);
$hasCommission = $commissionAmount > 0;
?>
<article class="admin-property-card">
    <a href="<?= e($detailUrl) ?>" class="admin-property-cover" aria-label="<?= e((string) ($item['title'] ?? '')) ?>">
        <img src="<?= e(property_image($item['primary_image'] ?? null, $propertyType)) ?>"
             alt=""
             loading="lazy">
        <span class="admin-property-type"><?= e(property_type($propertyType)) ?></span>
        <?php if ($imageCount > 1): ?>
            <span class="admin-property-photo-count">
                <i class="bi bi-images" aria-hidden="true"></i>
                <?= (int) $imageCount ?>
            </span>
        <?php endif; ?>
    </a>

    <div class="admin-property-body">
        <div class="admin-property-head">
            <h3 class="admin-property-title">
                <a href="<?= e($detailUrl) ?>"><?= e((string) ($item['title'] ?? '')) ?></a>
            </h3>
            <span class="admin-pill admin-pill-<?= e($status) ?>"><?= e(__('reservations.status.' . $status)) ?></span>
        </div>

        <p class="admin-property-meta">
            <i class="bi bi-geo-alt" aria-hidden="true"></i>
            <?= e((string) ($item['city'] ?? '')) ?>
        </p>

        <?php if ($guestName !== ''): ?>
            <p class="admin-property-owner">
                <i class="bi bi-person" aria-hidden="true"></i>
                <?= e($guestName) ?>
            </p>
        <?php endif; ?>

        <p class="admin-property-agency">
            <i class="bi bi-calendar3" aria-hidden="true"></i>
            <?= e($dateRange) ?>
        </p>

        <?php if ($totalAmount > 0): ?>
            <div class="admin-booking-amounts">
                <p class="admin-property-price">
                    <?= e(money($totalAmount, $currency)) ?>
                    <span><?= e(__('admin.bookings_card.total')) ?></span>
                </p>
                <?php if ($hasCommission): ?>
                    <p class="admin-booking-commission">
                        <i class="bi bi-percent" aria-hidden="true"></i>
                        <?= e(__('admin.bookings_card.commission', [
                            'amount' => money($commissionAmount, $currency),
                            'rate' => rtrim(rtrim(number_format($commissionRate, 2, '.', ''), '0'), '.'),
                        ])) ?>
                    </p>
                    <p class="admin-booking-owner-net">
                        <i class="bi bi-wallet2" aria-hidden="true"></i>
                        <?= e(__('admin.bookings_card.owner_net', ['amount' => money($ownerNet, $currency)])) ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>
