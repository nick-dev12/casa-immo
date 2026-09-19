<?php
/** @var list<array<string, mixed>> $items */
/** @var array{total: int, pending: int, reviewed: int, resolved: int, dismissed: int} $stats */

$activeNav = 'reports';
include base_path('views/admin/partials/header.php');

$statusLabels = [
    'pending' => __('admin.report_status.pending'),
    'reviewed' => __('admin.report_status.reviewed'),
    'resolved' => __('admin.report_status.resolved'),
    'dismissed' => __('admin.report_status.dismissed'),
];
?>
<div class="admin-reports-page">
    <p class="admin-reports-lead"><?= e(__('admin.reports_lead')) ?></p>

    <div class="admin-report-stats">
        <article class="admin-report-stat-chip">
            <span><?= (int) ($stats['total'] ?? 0) ?></span>
            <small><?= e(__('admin.report.stat.total')) ?></small>
        </article>
        <article class="admin-report-stat-chip is-pending">
            <span><?= (int) ($stats['pending'] ?? 0) ?></span>
            <small><?= e(__('admin.report.stat.pending')) ?></small>
        </article>
        <article class="admin-report-stat-chip is-reviewed">
            <span><?= (int) ($stats['reviewed'] ?? 0) ?></span>
            <small><?= e(__('admin.report.stat.reviewed')) ?></small>
        </article>
        <article class="admin-report-stat-chip is-resolved">
            <span><?= (int) ($stats['resolved'] ?? 0) ?></span>
            <small><?= e(__('admin.report.stat.resolved')) ?></small>
        </article>
        <article class="admin-report-stat-chip is-dismissed">
            <span><?= (int) ($stats['dismissed'] ?? 0) ?></span>
            <small><?= e(__('admin.report.stat.dismissed')) ?></small>
        </article>
    </div>

    <div class="admin-report-search-wrap">
        <label class="admin-report-search" for="report-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search"
                   id="report-search"
                   placeholder="<?= e(__('admin.report.search_placeholder')) ?>"
                   autocomplete="off"
                   data-report-search>
        </label>
        <p class="admin-report-search-meta">
            <strong data-report-count><?= count($items) ?></strong>
            <?= e(__('admin.report.search_meta')) ?>
        </p>
    </div>

    <?php if ($items === []): ?>
        <div class="admin-empty"><p><?= e(__('admin.empty_reports')) ?></p></div>
    <?php else: ?>
        <div class="admin-report-grid" data-report-grid>
            <?php foreach ($items as $item): ?>
                <?php
                $redirectTo = '/admin/reports';
                include base_path('views/admin/reports/partials/card.php');
                ?>
            <?php endforeach; ?>
        </div>
        <div class="admin-report-empty-search" data-report-empty-search hidden>
            <p><?= e(__('admin.report.search_empty')) ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    var input = document.querySelector('[data-report-search]');
    var cards = document.querySelectorAll('[data-report-card]');
    var countEl = document.querySelector('[data-report-count]');
    var emptyEl = document.querySelector('[data-report-empty-search]');
    if (!input || !cards.length) return;

    function filter() {
        var q = input.value.trim().toLowerCase();
        var visible = 0;
        cards.forEach(function (card) {
            var match = q === '' || (card.getAttribute('data-search') || '').indexOf(q) !== -1;
            card.hidden = !match;
            if (match) visible++;
        });
        if (countEl) countEl.textContent = String(visible);
        if (emptyEl) emptyEl.hidden = visible > 0;
    }

    input.addEventListener('input', filter);

    document.querySelectorAll('[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var msg = form.getAttribute('data-confirm');
            if (msg && !window.confirm(msg)) event.preventDefault();
        });
    });
})();
</script>
<?php include base_path('views/admin/partials/footer.php'); ?>
