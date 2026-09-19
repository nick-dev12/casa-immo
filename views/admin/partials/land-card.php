<?php
/** @var array<string, mixed> $item */

$landId = (int) ($item['id'] ?? 0);
$status = (string) ($item['status'] ?? 'draft');
$ownerName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? ''));
$imageCount = (int) ($item['image_count'] ?? 0);
$detailUrl = url('/admin/lands/' . $landId);
$landType = (string) ($item['land_type'] ?? 'autre');
?>
<article class="admin-property-card">
    <a href="<?= e($detailUrl) ?>" class="admin-property-cover" aria-label="<?= e((string) ($item['title'] ?? '')) ?>">
        <img src="<?= e(land_image($item['primary_image'] ?? null, $landType)) ?>"
             alt=""
             loading="lazy">
        <span class="admin-property-type"><?= e(land_type($landType)) ?></span>
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
            <?php if (!empty($item['area'])): ?>
                · <?= e(land_area($item['area'], (string) ($item['area_unit'] ?? 'm2'))) ?>
            <?php endif; ?>
        </p>

        <?php if ($ownerName !== ''): ?>
            <p class="admin-property-owner">
                <i class="bi bi-person" aria-hidden="true"></i>
                <?= e($ownerName) ?>
            </p>
        <?php endif; ?>

        <?php if ((float) ($item['price'] ?? 0) > 0): ?>
            <p class="admin-property-price">
                <?= e(money((float) $item['price'], (string) ($item['currency'] ?? 'XOF'))) ?>
            </p>
        <?php endif; ?>
    </div>
</article>
