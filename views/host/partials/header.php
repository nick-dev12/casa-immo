<?php
/** @var array<string, mixed>|null $establishment */
/** @var string $pageTitle */
/** @var string $activeNav dashboard|properties|new|profile|none */
/** @var bool $showBack */
/** @var array<string, int|float>|null $navBadges */

$activeNav = $activeNav ?? 'none';
$showBack = !empty($showBack);
$navBadges = $navBadges ?? [];
$hideShellNav = $activeNav === 'none';
$isSuperAdmin = \App\Helpers\AuthHelper::isAdmin();
?>
<div class="host-admin">
    <?php if (!$hideShellNav): ?>
        <?php include base_path('views/host/partials/sidebar.php'); ?>
    <?php endif; ?>

    <div class="host-admin-main">
        <?php if (!$hideShellNav): ?>
            <header class="host-admin-topbar d-lg-none">
                <?php if ($isSuperAdmin): ?>
                    <a href="<?= url('/admin') ?>" class="host-admin-topbar-back" aria-label="<?= e(__('host.nav_back_superadmin')) ?>">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        <span><?= e(__('host.nav_back_superadmin_short')) ?></span>
                    </a>
                <?php endif; ?>
                <div class="host-admin-topbar-brand">
                    <span class="host-admin-topbar-logo" aria-hidden="true">
                        <?php
                        $brandVariant = 'icon';
                        $brandClass = 'brand-mark-icon-sm';
                        include base_path('views/partials/brand-mark.php');
                        ?>
                    </span>
                    <span><?= e(trim((string) ($establishment['name'] ?? config('app', 'name')))) ?></span>
                </div>
            </header>
        <?php endif; ?>

        <div class="host-admin-content host-page"<?= ($activeNav ?? '') === 'dashboard' ? ' data-reservations-enable-banner' : '' ?>>
            <?php if ($showBack): ?>
                <a href="<?= url('/host/properties') ?>" class="host-back" aria-label="<?= e(__('host.back')) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="host-alert host-alert-success" role="status"><?= e((string) $success) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="host-alert host-alert-error" role="alert"><?= e((string) $error) ?></div>
            <?php endif; ?>

            <?php if (!empty($pageTitle) && ($activeNav ?? '') !== 'dashboard' && ($activeNav ?? '') !== 'properties' && empty($showBack)): ?>
                <header class="host-page-head">
                    <h1 class="host-page-title"><?= e($pageTitle) ?></h1>
                </header>
            <?php endif; ?>
