<?php
/** @var list<array<string, mixed>> $items */
/** @var array{total: int, active: int, inactive: int, banned: int} $stats */
/** @var int $currentUserId */

$activeNav = 'users';
include base_path('views/admin/partials/header.php');
?>
<div class="admin-users-page">
    <p class="admin-users-lead"><?= e(__('admin.users_lead')) ?></p>

    <div class="admin-user-stats">
        <article class="admin-user-stat-card">
            <span class="admin-user-stat-icon" aria-hidden="true"><i class="bi bi-people-fill"></i></span>
            <div>
                <p class="admin-user-stat-label"><?= e(__('admin.user.stat.total')) ?></p>
                <p class="admin-user-stat-value"><?= (int) ($stats['total'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-user-stat-card">
            <span class="admin-user-stat-icon admin-user-stat-icon-active" aria-hidden="true"><i class="bi bi-person-check-fill"></i></span>
            <div>
                <p class="admin-user-stat-label"><?= e(__('admin.user.stat.active')) ?></p>
                <p class="admin-user-stat-value"><?= (int) ($stats['active'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-user-stat-card">
            <span class="admin-user-stat-icon admin-user-stat-icon-inactive" aria-hidden="true"><i class="bi bi-person-dash"></i></span>
            <div>
                <p class="admin-user-stat-label"><?= e(__('admin.user.stat.inactive')) ?></p>
                <p class="admin-user-stat-value"><?= (int) ($stats['inactive'] ?? 0) ?></p>
            </div>
        </article>
        <article class="admin-user-stat-card">
            <span class="admin-user-stat-icon admin-user-stat-icon-banned" aria-hidden="true"><i class="bi bi-slash-circle"></i></span>
            <div>
                <p class="admin-user-stat-label"><?= e(__('admin.user.stat.banned')) ?></p>
                <p class="admin-user-stat-value"><?= (int) ($stats['banned'] ?? 0) ?></p>
            </div>
        </article>
    </div>

    <div class="admin-user-search-wrap">
        <label class="admin-user-search" for="user-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search"
                   id="user-search"
                   placeholder="<?= e(__('admin.user.search_placeholder')) ?>"
                   autocomplete="off"
                   data-user-search>
        </label>
        <p class="admin-user-search-meta">
            <strong data-user-count><?= count($items) ?></strong>
            <?= e(__('admin.user.search_meta')) ?>
        </p>
    </div>

    <?php if ($items === []): ?>
        <div class="admin-empty"><p><?= e(__('admin.empty_users')) ?></p></div>
    <?php else: ?>
        <div class="admin-user-grid" data-user-grid>
            <?php foreach ($items as $item): ?>
                <?php
                $redirectTo = '/admin/users';
                $isProtected = str_contains((string) ($item['role_slugs'] ?? ''), 'admin')
                    || (int) ($item['id'] ?? 0) === $currentUserId;
                include base_path('views/admin/users/partials/card.php');
                ?>
            <?php endforeach; ?>
        </div>
        <div class="admin-user-empty-search" data-user-empty-search hidden>
            <p><?= e(__('admin.user.search_empty')) ?></p>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    var input = document.querySelector('[data-user-search]');
    var cards = document.querySelectorAll('[data-user-card]');
    var countEl = document.querySelector('[data-user-count]');
    var emptyEl = document.querySelector('[data-user-empty-search]');
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
