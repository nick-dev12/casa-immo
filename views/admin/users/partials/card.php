<?php
/** @var array<string, mixed> $item */
/** @var string $redirectTo */
/** @var bool $isProtected */

$userId = (int) ($item['id'] ?? 0);
$fullName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? ''));
$initial = mb_strtoupper(mb_substr((string) ($item['first_name'] ?? $fullName), 0, 1));
$status = (string) ($item['status'] ?? 'active');
$roleLabel = user_primary_role_label($item);
$phone = trim((string) ($item['phone'] ?? ''));
$propertyCount = (int) ($item['property_count'] ?? 0);
$landCount = (int) ($item['land_count'] ?? 0);
$establishmentName = trim((string) ($item['establishment_name'] ?? ''));
$searchBlob = user_search_blob($item);

$statusLabels = [
    'active' => __('admin.user.status.active'),
    'inactive' => __('admin.user.status.inactive'),
    'banned' => __('admin.user.status.banned'),
];
?>
<article class="admin-user-card admin-user-card-<?= e($status) ?>"
         data-user-card
         data-search="<?= e($searchBlob) ?>">
    <div class="admin-user-card-head">
        <div class="admin-user-card-avatar" aria-hidden="true">
            <span><?= e($initial !== '' ? $initial : '?') ?></span>
        </div>
        <div class="admin-user-card-intro">
            <h3 class="admin-user-card-title"><?= e($fullName) ?></h3>
            <p class="admin-user-card-email"><?= e((string) ($item['email'] ?? '')) ?></p>
            <p class="admin-user-card-role"><?= e($roleLabel) ?></p>
        </div>
    </div>

    <div class="admin-user-card-body">
        <span class="admin-user-status admin-user-status-<?= e($status) ?>">
            <?= e($statusLabels[$status] ?? strtoupper($status)) ?>
        </span>
        <?php if ($phone !== ''): ?>
            <p class="admin-user-card-phone">
                <i class="bi bi-telephone" aria-hidden="true"></i>
                <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a>
            </p>
        <?php endif; ?>
        <?php if ($establishmentName !== ''): ?>
            <p class="admin-user-card-agency">
                <i class="bi bi-building" aria-hidden="true"></i>
                <?= e($establishmentName) ?>
            </p>
        <?php endif; ?>
        <?php if ($propertyCount > 0 || $landCount > 0): ?>
            <p class="admin-user-card-counts">
                <i class="bi bi-houses" aria-hidden="true"></i>
                <?php if ($propertyCount > 0): ?>
                    <?= (int) $propertyCount ?> <?= e(__('admin.user.listings_short')) ?>
                <?php endif; ?>
                <?php if ($landCount > 0): ?>
                    <?= $propertyCount > 0 ? ' · ' : '' ?><?= (int) $landCount ?> <?= e(__('admin.user.lands_short')) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="admin-user-card-actions">
        <a href="<?= url('/admin/users/' . $userId) ?>" class="admin-user-action-btn">
            <i class="bi bi-eye" aria-hidden="true"></i>
            <?= e(__('admin.user.action.detail')) ?>
        </a>
        <?php if (!$isProtected): ?>
            <?php if ($status === 'inactive' || $status === 'banned'): ?>
                <form method="post" action="<?= url('/admin/users/' . $userId . '/activate') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                    <button type="submit" class="admin-user-action-btn">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        <?= e(__('admin.user.action.activate')) ?>
                    </button>
                </form>
            <?php else: ?>
                <form method="post" action="<?= url('/admin/users/' . $userId . '/deactivate') ?>"
                      data-confirm="<?= e(__('admin.user.confirm_deactivate')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                    <button type="submit" class="admin-user-action-btn">
                        <i class="bi bi-pause" aria-hidden="true"></i>
                        <?= e(__('admin.user.action.deactivate')) ?>
                    </button>
                </form>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/users/' . $userId . '/delete') ?>"
                  data-confirm="<?= e(__('admin.user.confirm_delete')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="admin-user-action-btn admin-user-action-btn-danger">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                    <?= e(__('admin.user.action.delete')) ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</article>
