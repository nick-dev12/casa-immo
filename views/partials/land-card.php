<?php
/** @var array<string, mixed> $item */
$cardCompact = $cardCompact ?? false;
$landTypeLabel = land_type((string) $item['land_type']);
?>
<a href="<?= url('/lands/' . (int) $item['id']) ?>"
   class="property-card text-decoration-none text-reset<?= $cardCompact ? ' property-card--compact' : '' ?>">
    <div class="property-card-image">
        <img src="<?= e(land_image($item['primary_image'] ?? null, (string) $item['land_type'])) ?>"
             alt="<?= e(translated((string) $item['title'])) ?>" loading="lazy">
        <?php if ($cardCompact): ?>
            <span class="property-card-type"><?= e($landTypeLabel) ?></span>
        <?php endif; ?>
        <button type="button"
                class="fav-btn"
                aria-label="<?= e(__('common.add_favorite')) ?>"
                data-favorite-type="land"
                data-favorite-id="<?= (int) $item['id'] ?>"
                onclick="event.preventDefault(); event.stopPropagation();">
            <i class="bi bi-heart"></i>
        </button>
    </div>
    <div class="property-card-body">
        <?php if (!$cardCompact): ?>
            <div class="property-card-meta">
                <span class="badge-type"><?= e($landTypeLabel) ?></span>
                <?php if (!empty($item['paper_type'])): ?>
                    <span class="badge-paper"><?= e(land_paper_type((string) $item['paper_type'])) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <h3><?= e(translated((string) $item['title'])) ?></h3>
        <?php if (!$cardCompact): ?>
            <p class="location">
                <i class="bi bi-geo-alt"></i>
                <?= e(location_line($item)) ?> · <?= e(land_area($item['area'], (string) $item['area_unit'])) ?>
            </p>
        <?php endif; ?>
        <p class="price"><?= e(money($item['price'], (string) ($item['currency'] ?? 'XOF'))) ?></p>
    </div>
</a>
