<?php
/** @var list<array<string, mixed>> $items */
/** @var array{total: int, active: int, suspended: int, draft: int} $stats */

$activeNav = 'agencies';
include base_path('views/admin/partials/header.php');
?>
<div class="admin-agencies-page">
    <div class="admin-agencies-toolbar">
        <p class="admin-agencies-lead"><?= e(__('admin.agencies_lead')) ?></p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="admin-alert admin-alert-success" role="status"><?= e((string) $success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="admin-alert admin-alert-error" role="alert"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <div class="admin-agency-stats">
        <article class="admin-agency-stat-card">
            <span class="admin-agency-stat-icon" aria-hidden="true"><i class="bi bi-building"></i></span>
            <div>
                <p class="admin-agency-stat-label"><?= e(__('admin.agency.stat.total')) ?></p>
                <p class="admin-agency-stat-value"><?= (int) ($stats['total'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-agency-stat-card">
            <span class="admin-agency-stat-icon admin-agency-stat-icon-active" aria-hidden="true"><i class="bi bi-check-circle-fill"></i></span>
            <div>
                <p class="admin-agency-stat-label"><?= e(__('admin.agency.stat.active')) ?></p>
                <p class="admin-agency-stat-value"><?= (int) ($stats['active'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-agency-stat-card">
            <span class="admin-agency-stat-icon admin-agency-stat-icon-suspended" aria-hidden="true"><i class="bi bi-pause-circle"></i></span>
            <div>
                <p class="admin-agency-stat-label"><?= e(__('admin.agency.stat.suspended')) ?></p>
                <p class="admin-agency-stat-value"><?= (int) ($stats['suspended'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-agency-stat-card">
            <span class="admin-agency-stat-icon admin-agency-stat-icon-draft" aria-hidden="true"><i class="bi bi-pencil-square"></i></span>
            <div>
                <p class="admin-agency-stat-label"><?= e(__('admin.agency.stat.draft')) ?></p>
                <p class="admin-agency-stat-value"><?= (int) ($stats['draft'] ?? 0) ?></p>
            </div>
        </article>
    </div>

    <div class="admin-agency-search-wrap">
        <label class="admin-agency-search" for="agency-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search"
                   id="agency-search"
                   placeholder="<?= e(__('admin.agency.search_placeholder')) ?>"
                   autocomplete="off"
                   data-agency-search>
        </label>
        <p class="admin-agency-search-meta">
            <strong data-agency-count><?= count($items) ?></strong>
            <?= e(__('admin.agency.search_meta')) ?>
        </p>
    </div>

    <?php if ($items === []): ?>
        <div class="admin-empty admin-agency-empty"><p><?= e(__('admin.empty_agencies')) ?></p></div>
    <?php else: ?>
        <div class="admin-agency-grid" data-agency-grid>
            <?php foreach ($items as $item): ?>
                <?php
                $redirectTo = '/admin/agencies';
                include base_path('views/admin/agencies/partials/card.php');
                ?>
            <?php endforeach; ?>
        </div>
        <div class="admin-agency-empty-search" data-agency-empty-search hidden>
            <p><?= e(__('admin.agency.search_empty')) ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    var input = document.querySelector('[data-agency-search]');
    var cards = document.querySelectorAll('[data-agency-card]');
    var countEl = document.querySelector('[data-agency-count]');
    var emptyEl = document.querySelector('[data-agency-empty-search]');
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
