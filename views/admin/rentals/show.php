<?php
/** @var array<string, mixed> $contract */
/** @var list<array<string, mixed>> $propertyPhotos */
/** @var string $cardStatus */

$activeNav = 'rentals';
include base_path('views/admin/partials/header.php');

$contractId = (int) ($contract['id'] ?? 0);
$tenantName = trim((string) ($contract['tenant_first_name'] ?? '') . ' ' . (string) ($contract['tenant_last_name'] ?? ''));
$tenantInitial = mb_strtoupper(mb_substr((string) ($contract['tenant_first_name'] ?? $tenantName), 0, 1));
$ownerName = trim((string) ($contract['owner_first_name'] ?? '') . ' ' . (string) ($contract['owner_last_name'] ?? ''));
$pdfPath = (string) ($contract['pdf_path'] ?? '');
$photoPath = (string) ($contract['tenant_photo_path'] ?? '');
$propertyImage = (string) ($contract['property_image'] ?? '');
$contractType = (string) ($contract['contract_type'] ?? 'habitation');
$rentalDuration = (string) ($contract['rental_duration'] ?? 'long');
if (!in_array($rentalDuration, ['court', 'moyen', 'long'], true)) {
    $rentalDuration = 'long';
}
$depositMonths = (int) ($contract['deposit_months'] ?? 2);
if (!in_array($depositMonths, [2, 3], true)) {
    $depositMonths = 2;
}
$propertyType = (string) ($contract['property_type'] ?? 'autre');
$tenantEmail = (string) ($contract['tenant_email'] ?? '');
$showEmail = $tenantEmail !== '' && !str_contains($tenantEmail, '@rental.zig-imobilier.local');

$statusLabels = [
    'active' => __('admin.rental.card_status.active'),
    'inactive' => __('admin.rental.card_status.inactive'),
    'expired' => __('admin.rental.card_status.expired'),
];

$endTimestamp = strtotime((string) ($contract['end_date'] ?? ''));
$daysLeft = $endTimestamp !== false ? max(0, (int) floor(($endTimestamp - time()) / 86400)) : 0;
$monthlyRent = (float) ($contract['monthly_amount'] ?? 0);
$deposit = (float) ($contract['deposit_amount'] ?? 0);

$firstPropertyPhoto = '';
if (!empty($propertyPhotos)) {
    $firstPropertyPhoto = (string) ($propertyPhotos[0]['path'] ?? '');
}

$coverImage = $firstPropertyPhoto !== ''
    ? upload_url($firstPropertyPhoto)
    : ($photoPath !== ''
        ? upload_url($photoPath)
        : ($propertyImage !== '' ? property_image($propertyImage, $propertyType) : ''));
?>
<a href="<?= url('/admin/rentals') ?>" class="admin-listing-back no-print">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <?= e(__('admin.rental.back')) ?>
</a>

