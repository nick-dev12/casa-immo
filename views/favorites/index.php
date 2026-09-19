<?php
/** @var array<string, mixed> $user */
/** @var array<int, array<string, mixed>> $items */
?>
<section class="account-page">
    <div class="container app-container">
        <header class="account-page-head">
            <h1><?= e(__('favorites.title')) ?></h1>
            <?php if ($items !== []): ?>
                <span class="account-badge"><?= count($items) ?></span>
            <?php endif; ?>
        </header>

        <?php if ($items === []): ?>
            <div class="account-empty">
                <i class="bi bi-heart" aria-hidden="true"></i>
                <h2><?= e(__('favorites.empty_title')) ?></h2>
                <p><?= e(__('favorites.empty_lead')) ?></p>
                <a href="<?= url('/properties') ?>" class="btn btn-primary"><?= e(__('favorites.explore')) ?></a>
            </div>
        <?php else: ?>
            <div class="account-grid listing-grid">
                <?php foreach ($items as $item): ?>
                    <?php if (($item['favorite_type'] ?? '') === 'land'): ?>
                        <?php include base_path('views/partials/land-card.php'); ?>
                    <?php else: ?>
                        <?php include base_path('views/partials/property-card.php'); ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
