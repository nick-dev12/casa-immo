<?php
/** @var string $activeNav */
/** @var array<string, mixed>|null $establishment */
/** @var bool $isAgency */
/** @var array<string, int|float>|null $navBadges */

$activeNav = $activeNav ?? 'none';
$navBadges = $navBadges ?? [];
$reservationsUnread = unread_reservations_count();
$agencyName = trim((string) ($establishment['name'] ?? config('app', 'name')));
$city = trim((string) ($establishment['city'] ?? ''));
$isSuperAdmin = \App\Helpers\AuthHelper::isAdmin();
?>
<aside class="host-sidebar" aria-label="<?= e(!empty($isAgency) ? __('host.nav_agency') : __('host.nav')) ?>">
    <div class="host-sidebar-brand">
        <span class="host-sidebar-logo" aria-hidden="true">
            <?php
            $brandVariant = 'icon';
            $brandClass = 'brand-mark-icon-lg';
            include base_path('views/partials/brand-mark.php');
            ?>
        </span>
        <span class="host-sidebar-name"><?= e($agencyName) ?></span>
        <?php if ($city !== ''): ?>
            <span class="host-sidebar-city"><?= e($city) ?></span>
        <?php endif; ?>
    </div>

    <nav class="host-sidebar-nav">
        <?php if ($isSuperAdmin): ?>
            <a href="<?= url('/admin') ?>" class="host-sidebar-link host-sidebar-link-admin">
                <span class="host-sidebar-icon host-sidebar-icon-admin" aria-hidden="true">
                    <i class="bi bi-arrow-left-circle-fill"></i>
                </span>
                <span><?= e(__('host.nav_back_superadmin')) ?></span>
            </a>
        <?php endif; ?>
        <a href="<?= url('/host') ?>"
           class="host-sidebar-link<?= $activeNav === 'dashboard' ? ' is-active' : '' ?>"
           data-reservations-nav>
            <span class="host-sidebar-icon host-sidebar-icon-dashboard" aria-hidden="true">
                <i class="bi bi-grid-1x2-fill"></i>
            </span>
            <span><?= e(__('host.nav_dashboard')) ?></span>
            <span class="host-sidebar-badge<?= $reservationsUnread > 0 ? ' is-visible' : '' ?>" data-reservations-badge<?= $reservationsUnread <= 0 ? ' hidden' : '' ?>><?= $reservationsUnread > 0 ? ($reservationsUnread > 9 ? '9+' : $reservationsUnread) : '' ?></span>
        </a>
        <a href="<?= url('/host/properties') ?>"
           class="host-sidebar-link<?= $activeNav === 'properties' ? ' is-active' : '' ?>">
            <span class="host-sidebar-icon host-sidebar-icon-listings" aria-hidden="true">
                <i class="bi bi-houses-fill"></i>
            </span>
            <span><?= e(__('host.nav_listings')) ?></span>
            <?php if (!empty($navBadges['listings'])): ?>
                <span class="host-sidebar-badge"><?= (int) $navBadges['listings'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('/host/properties/new') ?>"
           class="host-sidebar-link<?= $activeNav === 'new' ? ' is-active' : '' ?>">
            <span class="host-sidebar-icon host-sidebar-icon-add" aria-hidden="true">
                <i class="bi bi-plus-circle-fill"></i>
            </span>
            <span><?= e(__('host.nav_add_property')) ?></span>
        </a>
        <a href="<?= url('/messages') ?>"
           class="host-sidebar-link<?= $activeNav === 'messages' ? ' is-active' : '' ?>"
           data-messages-nav>
            <span class="host-sidebar-icon host-sidebar-icon-listings" aria-hidden="true">
                <i class="bi bi-chat-dots-fill"></i>
            </span>
            <span><?= e(__('host.nav_messages')) ?></span>
            <span class="host-sidebar-badge<?= !empty($navBadges['messages']) ? ' is-visible' : '' ?>" data-messages-badge<?= empty($navBadges['messages']) ? ' hidden' : '' ?>><?= !empty($navBadges['messages']) ? (int) $navBadges['messages'] : '' ?></span>
        </a>
        <a href="<?= url('/profile') ?>"
           class="host-sidebar-link<?= $activeNav === 'profile' ? ' is-active' : '' ?>">
            <span class="host-sidebar-icon host-sidebar-icon-profile" aria-hidden="true">
                <i class="bi bi-person-fill"></i>
            </span>
            <span><?= e(__('host.nav_profile')) ?></span>
        </a>
    </nav>

    <div class="host-sidebar-foot">
        <form method="post" action="<?= url('/logout') ?>" class="host-sidebar-logout">
            <?= csrf_field() ?>
            <button type="submit" class="host-sidebar-link host-sidebar-link-logout">
                <span class="host-sidebar-icon host-sidebar-icon-logout" aria-hidden="true">
                    <i class="bi bi-box-arrow-right"></i>
                </span>
                <span><?= e(__('host.nav_logout')) ?></span>
            </button>
        </form>
    </div>
</aside>
