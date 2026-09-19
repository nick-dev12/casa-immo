<?php
/**
 * @var string $listingType property|land
 * @var array<string, mixed> $listing
 * @var list<array<string, mixed>> $images
 * @var array<string, string> $motives
 * @var list<array<string, mixed>> $history
 * @var string $backUrl
 * @var string $publicUrl
 */

$activeNav = $listingType === 'land' ? 'lands' : 'properties';
include base_path('views/admin/partials/header.php');

$listingId = (int) ($listing['id'] ?? 0);
$status = (string) ($listing['status'] ?? 'draft');
$ownerName = trim((string) ($listing['first_name'] ?? '') . ' ' . (string) ($listing['last_name'] ?? ''));
$ownerEmail = trim((string) ($listing['email'] ?? ''));
$ownerPhone = trim((string) ($listing['phone'] ?? ''));
$ownerStatus = (string) ($listing['owner_status'] ?? 'active');
$canRestore = in_array($status, ['suspended', 'rejected'], true);

if ($listingType === 'land') {
    $typeLabel = land_type((string) ($listing['land_type'] ?? 'autre'));
    $priceLine = money((float) ($listing['price'] ?? 0), (string) ($listing['currency'] ?? 'XOF'));
    $metaLine = e((string) ($listing['city'] ?? ''));
    if (!empty($listing['district'])) {
        $metaLine .= ' · ' . e((string) $listing['district']);
    }
    if (!empty($listing['area'])) {
        $metaLine .= ' · ' . e(land_area($listing['area'], (string) ($listing['area_unit'] ?? 'm2')));
    }
} else {
    $typeLabel = property_type((string) ($listing['type'] ?? 'autre'));
    $isMonthly = (float) ($listing['price_per_month'] ?? 0) > 0
        && (float) ($listing['price_per_night'] ?? 0) <= 0;
    $priceValue = $isMonthly
        ? (float) ($listing['price_per_month'] ?? 0)
        : (float) ($listing['price_per_night'] ?? 0);
    $priceSuffix = $isMonthly ? __('common.per_month') : __('common.per_night');
    $priceLine = $priceValue > 0
        ? money($priceValue, (string) ($listing['price_currency'] ?? 'XOF')) . ' ' . $priceSuffix
        : '—';
    $metaLine = e((string) ($listing['city'] ?? ''));
    if (!empty($listing['district'])) {
        $metaLine .= ' · ' . e((string) $listing['district']);
    }
}

$establishmentName = trim((string) ($listing['establishment_name'] ?? ''));
$establishmentPhone = trim((string) ($listing['establishment_phone'] ?? ''));
$establishmentAddress = trim((string) ($listing['establishment_address'] ?? ''));
$establishmentCity = trim((string) ($listing['establishment_city'] ?? ''));
?>
<a href="<?= e($backUrl) ?>" class="admin-listing-back">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <?= e(__('admin.moderation.back_list')) ?>
</a>