<div class="admin-rental-dossier" id="rental-dossier-print">
    <section class="admin-rental-dossier-hero">
        <div class="admin-rental-dossier-hero-top">
            <div class="admin-rental-dossier-profile">
                <div class="admin-rental-dossier-avatar" aria-hidden="true">
                    <?php if ($coverImage !== ''): ?>
                        <img src="<?= e($coverImage) ?>" alt="">
                    <?php else: ?>
                        <span><?= e($tenantInitial !== '' ? $tenantInitial : 'L') ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="admin-rental-dossier-kicker"><?= e(__('admin.rental.dossier.kicker')) ?></p>
                    <h1 class="admin-rental-dossier-name"><?= e((string) ($contract['property_title'] ?? '')) ?></h1>
                    <p class="admin-rental-dossier-role"><?= e($tenantName) ?> · <?= e((string) ($contract['property_city'] ?? '')) ?></p>
                </div>
            </div>
            <div class="admin-rental-dossier-actions no-print">
                <?php if ($pdfPath !== ''): ?>
                    <a href="<?= e(upload_url($pdfPath)) ?>" class="admin-rental-dossier-btn" target="_blank" rel="noopener">
                        <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
                        <?= e(__('admin.rental.dossier.add_doc')) ?>
                    </a>
                <?php endif; ?>
                <button type="button" class="admin-rental-dossier-btn" onclick="window.print()">
                    <i class="bi bi-printer" aria-hidden="true"></i>
                    <?= e(__('admin.rental.dossier.print')) ?>
                </button>
                <?php if ($cardStatus === 'inactive' || $cardStatus === 'expired'): ?>
                    <form method="post" action="<?= url('/admin/rentals/' . $contractId . '/activate') ?>" class="admin-rental-dossier-form-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-rental-dossier-btn">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                            <?= e(__('admin.rental.action.activate')) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url('/admin/rentals/' . $contractId . '/deactivate') ?>"
                          class="admin-rental-dossier-form-inline"
                          onsubmit="return confirm('<?= e(__('admin.rental.confirm_deactivate')) ?>')">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-rental-dossier-btn">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                            <?= e(__('admin.rental.dossier.edit')) ?>
                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?= url('/admin/rentals') ?>" class="admin-rental-dossier-btn">
                    <i class="bi bi-list-ul" aria-hidden="true"></i>
                    <?= e(__('admin.rental.dossier.list')) ?>
                </a>
            </div>
        </div>

        <div class="admin-rental-dossier-pills">
            <div class="admin-rental-dossier-pill">
                <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                <span class="admin-rental-dossier-pill-label"><?= e(__('admin.rental.field.contract_type')) ?></span>
                <strong class="admin-rental-dossier-pill-value"><?= e(__('admin.rental.type.' . $contractType)) ?></strong>
            </div>
            <div class="admin-rental-dossier-pill">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                <span class="admin-rental-dossier-pill-label"><?= e(__('admin.rental.field.rental_duration')) ?></span>
                <strong class="admin-rental-dossier-pill-value"><?= e(__('admin.rental.duration.' . $rentalDuration)) ?></strong>
            </div>
            <div class="admin-rental-dossier-pill">
                <i class="bi bi-heart" aria-hidden="true"></i>
                <span class="admin-rental-dossier-pill-label"><?= e(__('admin.rental.dossier.status')) ?></span>
                <strong class="admin-rental-dossier-pill-value admin-rental-dossier-pill-value-<?= e($cardStatus) ?>">
                    <?= e($statusLabels[$cardStatus] ?? strtoupper($cardStatus)) ?>
                </strong>
            </div>
        </div>
    </section>

    <div class="admin-rental-dossier-stats">
        <article class="admin-rental-dossier-stat">
            <p class="admin-rental-dossier-stat-value"><?= e(money($monthlyRent, 'XOF')) ?></p>
            <p class="admin-rental-dossier-stat-label"><?= e(__('admin.rental.dossier.stat_rent')) ?></p>
        </article>
        <article class="admin-rental-dossier-stat">
            <p class="admin-rental-dossier-stat-value"><?= e(money($deposit, 'XOF')) ?></p>
            <p class="admin-rental-dossier-stat-label"><?= e(__('admin.rental.dossier.stat_deposit')) ?></p>
        </article>
        <article class="admin-rental-dossier-stat<?= $cardStatus === 'expired' ? ' is-alert' : '' ?>">
            <p class="admin-rental-dossier-stat-value"><?= $daysLeft ?></p>
            <p class="admin-rental-dossier-stat-label"><?= e(__('admin.rental.dossier.stat_days')) ?></p>
        </article>
    </div>

    <nav class="admin-rental-dossier-tabs no-print" aria-label="<?= e(__('admin.rental.dossier.tabs')) ?>">
        <button type="button" class="admin-rental-dossier-tab is-active" data-rental-tab="info">
            <span class="admin-rental-dossier-tab-icon admin-rental-dossier-tab-icon-blue"><i class="bi bi-person-vcard"></i></span>
            <span>
                <strong><?= e(__('admin.rental.dossier.tab_info')) ?></strong>
                <small><?= e(__('admin.rental.dossier.tab_info_lead')) ?></small>
            </span>
        </button>
        <button type="button" class="admin-rental-dossier-tab" data-rental-tab="documents">
            <span class="admin-rental-dossier-tab-icon admin-rental-dossier-tab-icon-green"><i class="bi bi-folder2-open"></i></span>
            <span>
                <strong><?= e(__('admin.rental.dossier.tab_docs')) ?></strong>
                <small><?= e(__('admin.rental.dossier.tab_docs_lead')) ?></small>
            </span>
        </button>
        <button type="button" class="admin-rental-dossier-tab" data-rental-tab="lease">
            <span class="admin-rental-dossier-tab-icon admin-rental-dossier-tab-icon-orange"><i class="bi bi-calendar3"></i></span>
            <span>
                <strong><?= e(__('admin.rental.dossier.tab_lease')) ?></strong>
                <small><?= e(__('admin.rental.dossier.tab_lease_lead')) ?></small>
            </span>
        </button>
    </nav>

    <div class="admin-rental-dossier-panels">
        <section class="admin-rental-dossier-panel is-active" data-rental-panel="info">
            <div class="admin-rental-dossier-panel-grid">
                <div class="admin-rental-dossier-info-card">
                    <h2><?= e(__('admin.rental.detail.tenant')) ?></h2>
                    <dl class="admin-rental-dossier-dl">
                        <dt><?= e(__('admin.col_user')) ?></dt>
                        <dd><?= e($tenantName) ?></dd>
                        <?php if (!empty($contract['tenant_phone'])): ?>
                            <dt><?= e(__('admin.rental.field.phone')) ?></dt>
                            <dd><a href="tel:<?= e(preg_replace('/\s+/', '', (string) $contract['tenant_phone'])) ?>"><?= e((string) $contract['tenant_phone']) ?></a></dd>
                        <?php endif; ?>
                        <?php if ($showEmail): ?>
                            <dt><?= e(__('admin.rental.field.email')) ?></dt>
                            <dd><a href="mailto:<?= e($tenantEmail) ?>"><?= e($tenantEmail) ?></a></dd>
                        <?php endif; ?>
                    </dl>
                </div>
                <div class="admin-rental-dossier-info-card">
                    <h2><?= e(__('admin.rental.detail.owner')) ?></h2>
                    <dl class="admin-rental-dossier-dl">
                        <dt><?= e(__('admin.col_user')) ?></dt>
                        <dd><?= e($ownerName) ?></dd>
                        <dt><?= e(__('admin.col_listing')) ?></dt>
                        <dd><?= e((string) ($contract['property_title'] ?? '')) ?></dd>
                        <dt><?= e(__('admin.col_city')) ?></dt>
                        <dd><?= e((string) ($contract['property_city'] ?? '')) ?></dd>
                    </dl>
                </div>
            </div>
        </section>

        <section class="admin-rental-dossier-panel" data-rental-panel="documents" hidden>
            <div class="admin-rental-dossier-docs">
                <?php if ($pdfPath !== ''): ?>
                    <a href="<?= e(upload_url($pdfPath)) ?>" class="admin-rental-dossier-doc" target="_blank" rel="noopener">
                        <span class="admin-rental-dossier-doc-icon admin-rental-dossier-doc-icon-pdf"><i class="bi bi-file-earmark-pdf"></i></span>
                        <span>
                            <strong><?= e(__('admin.rental.upload.contract')) ?></strong>
                            <small><?= e(__('admin.rental.dossier.doc_contract')) ?></small>
                        </span>
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <p class="admin-rental-dossier-empty"><?= e(__('admin.rental.dossier.no_contract')) ?></p>
                <?php endif; ?>

                <?php if (!empty($propertyPhotos)): ?>
                    <div class="admin-rental-dossier-gallery">
                        <h3><?= e(__('admin.rental.upload.property_photos')) ?></h3>
                        <div class="admin-rental-dossier-gallery-grid">
                            <?php foreach ($propertyPhotos as $image): ?>
                                <?php $imagePath = (string) ($image['path'] ?? ''); ?>
                                <?php if ($imagePath === '') continue; ?>
                                <a href="<?= e(upload_url($imagePath)) ?>" class="admin-rental-dossier-gallery-item" target="_blank" rel="noopener">
                                    <img src="<?= e(upload_url($imagePath)) ?>" alt="">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif ($photoPath !== ''): ?>
                    <a href="<?= e(upload_url($photoPath)) ?>" class="admin-rental-dossier-doc" target="_blank" rel="noopener">
                        <span class="admin-rental-dossier-doc-icon admin-rental-dossier-doc-icon-photo"><i class="bi bi-camera"></i></span>
                        <span>
                            <strong><?= e(__('admin.rental.upload.photo')) ?></strong>
                            <small><?= e(__('admin.rental.dossier.doc_photo')) ?></small>
                        </span>
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <p class="admin-rental-dossier-empty"><?= e(__('admin.rental.dossier.no_photos')) ?></p>
                <?php endif; ?>
            </div>
        </section>

        <section class="admin-rental-dossier-panel" data-rental-panel="lease" hidden>
            <div class="admin-rental-dossier-info-card">
                <h2><?= e(__('admin.rental.detail.period')) ?></h2>
                <dl class="admin-rental-dossier-dl">
                    <dt><?= e(__('admin.rental.field.start_date')) ?></dt>
                    <dd><?= e(format_date((string) ($contract['start_date'] ?? ''))) ?></dd>
                    <dt><?= e(__('admin.rental.field.end_date')) ?></dt>
                    <dd><?= e(format_date((string) ($contract['end_date'] ?? ''))) ?></dd>
                    <dt><?= e(__('admin.rental.field.rental_duration')) ?></dt>
                    <dd><?= e(__('admin.rental.duration.' . $rentalDuration)) ?></dd>
                    <dt><?= e(__('admin.rental.field.monthly_rent')) ?></dt>
                    <dd><?= e(money($monthlyRent, 'XOF')) ?></dd>
                    <dt><?= e(__('admin.rental.field.deposit_months')) ?></dt>
                    <dd><?= e(__('admin.rental.deposit_months.' . $depositMonths)) ?></dd>
                    <dt><?= e(__('admin.rental.field.deposit')) ?></dt>
                    <dd><?= e(money($deposit, 'XOF')) ?></dd>
                </dl>
                <?php if (!empty($contract['terms'])): ?>
                    <div class="admin-rental-dossier-notes">
                        <h3><?= e(__('admin.rental.field.notes')) ?></h3>
                        <p><?= nl2br(e((string) $contract['terms'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="admin-rental-dossier-foot no-print">
        <form method="post" action="<?= url('/admin/rentals/' . $contractId . '/delete') ?>"
              onsubmit="return confirm('<?= e(__('admin.rental.confirm_delete')) ?>')">
            <?= csrf_field() ?>
            <button type="submit" class="admin-btn admin-btn-danger">
                <i class="bi bi-trash" aria-hidden="true"></i>
                <?= e(__('admin.rental.action.delete')) ?>
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    var tabs = document.querySelectorAll('[data-rental-tab]');
    var panels = document.querySelectorAll('[data-rental-panel]');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var id = tab.getAttribute('data-rental-tab');
            tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            panels.forEach(function (panel) {
                var show = panel.getAttribute('data-rental-panel') === id;
                panel.classList.toggle('is-active', show);
                panel.hidden = !show;
            });
        });
    });
})();
</script>
<?php include base_path('views/admin/partials/footer.php'); ?>
