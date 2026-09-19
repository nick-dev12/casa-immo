<?php
/** @var array<int, array<string, mixed>> $travelers */
?>
<div class="profile-panel">
    <?php if ($travelers === []): ?>
        <div class="profile-empty">
            <i class="bi bi-people" aria-hidden="true"></i>
            <p><?= e(__('profile.travelers_empty')) ?></p>
        </div>
    <?php else: ?>
        <div class="profile-list">
            <?php foreach ($travelers as $traveler): ?>
                <article class="profile-list-item">
                    <div class="profile-list-main">
                        <strong><?= e(trim((string) $traveler['first_name'] . ' ' . (string) $traveler['last_name'])) ?></strong>
                        <?php if (!empty($traveler['email'])): ?>
                            <span><?= e((string) $traveler['email']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($traveler['phone'])): ?>
                            <span><?= e((string) $traveler['phone']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="profile-list-meta">
                        <?= e(format_date((string) $traveler['check_in'], 'd/m/Y')) ?>
                        →
                        <?= e(format_date((string) $traveler['check_out'], 'd/m/Y')) ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
