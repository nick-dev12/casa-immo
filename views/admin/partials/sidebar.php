<?php
/** @var string $activeNav */
/** @var array<string, int|float>|null $navBadges */
/** @var array<string, mixed>|null $adminEstablishment */

$activeNav = $activeNav ?? 'dashboard';
$navBadges = $navBadges ?? [];
$hasAgency = !empty($adminEstablishment);
$agencyName = trim((string) ($adminEstablishment['name'] ?? __('admin.my_agency')));
$agencyUrl = $hasAgency ? url('/host') : url('/host/setup');
?>
<aside class="admin-sidebar" aria-label="<?= e(__('admin.nav_label')) ?>">
    <div class="admin-sidebar-brand">
        <span class="admin-sidebar-logo" aria-hidden="true">
            <?php
            $brandVariant = 'icon';
            $brandClass = 'brand-mark-icon';
            include base_path('views/partials/brand-mark.php');
            ?>
        </span>
        <span class="admin-sidebar-name"><?= e(__('admin.brand')) ?></span>
        <?php if ($hasAgency): ?>
            <span class="admin-sidebar-tag"><?= e($agencyName) ?></span>
        <?php else: ?>
            <span class="admin-sidebar-tag"><?= e(config('app', 'name')) ?></span>
        <?php endif; ?>
    </div>

    <nav class="admin-sidebar-nav">
        <p class="admin-sidebar-section"><?= e(__('admin.section_platform')) ?></p>
        <a href="<?= url('/admin') ?>" class="admin-sidebar-link<?= $activeNav === 'dashboard' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-dashboard" aria-hidden="true"><i class="bi bi-grid-1x2-fill"></i></span>
            <span><?= e(__('admin.nav_dashboard')) ?></span>
        </a>
        <a href="<?= e($agencyUrl) ?>" class="admin-sidebar-link admin-sidebar-link-agency<?= in_array($activeNav, ['agency_dashboard', 'agency_publish', 'agency_listings'], true) ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-agencies" aria-hidden="true"><i class="bi bi-building"></i></span>
            <span><?= e(__('admin.nav_agency_space')) ?></span>
        </a>
        <a href="<?= url('/admin/agencies') ?>" class="admin-sidebar-link<?= $activeNav === 'agencies' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-agencies" aria-hidden="true"><i class="bi bi-building"></i></span>
            <span><?= e(__('admin.nav_agencies')) ?></span>
        </a>
        <a href="<?= url('/admin/users') ?>" class="admin-sidebar-link<?= $activeNav === 'users' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-users" aria-hidden="true"><i class="bi bi-people-fill"></i></span>
            <span><?= e(__('admin.nav_users')) ?></span>
        </a>
        <a href="<?= url('/admin/reports') ?>" class="admin-sidebar-link<?= $activeNav === 'reports' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-reports" aria-hidden="true"><i class="bi bi-flag-fill"></i></span>
            <span><?= e(__('admin.nav_reports')) ?></span>
            <?php if (!empty($navBadges['reports'])): ?>
                <span class="admin-sidebar-badge"><?= (int) $navBadges['reports'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('/admin/properties') ?>" class="admin-sidebar-link<?= $activeNav === 'properties' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-properties" aria-hidden="true"><i class="bi bi-building"></i></span>
            <span><?= e(__('admin.nav_properties')) ?></span>
            <?php if (!empty($navBadges['pending'])): ?>
                <span class="admin-sidebar-badge"><?= (int) $navBadges['pending'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('/admin/lands') ?>" class="admin-sidebar-link<?= $activeNav === 'lands' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-lands" aria-hidden="true"><i class="bi bi-map-fill"></i></span>
            <span><?= e(__('admin.nav_lands')) ?></span>
        </a>
        <a href="<?= url('/admin/rentals') ?>" class="admin-sidebar-link<?= $activeNav === 'rentals' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-rentals" aria-hidden="true"><i class="bi bi-file-earmark-text-fill"></i></span>
            <span><?= e(__('admin.nav_rentals')) ?></span>
            <?php if (!empty($navBadges['overdue'])): ?>
                <span class="admin-sidebar-badge admin-sidebar-badge-warn"><?= (int) $navBadges['overdue'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('/admin/bookings') ?>" class="admin-sidebar-link<?= $activeNav === 'bookings' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-bookings" aria-hidden="true"><i class="bi bi-house-heart-fill"></i></span>
            <span><?= e(__('admin.nav_bookings')) ?></span>
        </a>
        <a href="<?= url('/admin/construction') ?>" class="admin-sidebar-link<?= $activeNav === 'construction' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-construction" aria-hidden="true"><i class="bi bi-bricks"></i></span>
            <span><?= e(__('admin.nav_construction')) ?></span>
        </a>
        <a href="<?= url('/admin/settings') ?>" class="admin-sidebar-link<?= $activeNav === 'admins' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-admins" aria-hidden="true"><i class="bi bi-shield-lock-fill"></i></span>
            <span><?= e(__('admin.nav_admins')) ?></span>
        </a>

        <p class="admin-sidebar-section"><?= e(__('admin.section_account')) ?></p>
        <a href="<?= url('/profile') ?>" class="admin-sidebar-link<?= $activeNav === 'profile' ? ' is-active' : '' ?>">
            <span class="admin-sidebar-icon admin-sidebar-icon-profile" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
            <span><?= e(__('admin.nav_profile')) ?></span>
        </a>
    </nav>

    <div class="admin-sidebar-foot">
        <form method="post" action="<?= url('/logout') ?>" class="admin-sidebar-logout">
            <?= csrf_field() ?>
            <button type="submit" class="admin-sidebar-link admin-sidebar-link-logout">
                <span class="admin-sidebar-icon admin-sidebar-icon-logout" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></span>
                <span><?= e(__('admin.nav_logout')) ?></span>
            </button>
        </form>
    </div>
</aside>
