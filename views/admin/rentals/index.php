<?php
/** @var list<array<string, mixed>> $items */
/** @var array{total: int, active: int, expired: int, inactive: int} $stats */

$activeNav = 'rentals';
include base_path('views/admin/partials/header.php');
?>
<div class="admin-rentals-page">
    <div class="admin-rentals-toolbar">
        <p class="admin-rentals-lead"><?= e(__('admin.rentals_lead')) ?></p>
        <a href="<?= url('/admin/rentals/new') ?>" class="admin-btn admin-btn-rental">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            <?= e(__('admin.rental_new')) ?>
        </a>
    </div>

    <div class="admin-rental-stats">
        <article class="admin-rental-stat-card">
            <span class="admin-rental-stat-icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
            <div>
                <p class="admin-rental-stat-label"><?= e(__('admin.rental.stat.total')) ?></p>
                <p class="admin-rental-stat-value"><?= (int) ($stats['total'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-rental-stat-card">
            <span class="admin-rental-stat-icon admin-rental-stat-icon-active" aria-hidden="true"><i class="bi bi-person-check-fill"></i></span>
            <div>
                <p class="admin-rental-stat-label"><?= e(__('admin.rental.stat.active')) ?></p>
                <p class="admin-rental-stat-value"><?= (int) ($stats['active'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-rental-stat-card">
            <span class="admin-rental-stat-icon admin-rental-stat-icon-expired" aria-hidden="true"><i class="bi bi-calendar-x"></i></span>
            <div>
                <p class="admin-rental-stat-label"><?= e(__('admin.rental.stat.expired')) ?></p>
                <p class="admin-rental-stat-value"><?= (int) ($stats['expired'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-rental-stat-card">
            <span class="admin-rental-stat-icon admin-rental-stat-icon-inactive" aria-hidden="true"><i class="bi bi-person-dash"></i></span>
            <div>
                <p class="admin-rental-stat-label"><?= e(__('admin.rental.stat.inactive')) ?></p>
                <p class="admin-rental-stat-value"><?= (int) ($stats['inactive'] ?? 0) ?></p>
            </div>
        </article>
    </div>

    <div class="admin-rental-search-wrap">
        <label class="admin-rental-search" for="rental-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search"
                   id="rental-search"
                   placeholder="<?= e(__('admin.rental.search_placeholder')) ?>"
                   autocomplete="off"
                   data-rental-search>
        </label>
        <p class="admin-rental-search-meta">
            <strong data-rental-count><?= count($items) ?></strong>
            <?= e(__('admin.rental.search_meta')) ?>
        </p>
    </div>

    <?php if ($items === []): ?>
        <div class="admin-empty admin-rental-empty"><p><?= e(__('admin.empty_rentals')) ?></p></div>
    <?php else: ?>
        <div class="admin-rental-grid" data-rental-grid>
            <?php foreach ($items as $item): ?>
                <?php
                $cardStatus = rental_card_status($item);
                include base_path('views/admin/rentals/partials/card.php');
                ?>
            <?php endforeach; ?>
        </div>
        <div class="admin-rental-empty-search" data-rental-empty-search hidden>
            <p><?= e(__('admin.rental.search_empty')) ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    var input = document.querySelector('[data-rental-search]');
    var cards = document.querySelectorAll('[data-rental-card]');
    var countEl = document.querySelector('[data-rental-count]');
    var emptyEl = document.querySelector('[data-rental-empty-search]');
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
