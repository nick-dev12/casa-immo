<?php
/** @var array<string, mixed> $item */
/** @var string $redirectTo */

$agencyId = (int) ($item['id'] ?? 0);
$agencyName = (string) ($item['name'] ?? '');
$initial = mb_strtoupper(mb_substr($agencyName, 0, 1));
$ownerName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? ''));
$status = (string) ($item['status'] ?? 'draft');
$propertyCount = (int) ($item['property_count'] ?? 0);
$publishedCount = (int) ($item['property_published_count'] ?? 0);
$searchBlob = agency_search_blob($item);

$statusLabels = [
    'active' => __('admin.agency.status.active'),
    'suspended' => __('admin.agency.status.suspended'),
    'draft' => __('admin.agency.status.draft'),
];
?>
<article class="admin-agency-card admin-agency-card-<?= e($status) ?>"
         data-agency-card
         data-search="<?= e($searchBlob) ?>">
    <div class="admin-agency-card-head">
        <div class="admin-agency-card-avatar" aria-hidden="true">
            <?php if ($initial !== ''): ?>
                <span><?= e($initial) ?></span>
            <?php else: ?>
                <i class="bi bi-building"></i>
            <?php endif; ?>
        </div>
        <div class="admin-agency-card-intro">
            <h3 class="admin-agency-card-title"><?= e($agencyName) ?></h3>
            <p class="admin-agency-card-subtitle"><?= e($ownerName) ?></p>
            <p class="admin-agency-card-meta">
                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                <?= e((string) ($item['city'] ?? '')) ?>
            </p>
        </div>
    </div>

    <div class="admin-agency-card-body">
        <span class="admin-agency-status admin-agency-status-<?= e($status) ?>">
            <?= e($statusLabels[$status] ?? strtoupper($status)) ?>
        </span>
        <p class="admin-agency-card-stat">
            <i class="bi bi-houses" aria-hidden="true"></i>
            <?= e(__('admin.agency.properties_count', ['count' => $propertyCount, 'published' => $publishedCount])) ?>
        </p>
        <?php if (!empty($item['email'])): ?>
            <p class="admin-agency-card-email">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <?= e((string) $item['email']) ?>
            </p>
        <?php endif; ?>
        <?php if (!empty($item['phone'])): ?>
            <p class="admin-agency-card-phone">
                <i class="bi bi-telephone" aria-hidden="true"></i>
                <?= e((string) $item['phone']) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="admin-agency-card-actions">
        <a href="<?= url('/admin/agencies/' . $agencyId) ?>" class="admin-agency-action-btn">
            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
            <?= e(__('admin.agency.action.enter')) ?>
        </a>
        <?php if ($status === 'suspended' || $status === 'draft'): ?>
            <form method="post" action="<?= url('/admin/agencies/' . $agencyId . '/activate') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                <button type="submit" class="admin-agency-action-btn">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                    <?= e(__('admin.agency.action.activate')) ?>
                </button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= url('/admin/agencies/' . $agencyId . '/deactivate') ?>"
                  data-confirm="<?= e(__('admin.agency.confirm_deactivate')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                <button type="submit" class="admin-agency-action-btn">
                    <i class="bi bi-pause" aria-hidden="true"></i>
                    <?= e(__('admin.agency.action.deactivate')) ?>
                </button>
            </form>
        <?php endif; ?>
        <form method="post" action="<?= url('/admin/agencies/' . $agencyId . '/delete') ?>"
              data-confirm="<?= e(__('admin.agency.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="admin-agency-action-btn admin-agency-action-btn-danger">
                <i class="bi bi-trash" aria-hidden="true"></i>
                <?= e(__('admin.agency.action.delete')) ?>
            </button>
        </form>
    </div>
</article>
