<?php
/** @var array<string, int|float> $stats */
/** @var list<array<string, mixed>> $modules */
/** @var list<array<string, mixed>> $recentBookings */
/** @var array<string, mixed>|null $user */

$activeNav = 'dashboard';
include base_path('views/admin/partials/header.php');

$adminName = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
$adminEmail = (string) ($user['email'] ?? '');
$revenueMonth = money($stats['bookings_revenue_month'] ?? 0, 'XOF');
?>
<section class="admin-hero" aria-label="<?= e(__('admin.dashboard_title')) ?>">
    <div class="admin-hero-body">
        <p class="admin-hero-kicker"><i class="bi bi-globe2" aria-hidden="true"></i> <?= e(__('admin.platform_kicker')) ?></p>
        <div class="admin-hero-title-row">
            <h1 class="admin-hero-title"><?= e(__('admin.dashboard_title')) ?></h1>
            <span class="admin-hero-badge"><?= e(__('admin.super_admin_badge')) ?></span>
        </div>
        <p class="admin-hero-lead"><?= e(__('admin.dashboard_lead', ['name' => $adminName !== '' ? $adminName : __('admin.default_admin')])) ?></p>
        <?php if ($adminEmail !== ''): ?>
            <p class="admin-hero-email"><i class="bi bi-person-badge" aria-hidden="true"></i> <?= e($adminEmail) ?></p>
        <?php endif; ?>
    </div>
    <div class="admin-hero-visual" aria-hidden="true">
        <span class="admin-hero-gauge"><i class="bi bi-speedometer2"></i></span>
    </div>
</section>

<div class="admin-stats-grid">
    <article class="admin-stat-card admin-stat-card-blue">
        <span class="admin-stat-icon"><i class="bi bi-building"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.stat_agencies')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['establishments_total'] ?? 0) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.stat_agencies_meta', ['count' => (int) ($stats['establishments_active'] ?? 0)])) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-navy">
        <span class="admin-stat-icon"><i class="bi bi-people-fill"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.stat_clients')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['clients_total'] ?? 0) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.stat_clients_meta', ['count' => (int) ($stats['users_active'] ?? 0)])) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-orange">
        <span class="admin-stat-icon"><i class="bi bi-house-heart-fill"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.stat_bookings_month')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['bookings_month'] ?? 0) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.stat_bookings_meta')) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-blue">
        <span class="admin-stat-icon"><i class="bi bi-cash-stack"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.stat_revenue_month')) ?></p>
        <p class="admin-stat-value admin-stat-value-sm"><?= e($revenueMonth) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.stat_revenue_meta')) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-orange">
        <span class="admin-stat-icon"><i class="bi bi-map-fill"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.stat_lands')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['lands_published'] ?? 0) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.stat_lands_meta', [
            'res' => (int) ($stats['lands_residential'] ?? 0),
            'agri' => (int) ($stats['lands_agricultural'] ?? 0),
            'com' => (int) ($stats['lands_commercial'] ?? 0),
        ])) ?></p>
    </article>
</div>

<section class="admin-panel">
    <div class="admin-panel-head">
        <div>
            <h2><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> <?= e(__('admin.quick_access')) ?></h2>
            <p class="admin-panel-lead"><?= e(__('admin.quick_access_lead')) ?></p>
        </div>
    </div>
    <div class="admin-module-grid">
        <?php foreach ($modules as $module): ?>
            <a href="<?= e((string) $module['href']) ?>" class="admin-module-card admin-module-card-<?= e((string) ($module['tone'] ?? 'blue')) ?>">
                <span class="admin-module-icon"><i class="bi bi-<?= e((string) ($module['icon'] ?? 'grid')) ?>"></i></span>
                <div class="admin-module-body">
                    <strong><?= e((string) $module['title']) ?></strong>
                    <p><?= e((string) $module['description']) ?></p>
                    <?php if ($module['metric'] !== null): ?>
                        <span class="admin-module-metric"><?= (int) $module['metric'] ?> <?= e((string) $module['metric_label']) ?></span>
                    <?php else: ?>
                        <span class="admin-module-metric"><?= e((string) $module['metric_label']) ?></span>
                    <?php endif; ?>
                </div>
                <i class="bi bi-chevron-right admin-module-arrow" aria-hidden="true"></i>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php if (($recentBookings ?? []) !== []): ?>
<section class="admin-panel">
    <div class="admin-panel-head admin-panel-head-inline">
        <h2><?= e(__('admin.recent_bookings')) ?></h2>
        <a href="<?= url('/admin/bookings') ?>" class="admin-link"><?= e(__('admin.view_more')) ?></a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?= e(__('admin.col_listing')) ?></th>
                    <th><?= e(__('admin.col_guest')) ?></th>
                    <th><?= e(__('admin.col_dates')) ?></th>
                    <th><?= e(__('admin.col_amount')) ?></th>
                    <th><?= e(__('admin.col_status')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentBookings as $booking): ?>
                    <tr>
                        <td>
                            <strong><?= e(translated((string) ($booking['title'] ?? ''))) ?></strong>
                            <span class="admin-table-sub"><?= e((string) ($booking['city'] ?? '')) ?></span>
                        </td>
                        <td><?= e(trim((string) ($booking['first_name'] ?? '') . ' ' . (string) ($booking['last_name'] ?? ''))) ?></td>
                        <td><?= e(booking_date_range((string) ($booking['check_in'] ?? ''), (string) ($booking['check_out'] ?? ''))) ?></td>
                        <td><?= e(money((float) ($booking['total_amount'] ?? 0), (string) ($booking['currency'] ?? 'XOF'))) ?></td>
                        <td><span class="admin-pill admin-pill-<?= e((string) ($booking['status'] ?? 'pending')) ?>"><?= e(__('reservations.status.' . (string) ($booking['status'] ?? 'pending'))) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<?php include base_path('views/admin/partials/footer.php'); ?>
