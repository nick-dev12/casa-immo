<?php
/** @var array<string, mixed> $user */
/** @var list<array<string, mixed>> $properties */
/** @var list<array<string, mixed>> $lands */
/** @var bool $isProtected */

$activeNav = 'users';
include base_path('views/admin/partials/header.php');

$userId = (int) ($user['id'] ?? 0);
$fullName = trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? ''));
$initial = mb_strtoupper(mb_substr((string) ($user['first_name'] ?? $fullName), 0, 1));
$status = (string) ($user['status'] ?? 'active');
$redirectTo = '/admin/users/' . $userId;
$establishmentId = (int) ($user['establishment_id'] ?? 0);

$statusLabels = [
    'active' => __('admin.user.status.active'),
    'inactive' => __('admin.user.status.inactive'),
    'banned' => __('admin.user.status.banned'),
];
?>
<a href="<?= url('/admin/users') ?>" class="admin-listing-back">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <?= e(__('admin.user.back')) ?>
</a>

<div class="admin-user-dossier">
    <section class="admin-user-dossier-hero">
        <div class="admin-user-dossier-top">
            <div class="admin-user-dossier-profile">
                <span class="admin-user-dossier-avatar" aria-hidden="true"><?= e($initial !== '' ? $initial : '?') ?></span>
                <div>
                    <p class="admin-user-dossier-kicker"><?= e(__('admin.user.dossier.kicker')) ?></p>
                    <h1 class="admin-user-dossier-name"><?= e($fullName) ?></h1>
                    <p class="admin-user-dossier-role"><?= e((string) ($user['roles'] ?? __('admin.user.role_unknown'))) ?></p>
                </div>
            </div>
            <?php if (!$isProtected): ?>
                <div class="admin-user-dossier-actions">
                    <?php if ($status === 'inactive' || $status === 'banned'): ?>
                        <form method="post" action="<?= url('/admin/users/' . $userId . '/activate') ?>" class="admin-user-dossier-form-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                            <button type="submit" class="admin-user-dossier-btn">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                <?= e(__('admin.user.action.activate')) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="<?= url('/admin/users/' . $userId . '/deactivate') ?>"
                              class="admin-user-dossier-form-inline"
                              data-confirm="<?= e(__('admin.user.confirm_deactivate')) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                            <button type="submit" class="admin-user-dossier-btn">
                                <i class="bi bi-pause" aria-hidden="true"></i>
                                <?= e(__('admin.user.action.deactivate')) ?>
                            </button>
                        </form>
                    <?php endif; ?>
                    <a href="<?= url('/admin/users') ?>" class="admin-user-dossier-btn">
                        <i class="bi bi-list-ul" aria-hidden="true"></i>
                        <?= e(__('admin.user.action.list')) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-user-dossier-pills">
            <div class="admin-user-dossier-pill">
                <span><?= e(__('admin.col_status')) ?></span>
                <strong class="admin-user-dossier-pill-<?= e($status) ?>"><?= e($statusLabels[$status] ?? strtoupper($status)) ?></strong>
            </div>
            <div class="admin-user-dossier-pill">
                <span><?= e(__('admin.col_email')) ?></span>
                <strong><?= e((string) ($user['email'] ?? '')) ?></strong>
            </div>
            <?php if (!empty($user['phone'])): ?>
                <div class="admin-user-dossier-pill">
                    <span><?= e(__('admin.user.field.phone')) ?></span>
                    <strong><?= e((string) $user['phone']) ?></strong>
                </div>
            <?php endif; ?>
            <?php if (!empty($user['establishment_name']) && $establishmentId > 0): ?>
                <div class="admin-user-dossier-pill">
                    <span><?= e(__('admin.col_agency')) ?></span>
                    <strong>
                        <a href="<?= url('/admin/agencies/' . $establishmentId) ?>"><?= e((string) $user['establishment_name']) ?></a>
                    </strong>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="admin-user-dossier-stats">
        <article><strong><?= count($properties) ?></strong><span><?= e(__('admin.user.stat.properties')) ?></span></article>
        <article><strong><?= count($lands) ?></strong><span><?= e(__('admin.user.stat.lands')) ?></span></article>
        <article><strong><?= !empty($user['last_login_at']) ? e(format_date((string) $user['last_login_at'])) : '—' ?></strong><span><?= e(__('admin.user.stat.last_login')) ?></span></article>
    </div>

    <?php if ($properties !== []): ?>
        <section class="admin-user-listings">
            <header>
                <h2><?= e(__('admin.user.properties_title')) ?></h2>
                <p><?= e(__('admin.user.properties_lead')) ?></p>
            </header>
            <div class="admin-properties-grid">
                <?php foreach ($properties as $item): ?>
                    <?php include base_path('views/admin/partials/property-card.php'); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($lands !== []): ?>
        <section class="admin-user-listings">
            <header>
                <h2><?= e(__('admin.user.lands_title')) ?></h2>
                <p><?= e(__('admin.user.lands_lead')) ?></p>
            </header>
            <div class="admin-properties-grid">
                <?php foreach ($lands as $item): ?>
                    <?php include base_path('views/admin/partials/land-card.php'); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($properties === [] && $lands === []): ?>
        <div class="admin-empty"><p><?= e(__('admin.user.empty_listings')) ?></p></div>
    <?php endif; ?>

    <?php if (!$isProtected): ?>
        <div class="admin-user-dossier-foot">
            <form method="post" action="<?= url('/admin/users/' . $userId . '/delete') ?>"
                  data-confirm="<?= e(__('admin.user.confirm_delete')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="admin-btn admin-btn-danger">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                    <?= e(__('admin.user.action.delete')) ?>
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        var msg = form.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) event.preventDefault();
    });
});
</script>
<?php include base_path('views/admin/partials/footer.php'); ?>
