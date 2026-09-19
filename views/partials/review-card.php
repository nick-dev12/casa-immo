<?php /** @var array<string, mixed> $review */ ?>
<article class="pd-review">
    <div class="pd-review-top">
        <div class="pd-review-avatar">
            <?= e(mb_strtoupper(mb_substr((string) $review['first_name'], 0, 1))) ?>
        </div>
        <div>
            <strong><?= e($review['first_name'] . ' ' . mb_substr((string) $review['last_name'], 0, 1) . '.') ?></strong>
            <span><?= e(format_date((string) $review['created_at'], 'month_year')) ?></span>
        </div>
        <div class="pd-review-stars">
            <?php for ($s = 1; $s <= 5; $s++): ?>
                <i class="bi bi-star-fill<?= $s <= (int) $review['rating'] ? '' : ' empty' ?>"></i>
            <?php endfor; ?>
        </div>
    </div>
    <?php if (!empty($review['comment'])): ?>
        <p><?= e($review['comment']) ?></p>
    <?php endif; ?>
</article>
