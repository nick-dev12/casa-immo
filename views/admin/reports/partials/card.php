<?php
/** @var array<string, mixed> $item */
/** @var string $redirectTo */

$reportId = (int) ($item['id'] ?? 0);
$status = (string) ($item['status'] ?? 'pending');
$reporterName = trim((string) ($item['first_name'] ?? '') . ' ' . (string) ($item['last_name'] ?? ''));
$typeLabel = report_type_label($item);
$targetId = (int) ($item['reportable_id'] ?? 0);
$listingTitle = trim((string) ($item['listing_title'] ?? ''));
$headline = $listingTitle !== ''
    ? $listingTitle
    : $typeLabel . ' #' . $targetId;
$reason = report_reason_label((string) ($item['reason'] ?? ''));
$description = trim((string) ($item['description'] ?? ''));
$searchBlob = report_search_blob($item);

$statusLabels = [
    'pending' => __('admin.report_status.pending'),
    'reviewed' => __('admin.report_status.reviewed'),
    'resolved' => __('admin.report_status.resolved'),
    'dismissed' => __('admin.report_status.dismissed'),
];
?>
<article class="admin-report-tile admin-report-tile-<?= e($status) ?>"
         data-report-card
         data-search="<?= e($searchBlob) ?>">
    <a href="<?= url('/admin/reports/' . $reportId) ?>" class="admin-report-tile-main">
        <span class="admin-report-tile-icon" aria-hidden="true"><i class="bi bi-flag-fill"></i></span>
        <span class="admin-report-tile-body">
            <strong class="admin-report-tile-title"><?= e($headline) ?></strong>
            <span class="admin-report-tile-sub">
                <?= e(__('admin.report_by', ['name' => $reporterName !== '' ? $reporterName : (string) ($item['reporter_email'] ?? '')])) ?>
            </span>
            <span class="admin-report-tile-meta">
                <em><?= e($reason) ?></em>
                <span class="admin-report-tile-status admin-report-tile-status-<?= e($status) ?>">
                    <?= e($statusLabels[$status] ?? strtoupper($status)) ?>
                </span>
            </span>
            <?php if ($description !== ''): ?>
                <span class="admin-report-tile-desc"><?= e(mb_strlen($description) > 72 ? mb_substr($description, 0, 72) . '…' : $description) ?></span>
            <?php endif; ?>
        </span>
    </a>

    <?php if ($status === 'pending' || $status === 'reviewed'): ?>
        <div class="admin-report-tile-actions">
            <?php if ($status === 'pending'): ?>
                <form method="post" action="<?= url('/admin/reports/' . $reportId) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="reviewed">
                    <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                    <button type="submit" class="admin-report-tile-btn" title="<?= e(__('admin.report_action.review')) ?>">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </form>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/reports/' . $reportId) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="resolved">
                <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                <button type="submit" class="admin-report-tile-btn" title="<?= e(__('admin.report_action.resolve')) ?>">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                </button>
            </form>
            <form method="post" action="<?= url('/admin/reports/' . $reportId) ?>"
                  data-confirm="<?= e(__('admin.report.confirm_dismiss')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="dismissed">
                <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                <button type="submit" class="admin-report-tile-btn admin-report-tile-btn-danger" title="<?= e(__('admin.report_action.dismiss')) ?>">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>
</article>
