<?php
/** @var array<string, mixed> $item */
/** @var string $cardStatus */

$contractId = (int) ($item['id'] ?? 0);
$tenantName = trim((string) ($item['tenant_first_name'] ?? '') . ' ' . (string) ($item['tenant_last_name'] ?? ''));
$initial = mb_strtoupper(mb_substr((string) ($item['tenant_first_name'] ?? $tenantName), 0, 1));
$photoPath = (string) ($item['tenant_photo_path'] ?? '');
$coverImage = (string) ($item['cover_image'] ?? '');
$phone = trim((string) ($item['tenant_phone'] ?? ''));
$contractType = (string) ($item['contract_type'] ?? 'habitation');
$searchBlob = rental_search_blob($item);

$statusLabels = [
    'active' => __('admin.rental.card_status.active'),
    'inactive' => __('admin.rental.card_status.inactive'),
    'expired' => __('admin.rental.card_status.expired'),
];
?>
<article class="admin-rental-card admin-rental-card-<?= e($cardStatus) ?>"
         data-rental-card
         data-search="<?= e($searchBlob) ?>">
    <div class="admin-rental-card-head">
        <div class="admin-rental-card-avatar" aria-hidden="true">
            <?php if ($coverImage !== ''): ?>
                <img src="<?= e(upload_url($coverImage)) ?>" alt="">
            <?php elseif ($photoPath !== ''): ?>
                <img src="<?= e(upload_url($photoPath)) ?>" alt="">
            <?php else: ?>
                <span><?= e($initial !== '' ? $initial : '?') ?></span>
            <?php endif; ?>
        </div>
        <div class="admin-rental-card-intro">
            <h3 class="admin-rental-card-title"><?= e((string) ($item['property_title'] ?? '')) ?></h3>
            <p class="admin-rental-card-subtitle"><?= e($tenantName) ?></p>
            <p class="admin-rental-card-meta"><?= e((string) ($item['property_city'] ?? '')) ?> · <?= e(__('admin.rental.type.' . $contractType)) ?></p>
        </div>
    </div>

    <div class="admin-rental-card-body">
        <span class="admin-rental-status admin-rental-status-<?= e($cardStatus) ?>">
            <?= e($statusLabels[$cardStatus] ?? strtoupper($cardStatus)) ?>
        </span>
        <?php if ($phone !== ''): ?>
            <p class="admin-rental-card-phone">
                <i class="bi bi-telephone" aria-hidden="true"></i>
                <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a>
            </p>
        <?php endif; ?>
        <p class="admin-rental-card-period">
            <i class="bi bi-calendar3" aria-hidden="true"></i>
            <?= e(format_date((string) ($item['start_date'] ?? '')) . ' → ' . format_date((string) ($item['end_date'] ?? ''))) ?>
        </p>
        <p class="admin-rental-card-rent">
            <i class="bi bi-cash-stack" aria-hidden="true"></i>
            <?= e(money((float) ($item['monthly_amount'] ?? 0), 'XOF')) ?>
            <span><?= e(__('common.per_month')) ?></span>
        </p>
    </div>

    <div class="admin-rental-card-actions">
        <a href="<?= url('/admin/rentals/' . $contractId) ?>" class="admin-rental-action-btn">
            <i class="bi bi-eye" aria-hidden="true"></i>
            <?= e(__('admin.rental.action.detail')) ?>
        </a>
        <?php if ($cardStatus === 'inactive' || $cardStatus === 'expired'): ?>
            <form method="post" action="<?= url('/admin/rentals/' . $contractId . '/activate') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="admin-rental-action-btn">
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                    <?= e(__('admin.rental.action.activate')) ?>
                </button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= url('/admin/rentals/' . $contractId . '/deactivate') ?>"
                  data-confirm="<?= e(__('admin.rental.confirm_deactivate')) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="admin-rental-action-btn">
                    <i class="bi bi-pencil" aria-hidden="true"></i>
                    <?= e(__('admin.rental.action.deactivate')) ?>
                </button>
            </form>
        <?php endif; ?>
        <form method="post" action="<?= url('/admin/rentals/' . $contractId . '/delete') ?>"
              data-confirm="<?= e(__('admin.rental.confirm_delete')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="admin-rental-action-btn admin-rental-action-btn-danger">
                <i class="bi bi-trash" aria-hidden="true"></i>
                <?= e(__('admin.rental.action.delete')) ?>
            </button>
        </form>
    </div>
</article>
