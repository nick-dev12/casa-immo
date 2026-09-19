<?php /** @var array<string, mixed> $item */ ?>
<a href="<?= url('/properties/' . (int) $item['id']) ?>" class="nearby-card text-decoration-none text-reset">
    <div class="nearby-card-image">
        <img src="<?= e(property_image($item['primary_image'] ?? null, (string) $item['type'])) ?>"
             alt="<?= e($item['title']) ?>" loading="lazy">
        <button type="button" class="fav-btn fav-btn-sm" aria-label="Ajouter aux favoris"
                onclick="event.preventDefault(); event.stopPropagation();">
            <i class="bi bi-heart"></i>
        </button>
    </div>
    <div class="nearby-card-body">
        <div class="property-card-meta">
            <span class="badge-type"><?= e(property_type((string) $item['type'])) ?></span>
            <?php if ((float) ($item['avg_rating'] ?? 0) > 0): ?>
                <span class="rating"><i class="bi bi-star-fill"></i> <?= e(rating_display($item['avg_rating'])) ?></span>
            <?php endif; ?>
        </div>
        <h3><?= e($item['title']) ?></h3>
        <p class="location"><i class="bi bi-geo-alt"></i> <?= e(location_line($item)) ?></p>
        <?php if (!empty($item['price_per_night'])): ?>
            <p class="price"><?= e(money($item['price_per_night'], (string) ($item['price_currency'] ?? 'XOF'))) ?> <span>/ nuit</span></p>
        <?php endif; ?>
    </div>
</a>
