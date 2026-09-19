<?php
/** @var array<int, array<string, mixed>> $notifications */
?>
<div class="profile-panel">
    <?php if ($notifications === []): ?>
        <div class="profile-empty">
            <i class="bi bi-bell" aria-hidden="true"></i>
            <p><?= e(__('profile.notifications_empty')) ?></p>
        </div>
    <?php else: ?>
        <div class="profile-list">
            <?php foreach ($notifications as $notification): ?>
                <article class="profile-list-item<?= empty($notification['is_read']) ? ' is-unread' : '' ?>">
                    <div class="profile-list-main">
                        <strong><?= e((string) $notification['title']) ?></strong>
                        <?php if (!empty($notification['body'])): ?>
                            <span><?= e((string) $notification['body']) ?></span>
                        <?php endif; ?>
                        <span><?= e(format_date((string) $notification['created_at'], 'datetime')) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
