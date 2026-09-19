<?php
/** @var array<string, mixed> $item */
$cardCompact = $cardCompact ?? false;
$cardShowCity = $cardShowCity ?? $cardCompact;
$hasRating = (float) ($item['avg_rating'] ?? 0) > 0;
?>
<a href="<?= url('/properties/' . (int) $item['id']) ?>"
   class="property-card text-decoration-none text-reset<?= $cardCompact ? ' property-card--compact' : '' ?>">
    <div class="property-card-image">
        <img src="<?= e(property_image($item['primary_image'] ?? null, (string) $item['type'])) ?>"
             alt="<?= e(translated((string) $item['title'])) ?>" loading="lazy">
        <?php if ($cardCompact && $hasRating): ?>
            <span class="property-card-rating">
                <i class="bi bi-star-fill"></i> <?= e(rating_display($item['avg_rating'])) ?>
            </span>
        <?php endif; ?>
        <?php if ($cardCompact): ?>
            <span class="property-card-type"><?= e(property_type((string) $item['type'])) ?></span>
        <?php endif; ?>
        <button type="button"
                class="fav-btn"
                aria-label="<?= e(__('common.add_favorite')) ?>"
                data-favorite-type="property"
                data-favorite-id="<?= (int) $item['id'] ?>"
                onclick="event.preventDefault(); event.stopPropagation();">
            <i class="bi bi-heart"></i>
        </button>
    </div>
    <div class="property-card-body">
        <?php if (!$cardCompact): ?>
            <div class="property-card-meta">
                <span class="badge-type"><?= e(property_type((string) $item['type'])) ?></span>
                <?php if ($hasRating): ?>
                    <span class="rating"><i class="bi bi-star-fill"></i> <?= e(rating_display($item['avg_rating'])) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <h3><?= e(translated((string) $item['title'])) ?></h3>
        <?php if (!$cardCompact): ?>
            <p class="location"><i class="bi bi-geo-alt"></i> <?= e(location_line($item)) ?></p>
        <?php elseif ($cardShowCity && ($item['city'] ?? '') !== ''): ?>
            <p class="property-card-city"><?= e((string) $item['city']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['price_per_night'])): ?>
            <p class="price"><?= e(money($item['price_per_night'], (string) ($item['price_currency'] ?? 'XOF'))) ?> <span><?= e(__('common.per_night')) ?></span></p>
        <?php endif; ?>
    </div>
</a>
