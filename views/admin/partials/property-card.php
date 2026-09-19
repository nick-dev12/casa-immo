<?php
/** @var array<string, mixed> $item */

$propertyId = (int) ($item['id'] ?? 0);
$status = (string) ($item['status'] ?? 'draft');
$ownerName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? ''));
$isMonthly = (float) ($item['price_per_month'] ?? 0) > 0
    && (float) ($item['price_per_night'] ?? 0) <= 0;
$priceValue = $isMonthly
    ? (float) ($item['price_per_month'] ?? 0)
    : (float) ($item['price_per_night'] ?? 0);
$priceSuffix = $isMonthly ? __('common.per_month') : __('common.per_night');
$imageCount = (int) ($item['image_count'] ?? 0);
$detailUrl = url('/admin/properties/' . $propertyId);
?>
<article class="admin-property-card">
    <a href="<?= e($detailUrl) ?>" class="admin-property-cover" aria-label="<?= e((string) ($item['title'] ?? '')) ?>">
        <img src="<?= e(property_image($item['primary_image'] ?? null, (string) ($item['type'] ?? 'autre'))) ?>"
             alt=""
             loading="lazy">
        <span class="admin-property-type"><?= e(property_type((string) ($item['type'] ?? 'autre'))) ?></span>
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
            <span class="admin-pill admin-pill-<?= e($status) ?>"><?= e(__('host.status.' . $status)) ?></span>
        </div>

        <p class="admin-property-meta">
            <i class="bi bi-geo-alt" aria-hidden="true"></i>
            <?= e((string) ($item['city'] ?? '')) ?>
        </p>

        <?php if ($ownerName !== ''): ?>
            <p class="admin-property-owner">
                <i class="bi bi-person" aria-hidden="true"></i>
                <?= e($ownerName) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($item['establishment_name'])): ?>
            <p class="admin-property-agency">
                <i class="bi bi-building" aria-hidden="true"></i>
                <?= e((string) $item['establishment_name']) ?>
            </p>
        <?php endif; ?>

        <?php if ($priceValue > 0): ?>
            <p class="admin-property-price">
                <?= e(money($priceValue, (string) ($item['price_currency'] ?? 'XOF'))) ?>
                <span><?= e($priceSuffix) ?></span>
            </p>
        <?php endif; ?>
    </div>
</article>
