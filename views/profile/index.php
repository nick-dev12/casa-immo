<?php
/** @var array<string, mixed> $user */
/** @var array<string, int> $stats */
/** @var array{level: int, prefix: string, name: string} $tier */
/** @var list<array{title: ?string, items: list<array<string, mixed>>}> $menuGroups */

$fullName = trim((string) $user['first_name'] . ' ' . (string) $user['last_name']);
$avatarUrl = user_avatar_url($user);
$memberSince = format_date((string) ($user['created_at'] ?? ''), 'month_year');
?>
<section class="profile-page" aria-label="<?= e(__('profile.menu')) ?>">
    <header class="profile-topbar">
        <div class="profile-topbar-actions">
            <a href="<?= url('/messages') ?>" class="profile-topbar-btn" aria-label="<?= e(__('nav.messages')) ?>" data-messages-nav>
                <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                <?php $messagesUnread = unread_messages_count(); ?>
                <span class="profile-topbar-badge<?= $messagesUnread > 0 ? ' is-visible' : '' ?>" data-messages-badge<?= $messagesUnread <= 0 ? ' hidden' : '' ?>><?= $messagesUnread > 0 ? $messagesUnread : '' ?></span>
            </a>
            <a href="<?= url('/profile/notifications') ?>" class="profile-topbar-btn" aria-label="<?= e(__('nav.notifications')) ?>">
                <i class="bi bi-bell" aria-hidden="true"></i>
                <?php if ($stats['notification_count'] > 0): ?>
                    <span class="profile-topbar-badge"><?= $stats['notification_count'] ?></span>
                <?php endif; ?>
            </a>
        </div>
    </header>

    <div class="profile-hero">
        <div class="profile-hero-avatar" aria-hidden="true">
            <?php if ($avatarUrl): ?>
                <img src="<?= e($avatarUrl) ?>" alt="" class="profile-hero-photo">
            <?php else: ?>
                <span class="profile-hero-initials"><?= e(user_initials($user)) ?></span>
            <?php endif; ?>
        </div>
        <div class="profile-hero-text">
            <p class="profile-hero-welcome"><?= e(__('profile.welcome')) ?></p>
            <h1 class="profile-hero-name"><?= e($fullName) ?></h1>
            <p class="profile-hero-tier">
                <?= e($tier['prefix']) ?>
                <span class="profile-hero-tier-badge"><?= e($tier['name']) ?></span>
            </p>
            <p class="profile-hero-email"><?= e((string) $user['email']) ?></p>
            <?php if (!empty($user['phone'])): ?>
                <p class="profile-hero-phone"><i class="bi bi-telephone"></i> <?= e((string) $user['phone']) ?></p>
            <?php endif; ?>
            <?php if ($memberSince !== ''): ?>
                <p class="profile-hero-meta"><?= e(__('profile.member_since', ['date' => $memberSince])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="profile-sections">
        <?php foreach ($menuGroups as $group): ?>
            <section class="profile-section">
                <?php if (!empty($group['title'])): ?>
                    <h2 class="profile-section-title"><?= e((string) $group['title']) ?></h2>
                <?php endif; ?>
                <div class="profile-section-card">
                    <?php foreach ($group['items'] as $index => $item): ?>
                        <a href="<?= e((string) $item['url']) ?>"
                           class="profile-menu-item<?= $index === count($group['items']) - 1 ? ' profile-menu-item-last' : '' ?>">
                            <span class="profile-menu-icon" aria-hidden="true">
                                <i class="bi <?= e((string) $item['icon']) ?>"></i>
                            </span>
                            <span class="profile-menu-copy">
                                <span class="profile-menu-label"><?= e((string) $item['label']) ?></span>
                                <?php if (!empty($item['meta'])): ?>
                                    <span class="profile-menu-meta"><?= e((string) $item['meta']) ?></span>
                                <?php endif; ?>
                            </span>
                            <?php if (!empty($item['badge'])): ?>
                                <span class="profile-menu-badge"><?= (int) $item['badge'] ?></span>
                            <?php endif; ?>
                            <i class="bi bi-chevron-right profile-menu-chevron" aria-hidden="true"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <form method="post" action="<?= url('/logout') ?>" class="profile-logout">
        <?= csrf_field() ?>
        <button type="submit" class="profile-logout-btn"><?= e(__('profile.logout')) ?></button>
    </form>
</section>
