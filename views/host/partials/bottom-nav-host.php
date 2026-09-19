<?php
/** @var string $activeNav */
/** @var array<string, int|float>|null $navBadges */

$activeNav = $activeNav ?? 'none';
$navBadges = $navBadges ?? [];
$reservationsUnread = unread_reservations_count();
?>
<nav class="host-bottom-nav" aria-label="<?= e(__('host.nav_mobile')) ?>">
    <a href="<?= url('/host') ?>"
       class="host-bottom-nav-item<?= $activeNav === 'dashboard' ? ' is-active' : '' ?>"
       data-reservations-nav>
        <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i>
        <span><?= e(__('host.nav_dashboard_short')) ?></span>
        <span class="host-bottom-nav-badge<?= $reservationsUnread > 0 ? ' is-visible' : '' ?>" data-reservations-badge<?= $reservationsUnread <= 0 ? ' hidden' : '' ?>><?= $reservationsUnread > 0 ? ($reservationsUnread > 9 ? '9+' : $reservationsUnread) : '' ?></span>
    </a>
    <a href="<?= url('/host/properties') ?>"
       class="host-bottom-nav-item<?= $activeNav === 'properties' ? ' is-active' : '' ?>">
        <i class="bi bi-houses-fill" aria-hidden="true"></i>
        <span><?= e(__('host.nav_listings_short')) ?></span>
        <?php if (!empty($navBadges['listings'])): ?>
            <span class="host-bottom-nav-badge"><?= (int) $navBadges['listings'] ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= url('/host/properties/new') ?>"
       class="host-bottom-nav-item host-bottom-nav-item-fab<?= $activeNav === 'new' ? ' is-active' : '' ?>"
       aria-label="<?= e(__('host.nav_add_property')) ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
    </a>
    <a href="<?= url('/messages') ?>"
       class="host-bottom-nav-item<?= $activeNav === 'messages' ? ' is-active' : '' ?>"
       data-messages-nav>
        <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
        <span><?= e(__('host.nav_messages_short')) ?></span>
        <span class="host-bottom-nav-badge<?= !empty($navBadges['messages']) ? ' is-visible' : '' ?>" data-messages-badge<?= empty($navBadges['messages']) ? ' hidden' : '' ?>><?= !empty($navBadges['messages']) ? ((int) $navBadges['messages'] > 9 ? '9+' : (int) $navBadges['messages']) : '' ?></span>
    </a>
    <a href="<?= url('/profile') ?>"
       class="host-bottom-nav-item<?= $activeNav === 'profile' ? ' is-active' : '' ?>">
        <i class="bi bi-person-fill" aria-hidden="true"></i>
        <span><?= e(__('host.nav_profile_short')) ?></span>
    </a>
</nav>
