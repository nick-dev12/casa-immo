<?php
/**
 * Carte annonce hôte (logement ou terrain).
 *
 * @var array<string, mixed> $listing
 * @var string $kind logement|terrain
 * @var array<string, string> $statusLabels
 */

$kind = $kind ?? 'logement';
$listingId = (int) ($listing['id'] ?? 0);
$status = (string) ($listing['status'] ?? 'draft');
$statusLabel = $statusLabels[$status] ?? ucfirst($status);
$primaryImage = (string) ($listing['primary_image'] ?? '');
$imageCount = (int) ($listing['image_count'] ?? 0);
$editUrl = $kind === 'terrain'
    ? url('/host/lands/' . $listingId . '/edit')
    : url('/host/properties/' . $listingId . '/edit');
$publishUrl = $kind === 'terrain'
    ? url('/host/lands/' . $listingId . '/publish')
    : url('/host/properties/' . $listingId . '/publish');
$canPublish = in_array($status, ['draft', 'rejected'], true);

if ($kind === 'terrain') {
    $metaLine = e((string) ($listing['city'] ?? ''))
        . ' · ' . e(land_type((string) ($listing['land_type'] ?? 'autre')));
    if (!empty($listing['paper_type'])) {
        $metaLine .= ' · ' . e(land_paper_type((string) $listing['paper_type']));
    }
    $dimensions = land_dimensions($listing['length_m'] ?? null, $listing['width_m'] ?? null);
    if ($dimensions !== '') {
        $metaLine .= ' · ' . e($dimensions);
    }
    $metaLine .= ' · ' . e(land_area($listing['area'] ?? 0, (string) ($listing['area_unit'] ?? 'm2')));
    $priceLine = !empty($listing['price'])
        ? e(money((float) $listing['price'], (string) ($listing['currency'] ?? 'XOF')))
        : '';
} else {
    $isMonthly = (float) ($listing['price_per_month'] ?? 0) > 0
        && (float) ($listing['price_per_night'] ?? 0) <= 0;
    $priceValue = $isMonthly
        ? (float) ($listing['price_per_month'] ?? 0)
        : (float) ($listing['price_per_night'] ?? 0);
    $priceSuffix = $isMonthly ? __('common.per_month') : __('common.per_night');
    $metaLine = e((string) ($listing['city'] ?? ''))
        . ' · ' . e(property_type((string) ($listing['type'] ?? 'autre')));
    $priceLine = $priceValue > 0
        ? e(money($priceValue, (string) ($listing['price_currency'] ?? 'XOF'))) . e($priceSuffix)
        : '';
}
?>
<article class="host-listing-card">
    <a href="<?= e($editUrl) ?>" class="host-listing-cover" aria-label="<?= e((string) ($listing['title'] ?? '')) ?>">
        <?php if ($primaryImage !== ''): ?>
            <img src="<?= e(upload_url($primaryImage)) ?>" alt="" loading="lazy">
            <?php if ($imageCount > 1): ?>
                <span class="host-listing-photo-count">
                    <i class="bi bi-images" aria-hidden="true"></i>
                    <?= e(__('host.images.more_count', ['count' => $imageCount])) ?>
                </span>
            <?php endif; ?>
        <?php else: ?>
            <span class="host-listing-cover-empty" aria-hidden="true">
                <i class="bi bi-image"></i>
            </span>
        <?php endif; ?>
    </a>

    <div class="host-listing-body">
        <div class="host-listing-head">
            <div class="host-listing-info">
                <h3 class="host-listing-title">
                    <a href="<?= e($editUrl) ?>"><?= e((string) ($listing['title'] ?? '')) ?></a>
                </h3>
                <p class="host-listing-meta"><?= $metaLine ?></p>
                <?php if ($priceLine !== ''): ?>
                    <p class="host-listing-price"><?= $priceLine ?></p>
                <?php endif; ?>
            </div>
            <span class="host-status host-status-<?= e($status) ?>"><?= e($statusLabel) ?></span>
        </div>

        <div class="host-listing-actions">
            <a href="<?= e($editUrl) ?>" class="host-btn host-btn-sm" aria-label="<?= e(__('host.edit')) ?>">
                <i class="bi bi-pencil" aria-hidden="true"></i>
                <span class="host-btn-label"><?= e(__('host.edit')) ?></span>
            </a>
            <?php if ($kind === 'logement'): ?>
                <a href="<?= url('/host/properties/' . $listingId . '/availability') ?>" class="host-btn host-btn-sm host-btn-outline" aria-label="<?= e(__('host.availability')) ?>">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <span class="host-btn-label"><?= e(__('host.availability')) ?></span>
                </a>
            <?php endif; ?>
            <?php if ($canPublish): ?>
                <form method="post" action="<?= e($publishUrl) ?>" class="host-inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="host-btn host-btn-sm host-btn-primary" aria-label="<?= e(__('host.publish')) ?>">
                        <i class="bi bi-send" aria-hidden="true"></i>
                        <span class="host-btn-label"><?= e(__('host.publish')) ?></span>
                    </button>
                </form>
            <?php endif; ?>
            <form method="post"
                  action="<?= e($kind === 'terrain'
                      ? url('/host/lands/' . $listingId . '/delete')
                      : url('/host/properties/' . $listingId . '/delete')) ?>"
                  class="host-inline-form host-delete-form"
                  data-confirm="<?= e(__('host.delete_confirm')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="host-btn host-btn-sm host-btn-danger" aria-label="<?= e(__('host.delete_listing')) ?>">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                    <span class="host-btn-label"><?= e(__('host.delete_listing')) ?></span>
                </button>
            </form>
        </div>
    </div>
</article>
