<?php
/** @var array<string, mixed> $agency */
/** @var list<array<string, mixed>> $properties */

$activeNav = 'agencies';
include base_path('views/admin/partials/header.php');

$agencyId = (int) ($agency['id'] ?? 0);
$agencyName = (string) ($agency['name'] ?? '');
$initial = mb_strtoupper(mb_substr($agencyName, 0, 1));
$ownerName = trim((string) ($agency['first_name'] ?? '') . ' ' . (string) ($agency['last_name'] ?? ''));
$status = (string) ($agency['status'] ?? 'draft');
$propertyCount = (int) ($agency['property_count'] ?? 0);
$publishedCount = (int) ($agency['property_published_count'] ?? 0);
$redirectTo = '/admin/agencies/' . $agencyId;

$statusLabels = [
    'active' => __('admin.agency.status.active'),
    'suspended' => __('admin.agency.status.suspended'),
    'draft' => __('admin.agency.status.draft'),
];
?>
<a href="<?= url('/admin/agencies') ?>" class="admin-listing-back">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <?= e(__('admin.agency.back')) ?>
</a>

<?php if (!empty($success)): ?>
    <div class="admin-alert admin-alert-success" role="status"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="admin-alert admin-alert-error" role="alert"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="admin-agency-dossier">
    <section class="admin-agency-dossier-hero">
        <div class="admin-agency-dossier-hero-top">
            <div class="admin-agency-dossier-profile">
                <div class="admin-agency-dossier-avatar" aria-hidden="true">
                    <?php if ($initial !== ''): ?>
                        <span><?= e($initial) ?></span>
                    <?php else: ?>
                        <i class="bi bi-building"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="admin-agency-dossier-kicker"><?= e(__('admin.agency.dossier.kicker')) ?></p>
                    <h1 class="admin-agency-dossier-name"><?= e($agencyName) ?></h1>
                    <p class="admin-agency-dossier-role"><?= e($ownerName) ?> · <?= e((string) ($agency['city'] ?? '')) ?></p>
                </div>
            </div>
            <div class="admin-agency-dossier-actions">
                <?php if ($status === 'suspended' || $status === 'draft'): ?>
                    <form method="post" action="<?= url('/admin/agencies/' . $agencyId . '/activate') ?>" class="admin-agency-dossier-form-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                        <button type="submit" class="admin-agency-dossier-btn">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                            <?= e(__('admin.agency.action.activate')) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url('/admin/agencies/' . $agencyId . '/deactivate') ?>"
                          class="admin-agency-dossier-form-inline"
                          data-confirm="<?= e(__('admin.agency.confirm_deactivate')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                        <button type="submit" class="admin-agency-dossier-btn">
                            <i class="bi bi-pause" aria-hidden="true"></i>
                            <?= e(__('admin.agency.action.deactivate')) ?>
                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?= url('/admin/agencies') ?>" class="admin-agency-dossier-btn">
                    <i class="bi bi-list-ul" aria-hidden="true"></i>
                    <?= e(__('admin.agency.action.list')) ?>
                </a>
            </div>
        </div>

        <div class="admin-agency-dossier-pills">
            <div class="admin-agency-dossier-pill">
                <i class="bi bi-activity" aria-hidden="true"></i>
                <span class="admin-agency-dossier-pill-label"><?= e(__('admin.col_status')) ?></span>
                <strong class="admin-agency-dossier-pill-value admin-agency-dossier-pill-value-<?= e($status) ?>">
                    <?= e($statusLabels[$status] ?? strtoupper($status)) ?>
                </strong>
            </div>
            <?php if (!empty($agency['phone'])): ?>
                <div class="admin-agency-dossier-pill">
                    <i class="bi bi-telephone" aria-hidden="true"></i>
                    <span class="admin-agency-dossier-pill-label"><?= e(__('admin.agency.field.phone')) ?></span>
                    <strong class="admin-agency-dossier-pill-value"><?= e((string) $agency['phone']) ?></strong>
                </div>
            <?php endif; ?>
            <?php if (!empty($agency['email'])): ?>
                <div class="admin-agency-dossier-pill">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <span class="admin-agency-dossier-pill-label"><?= e(__('admin.col_email')) ?></span>
                    <strong class="admin-agency-dossier-pill-value"><?= e((string) $agency['email']) ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="admin-agency-dossier-stats">
        <article class="admin-agency-dossier-stat">
            <p class="admin-agency-dossier-stat-value"><?= $propertyCount ?></p>
            <p class="admin-agency-dossier-stat-label"><?= e(__('admin.agency.stat.properties')) ?></p>
        </article>
        <article class="admin-agency-dossier-stat">
            <p class="admin-agency-dossier-stat-value"><?= $publishedCount ?></p>
            <p class="admin-agency-dossier-stat-label"><?= e(__('admin.agency.stat.published')) ?></p>
        </article>
        <article class="admin-agency-dossier-stat">
            <p class="admin-agency-dossier-stat-value"><?= max(0, $propertyCount - $publishedCount) ?></p>
            <p class="admin-agency-dossier-stat-label"><?= e(__('admin.agency.stat.other_status')) ?></p>
        </article>
    </div>

    <section class="admin-agency-properties">
        <header class="admin-agency-properties-head">
            <div>
                <h2><?= e(__('admin.agency.properties_title')) ?></h2>
                <p><?= e(__('admin.agency.properties_lead')) ?></p>
            </div>
        </header>

        <?php if ($properties === []): ?>
            <div class="admin-empty"><p><?= e(__('admin.agency.empty_properties')) ?></p></div>
        <?php else: ?>
            <div class="admin-properties-grid">
                <?php foreach ($properties as $item): ?>
                    <?php include base_path('views/admin/partials/property-card.php'); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="admin-agency-dossier-foot">
        <form method="post" action="<?= url('/admin/agencies/' . $agencyId . '/delete') ?>"
              data-confirm="<?= e(__('admin.agency.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="admin-btn admin-btn-danger">
                <i class="bi bi-trash" aria-hidden="true"></i>
                <?= e(__('admin.agency.action.delete')) ?>
            </button>
        </form>
    </div>
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
