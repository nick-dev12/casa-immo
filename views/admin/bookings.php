<?php
/** @var array<string, int|float> $stats */
/** @var list<array<string, mixed>> $items */

$activeNav = 'bookings';
include base_path('views/admin/partials/header.php');
?>
<p class="admin-bookings-lead"><?= e(__('admin.bookings_lead')) ?></p>

<div class="admin-stats-grid">
    <article class="admin-stat-card admin-stat-card-orange">
        <span class="admin-stat-icon"><i class="bi bi-house-heart-fill"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.bookings_stat.month')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['month_count'] ?? 0) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.bookings_stat.month_meta', ['total' => (int) ($stats['total'] ?? 0)])) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-blue">
        <span class="admin-stat-icon"><i class="bi bi-cash-stack"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.stat_revenue_month')) ?></p>
        <p class="admin-stat-value admin-stat-value-sm"><?= e(money((float) ($stats['month_revenue'] ?? 0), 'XOF')) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.stat_revenue_meta')) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-navy">
        <span class="admin-stat-icon"><i class="bi bi-percent"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.bookings_stat.commission_month')) ?></p>
        <p class="admin-stat-value admin-stat-value-sm"><?= e(money((float) ($stats['month_commission'] ?? 0), 'XOF')) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.bookings_stat.commission_meta', [
            'total' => money((float) ($stats['total_commission'] ?? 0), 'XOF'),
        ])) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-green">
        <span class="admin-stat-icon"><i class="bi bi-wallet2"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.bookings_stat.owner_month')) ?></p>
        <p class="admin-stat-value admin-stat-value-sm"><?= e(money((float) ($stats['month_owner_net'] ?? 0), 'XOF')) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.bookings_stat.owner_meta')) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-orange">
        <span class="admin-stat-icon"><i class="bi bi-hourglass-split"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.bookings_stat.pending')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['pending'] ?? 0) ?></p>
        <p class="admin-stat-meta"><?= e(__('admin.bookings_stat.pending_meta', ['count' => (int) ($stats['confirmed'] ?? 0)])) ?></p>
    </article>
</div>

<section class="admin-panel">
    <?php if ($items === []): ?>
        <div class="admin-empty"><p><?= e(__('admin.empty_bookings')) ?></p></div>
    <?php else: ?>
        <div class="admin-properties-grid">
            <?php foreach ($items as $item): ?>
                <?php include base_path('views/admin/partials/booking-card.php'); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include base_path('views/admin/partials/footer.php'); ?>
