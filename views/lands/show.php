<?php
/** @var array<string, mixed> $land */
/** @var array<int, array<string, mixed>> $images */
/** @var array<string, mixed>|null $seller */

$mainImage = $images[0]['path'] ?? ($land['primary_image'] ?? null);
$displayTitle = translated((string) $land['title']);
$displayDescription = translated((string) ($land['description'] ?? ''));
$seller = $seller ?? null;
$sellerId = (int) ($seller['id'] ?? $land['seller_id'] ?? 0);
$isOwnListing = \App\Helpers\AuthHelper::id() !== null && \App\Helpers\AuthHelper::id() === $sellerId;
?>

<section class="detail-hero">
    <div class="detail-hero-image">
        <img src="<?= e(land_image($mainImage, (string) $land['land_type'])) ?>" alt="<?= e($displayTitle) ?>" fetchpriority="high">
        <a href="<?= url('/lands') ?>" class="detail-back"><i class="bi bi-arrow-left"></i></a>
        <div class="detail-actions">
            <button type="button" class="detail-action-btn"><i class="bi bi-share"></i></button>
            <button type="button" class="detail-action-btn fav-btn"><i class="bi bi-heart"></i></button>
        </div>
    </div>
</section>

<section class="container app-container detail-content pb-5">
    <div class="detail-meta-row">
        <span class="badge-type"><?= e(land_type((string) $land['land_type'])) ?></span>
        <?php if (!empty($land['paper_type'])): ?>
            <span class="badge-paper"><i class="bi bi-file-earmark-text"></i> <?= e(land_paper_type((string) $land['paper_type'])) ?></span>
        <?php endif; ?>
    </div>

    <h1 class="detail-title"><?= e($displayTitle) ?></h1>
    <p class="location"><i class="bi bi-geo-alt"></i> <?= e(location_line($land)) ?></p>

    <div class="detail-specs">
        <?php
        $landDimensions = land_dimensions($land['length_m'] ?? null, $land['width_m'] ?? null);
        ?>
        <?php if ($landDimensions !== ''): ?>
            <span><i class="bi bi-bounding-box"></i> <?= e($landDimensions) ?></span>
        <?php endif; ?>
        <?php if (!empty($land['paper_type'])): ?>
            <span><i class="bi bi-file-earmark-text"></i> <?= e(land_paper_type((string) $land['paper_type'])) ?></span>
        <?php endif; ?>
        <span><i class="bi bi-rulers"></i> <?= e(land_area($land['area'], (string) $land['area_unit'])) ?></span>
        <span><i class="bi bi-tag"></i> <?= e(money($land['price'], (string) ($land['currency'] ?? 'XOF'))) ?></span>
    </div>

    <?php
    $planLength = (float) ($land['length_m'] ?? 0);
    $planWidth = (float) ($land['width_m'] ?? 0);
    if ($planLength > 0 && $planWidth > 0):
        $planMode = 'static';
        $planArea = (float) ($land['area'] ?? 0);
        $planUnit = (string) ($land['area_unit'] ?? 'm2');
        include base_path('views/partials/land-plan-preview.php');
    endif;
    ?>

    <?php if (count($images) > 1): ?>
        <div class="detail-gallery mt-3">
            <?php foreach ($images as $index => $image): ?>
                <img src="<?= e(land_image($image['path'] ?? null, (string) $land['land_type'])) ?>" alt="Photo <?= $index + 1 ?>" loading="lazy">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="detail-section mt-4">
        <h2>Description</h2>
        <p class="text-muted"><?= nl2br(e($displayDescription)) ?></p>
    </div>

    <?php
    $listing = $land;
    $mapTitle = $displayTitle;
    include base_path('views/partials/listing-map.php');
    ?>

    <?php if ($seller !== null): ?>
        <div class="detail-section mt-4">
            <h2><?= e(__('messages.seller_title')) ?></h2>
            <div class="d-flex align-items-center gap-3">
                <div class="messages-avatar messages-avatar-sm" aria-hidden="true" style="background:var(--zig-primary-soft);color:var(--zig-primary);width:2.5rem;height:2.5rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-weight:700;">
                    <?= e(user_initials($seller)) ?>
                </div>
                <div>
                    <strong><?= e(\App\Helpers\AuthHelper::fullName($seller)) ?></strong>
                    <p class="text-muted mb-0 small"><?= e(__('messages.seller_meta')) ?></p>
                </div>
                <?php if (!$isOwnListing): ?>
                    <a href="<?= e(messages_start_url(null, (int) $land['id'])) ?>" class="btn btn-outline-primary ms-auto">
                        <i class="bi bi-chat-dots"></i> <?= e(__('messages.contact')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="detail-book-bar">
    <div class="container app-container d-flex justify-content-between align-items-center">
        <div>
            <small class="text-muted d-block">Prix</small>
            <strong class="price mb-0"><?= e(money($land['price'], (string) ($land['currency'] ?? 'XOF'))) ?></strong>
        </div>
        <?php if (!$isOwnListing): ?>
            <a href="<?= e(messages_start_url(null, (int) $land['id'])) ?>" class="btn btn-outline-primary me-2">
                <i class="bi bi-chat-dots"></i> <?= e(__('messages.contact')) ?>
            </a>
        <?php endif; ?>
        <button type="button" class="btn btn-primary btn-book">Demander une visite</button>
    </div>
</div>
