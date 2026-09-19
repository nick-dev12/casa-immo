<?php
/** @var array<string, mixed> $report */
/** @var string|null $listingUrl */

$activeNav = 'reports';
include base_path('views/admin/partials/header.php');

$reportId = (int) ($report['id'] ?? 0);
$status = (string) ($report['status'] ?? 'pending');
$reporterName = trim((string) ($report['first_name'] ?? '') . ' ' . (string) ($report['last_name'] ?? ''));
$typeLabel = report_type_label($report);
$targetId = (int) ($report['reportable_id'] ?? 0);
$listingTitle = trim((string) ($report['listing_title'] ?? ''));
$headline = $listingTitle !== '' ? $listingTitle : $typeLabel . ' #' . $targetId;
$reason = report_reason_label((string) ($report['reason'] ?? ''));
$redirectTo = '/admin/reports/' . $reportId;

$statusLabels = [
    'pending' => __('admin.report_status.pending'),
    'reviewed' => __('admin.report_status.reviewed'),
    'resolved' => __('admin.report_status.resolved'),
    'dismissed' => __('admin.report_status.dismissed'),
];
?>
<a href="<?= url('/admin/reports') ?>" class="admin-listing-back">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <?= e(__('admin.report.back')) ?>
</a>

<div class="admin-report-dossier">
    <section class="admin-report-dossier-hero">
        <div class="admin-report-dossier-top">
            <div class="admin-report-dossier-profile">
                <span class="admin-report-dossier-icon" aria-hidden="true"><i class="bi bi-flag-fill"></i></span>
                <div>
                    <p class="admin-report-dossier-kicker"><?= e(__('admin.report.dossier.kicker')) ?></p>
                    <h1 class="admin-report-dossier-name"><?= e($headline) ?></h1>
                    <p class="admin-report-dossier-role">
                        <?= e($typeLabel) ?> #<?= $targetId ?>
                        · <?= e(__('admin.report_by', ['name' => $reporterName !== '' ? $reporterName : (string) ($report['reporter_email'] ?? '')])) ?>
                    </p>
                </div>
            </div>
            <div class="admin-report-dossier-actions">
                <?php if ($listingUrl !== null): ?>
                    <a href="<?= e($listingUrl) ?>" class="admin-report-dossier-btn">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        <?= e(__('admin.report.action.open_listing')) ?>
                    </a>
                <?php endif; ?>
                <a href="<?= url('/admin/reports') ?>" class="admin-report-dossier-btn">
                    <i class="bi bi-list-ul" aria-hidden="true"></i>
                    <?= e(__('admin.report.action.list')) ?>
                </a>
            </div>
        </div>

        <div class="admin-report-dossier-pills">
            <div class="admin-report-dossier-pill">
                <span><?= e(__('admin.col_status')) ?></span>
                <strong class="admin-report-dossier-pill-<?= e($status) ?>"><?= e($statusLabels[$status] ?? strtoupper($status)) ?></strong>
            </div>
            <div class="admin-report-dossier-pill">
                <span><?= e(__('admin.report.field.reason')) ?></span>
                <strong><?= e($reason) ?></strong>
            </div>
            <div class="admin-report-dossier-pill">
                <span><?= e(__('admin.report.field.date')) ?></span>
                <strong><?= e(format_date((string) ($report['created_at'] ?? ''))) ?></strong>
            </div>
        </div>
    </section>

    <section class="admin-report-dossier-content">
        <h2><?= e(__('admin.report.field.description')) ?></h2>
        <?php if (!empty($report['description'])): ?>
            <p><?= nl2br(e((string) $report['description'])) ?></p>
        <?php else: ?>
            <p class="admin-report-dossier-empty"><?= e(__('admin.report.no_description')) ?></p>
        <?php endif; ?>
    </section>

    <?php if ($status === 'pending' || $status === 'reviewed'): ?>
        <div class="admin-report-dossier-foot">
            <?php if ($status === 'pending'): ?>
                <form method="post" action="<?= url('/admin/reports/' . $reportId) ?>" class="admin-report-dossier-form-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="reviewed">
                    <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                    <button type="submit" class="admin-btn admin-btn-ghost">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                        <?= e(__('admin.report_action.review')) ?>
                    </button>
                </form>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/reports/' . $reportId) ?>" class="admin-report-dossier-form-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="resolved">
                <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                    <?= e(__('admin.report_action.resolve')) ?>
                </button>
            </form>
            <form method="post" action="<?= url('/admin/reports/' . $reportId) ?>"
                  class="admin-report-dossier-form-inline"
                  data-confirm="<?= e(__('admin.report.confirm_dismiss')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="dismissed">
                <input type="hidden" name="redirect_to" value="<?= e($redirectTo) ?>">
                <button type="submit" class="admin-btn admin-btn-danger">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                    <?= e(__('admin.report_action.dismiss')) ?>
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
