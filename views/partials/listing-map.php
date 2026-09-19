<?php
/** @var array<string, mixed> $listing */
/** @var string $mapTitle */

$map = listing_map_urls($listing);
$mapTitle = $mapTitle ?? (string) ($listing['title'] ?? 'Localisation');
$displayAddress = listing_display_address($listing);
$mapLabel = (string) ($map['label'] ?? $mapTitle);
?>

<section class="pd-block" id="pd-map">
    <h2>Où vous serez</h2>
    <?php if ($displayAddress !== ''): ?>
        <p class="pd-map-address"><?= e($displayAddress) ?></p>
    <?php endif; ?>
    <?php if ($map['show']): ?>
        <div class="pd-map pd-map-google" aria-label="Carte Google Maps — position exacte du logement">
            <iframe
                src="<?= e($map['embed']) ?>"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
                title="<?= e($mapLabel) ?> — position exacte sur Google Maps">
            </iframe>
        </div>
        <?php if ($map['latitude'] !== null && $map['longitude'] !== null): ?>
            <p class="pd-map-coords">
                <?= e(__('property.map.coords')) ?> :
                <?= e(number_format((float) $map['latitude'], 8, '.', '')) ?>,
                <?= e(number_format((float) $map['longitude'], 8, '.', '')) ?>
            </p>
        <?php endif; ?>
        <a href="<?= e($map['link']) ?>" class="pd-map-link" target="_blank" rel="noopener noreferrer">
            <i class="bi bi-box-arrow-up-right"></i> <?= e(__('property.map.open_google')) ?>
        </a>
    <?php else: ?>
        <div class="pd-map pd-map-placeholder">
            <i class="bi bi-map"></i>
            <span><?= e(__('property.map.unavailable')) ?></span>
        </div>
        <?php if ($map['link'] !== ''): ?>
            <a href="<?= e($map['link']) ?>" class="pd-map-link" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-box-arrow-up-right"></i> <?= e(__('property.map.view_area')) ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
</section>
