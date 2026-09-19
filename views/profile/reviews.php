<?php
/** @var array<int, array<string, mixed>> $reviews */
?>
<div class="profile-panel">
    <?php if ($reviews === []): ?>
        <div class="profile-empty">
            <i class="bi bi-chat-left-dots" aria-hidden="true"></i>
            <p><?= e(__('profile.reviews_empty')) ?></p>
        </div>
    <?php else: ?>
        <div class="profile-list">
            <?php foreach ($reviews as $review): ?>
                <article class="profile-list-item">
                    <div class="profile-list-main">
                        <strong><?= e(translated((string) $review['title'])) ?></strong>
                        <span><?= e((string) $review['city']) ?> · <?= e(format_date((string) $review['created_at'], 'd/m/Y')) ?></span>
                        <span class="profile-review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= (int) $review['rating'] ? '-fill' : '' ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <?php if (!empty($review['comment'])): ?>
                            <p class="profile-review-comment"><?= e((string) $review['comment']) ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="<?= url('/properties/' . (int) $review['property_id']) ?>" class="profile-list-link">
                        <?= e(__('profile.view_listing')) ?>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