<div class="admin-listing-detail">
    <section class="admin-listing-main admin-panel">
        <div class="admin-listing-gallery">
            <?php if ($images !== []): ?>
                <div class="admin-listing-gallery-grid">
                    <?php foreach ($images as $index => $image): ?>
                        <?php
                        $imagePath = (string) ($image['path'] ?? '');
                        $imageUrl = $listingType === 'land'
                            ? land_image($imagePath, (string) ($listing['land_type'] ?? 'autre'))
                            : property_image($imagePath, (string) ($listing['type'] ?? 'autre'));
                        ?>
                        <figure class="admin-listing-gallery-item<?= $index === 0 ? ' is-cover' : '' ?>">
                            <img src="<?= e($imageUrl) ?>" alt="" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>">
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="admin-listing-gallery-empty">
                    <i class="bi bi-image" aria-hidden="true"></i>
                    <span><?= e(__('admin.moderation.no_photos')) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-listing-info">
            <div class="admin-listing-info-head">
                <div>
                    <span class="admin-listing-type"><?= e($typeLabel) ?></span>
                    <h2 class="admin-listing-title"><?= e((string) ($listing['title'] ?? '')) ?></h2>
                    <p class="admin-listing-meta"><?= $metaLine ?></p>
                </div>
                <span class="admin-pill admin-pill-<?= e($status) ?>"><?= e(__('host.status.' . $status)) ?></span>
            </div>

            <p class="admin-listing-price"><?= e($priceLine) ?></p>

            <?php if (!empty($listing['address'])): ?>
                <p class="admin-listing-address">
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    <?= e((string) $listing['address']) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($listing['description'])): ?>
                <div class="admin-listing-description">
                    <h3><?= e(__('admin.moderation.description')) ?></h3>
                    <p><?= nl2br(e((string) $listing['description'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="admin-listing-links">
                <a href="<?= e($publicUrl) ?>" class="admin-btn admin-btn-outline" target="_blank" rel="noopener">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                    <?= e(__('admin.moderation.view_public')) ?>
                </a>
            </div>
        </div>
    </section>

    <aside class="admin-listing-side">
        <section class="admin-panel admin-contact-card">
            <h2 class="admin-panel-title"><?= e(__('admin.moderation.publisher')) ?></h2>
            <div class="admin-contact-profile">
                <span class="admin-contact-avatar" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
                <div>
                    <strong class="admin-contact-name"><?= e($ownerName !== '' ? $ownerName : __('admin.moderation.unknown_user')) ?></strong>
                    <span class="admin-pill admin-pill-<?= e(match ($ownerStatus) {
                        'active' => 'approved',
                        'banned' => 'rejected',
                        default => 'draft',
                    }) ?>">
                        <?= e(__('admin.moderation.owner_status.' . $ownerStatus)) ?>
                    </span>
                </div>
            </div>

            <ul class="admin-contact-list">
                <?php if ($ownerEmail !== ''): ?>
                    <li>
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <a href="mailto:<?= e($ownerEmail) ?>"><?= e($ownerEmail) ?></a>
                    </li>
                <?php endif; ?>
                <?php if ($ownerPhone !== ''): ?>
                    <li>
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <a href="tel:<?= e(preg_replace('/\s+/', '', $ownerPhone)) ?>"><?= e($ownerPhone) ?></a>
                    </li>
                <?php endif; ?>
                <?php if (!empty($listing['owner_since'])): ?>
                    <li>
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                        <?= e(__('admin.moderation.member_since', ['date' => format_date((string) $listing['owner_since'])])) ?>
                    </li>
                <?php endif; ?>
            </ul>

            <?php if ($establishmentName !== ''): ?>
                <div class="admin-contact-establishment">
                    <h3><?= e(__('admin.moderation.establishment')) ?></h3>
                    <p><strong><?= e($establishmentName) ?></strong></p>
                    <?php if ($establishmentCity !== ''): ?>
                        <p><?= e($establishmentCity) ?><?= $establishmentAddress !== '' ? ' · ' . e($establishmentAddress) : '' ?></p>
                    <?php endif; ?>
                    <?php if ($establishmentPhone !== ''): ?>
                        <p>
                            <a href="tel:<?= e(preg_replace('/\s+/', '', $establishmentPhone)) ?>">
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                <?= e($establishmentPhone) ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php
        $moderationTargetType = $listingType;
        $moderationTargetId = $listingId;
        $canRestore = $canRestore;
        include base_path('views/admin/partials/moderation-form.php');
        ?>

        <?php if ($history !== []): ?>
            <section class="admin-panel admin-moderation-history">
                <h2 class="admin-panel-title"><?= e(__('admin.moderation.history')) ?></h2>
                <ul class="admin-moderation-history-list">
                    <?php foreach ($history as $entry): ?>
                        <?php
                        $entryMotives = is_array($entry['motives'] ?? null) ? $entry['motives'] : [];
                        $entryAction = (string) ($entry['action'] ?? '');
                        $adminName = trim((string) ($entry['first_name'] ?? '') . ' ' . (string) ($entry['last_name'] ?? ''));
                        ?>
                        <li class="admin-moderation-history-item">
                            <div class="admin-moderation-history-head">
                                <strong><?= e(__('admin.moderation.action.' . $entryAction)) ?></strong>
                                <time datetime="<?= e((string) ($entry['created_at'] ?? '')) ?>">
                                    <?= e(format_date((string) ($entry['created_at'] ?? ''), 'd/m/Y H:i')) ?>
                                </time>
                            </div>
                            <?php if ($entryMotives !== []): ?>
                                <ul class="admin-moderation-motives">
                                    <?php foreach ($entryMotives as $motiveSlug): ?>
                                        <li><?= e($motives[$motiveSlug] ?? $motiveSlug) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (!empty($entry['notes'])): ?>
                                <p class="admin-moderation-notes"><?= e((string) $entry['notes']) ?></p>
                            <?php endif; ?>
                            <p class="admin-moderation-by"><?= e(__('admin.moderation.by_admin', ['name' => $adminName])) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>
    </aside>
</div>

<?php include base_path('views/admin/partials/footer.php'); ?>
