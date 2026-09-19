<?php
/** @var array<string, int> $stats */
/** @var list<array<string, mixed>> $items */

$activeNav = 'construction';
include base_path('views/admin/partials/header.php');

$statusLabels = [
    'devis' => __('admin.construction_status.devis'),
    'planning' => __('admin.construction_status.planning'),
    'chantier' => __('admin.construction_status.chantier'),
    'livraison' => __('admin.construction_status.livraison'),
    'termine' => __('admin.construction_status.termine'),
    'annule' => __('admin.construction_status.annule'),
];

$typeLabels = [
    'maison_fini' => __('admin.construction_type.maison_fini'),
    'maison_finition' => __('admin.construction_type.maison_finition'),
    'villa' => __('admin.construction_type.villa'),
    'immeuble' => __('admin.construction_type.immeuble'),
    'autre' => __('admin.construction_type.autre'),
];
?>
<p class="admin-construction-lead"><?= e(__('admin.construction_lead')) ?></p>

<div class="admin-stats-grid admin-stats-grid-compact">
    <article class="admin-stat-card admin-stat-card-orange">
        <span class="admin-stat-icon"><i class="bi bi-file-earmark-text"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.construction_stat.devis')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['devis'] ?? 0) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-blue">
        <span class="admin-stat-icon"><i class="bi bi-bricks"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.construction_stat.active')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['active'] ?? 0) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-navy">
        <span class="admin-stat-icon"><i class="bi bi-truck"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.construction_stat.delivery')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['delivery'] ?? 0) ?></p>
    </article>
    <article class="admin-stat-card admin-stat-card-green">
        <span class="admin-stat-icon"><i class="bi bi-check-circle"></i></span>
        <p class="admin-stat-label"><?= e(__('admin.construction_stat.completed')) ?></p>
        <p class="admin-stat-value"><?= (int) ($stats['completed'] ?? 0) ?></p>
    </article>
</div>

<section class="admin-panel">
    <div class="admin-panel-toolbar">
        <div>
            <h2 class="admin-panel-title"><?= e(__('admin.construction_list_title')) ?></h2>
            <p class="admin-panel-lead"><?= e(__('admin.construction_list_lead')) ?></p>
        </div>
        <a href="<?= url('/admin/construction/new') ?>" class="admin-btn admin-btn-primary">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            <?= e(__('admin.construction_new')) ?>
        </a>
    </div>

    <?php if ($items === []): ?>
        <div class="admin-empty">
            <span class="admin-placeholder-icon" aria-hidden="true"><i class="bi bi-bricks"></i></span>
            <p><?= e(__('admin.construction_empty')) ?></p>
            <a href="<?= url('/admin/construction/new') ?>" class="admin-btn admin-btn-primary"><?= e(__('admin.construction_new')) ?></a>
        </div>
    <?php else: ?>
        <div class="admin-construction-grid">
            <?php foreach ($items as $item): ?>
                <?php
                $projectId = (int) ($item['id'] ?? 0);
                $status = (string) ($item['status'] ?? 'devis');
                $progress = (int) ($item['progress'] ?? 0);
                $type = (string) ($item['project_type'] ?? 'autre');
                ?>
                <article class="admin-construction-card admin-construction-card-<?= e($status) ?>">
                    <div class="admin-construction-card-head">
                        <div>
                            <h3><?= e((string) ($item['title'] ?? '')) ?></h3>
                            <p class="admin-table-sub">
                                <?= e($typeLabels[$type] ?? ucfirst($type)) ?>
                                · <?= e((string) ($item['city'] ?? '')) ?>
                            </p>
                        </div>
                        <span class="admin-pill admin-pill-<?= e($status) ?>"><?= e($statusLabels[$status] ?? ucfirst($status)) ?></span>
                    </div>

                    <?php if (!empty($item['property_title'])): ?>
                        <p class="admin-construction-link">
                            <i class="bi bi-link-45deg" aria-hidden="true"></i>
                            <?= e(__('admin.construction_linked_property', ['title' => (string) $item['property_title']])) ?>
                        </p>
                    <?php endif; ?>

                    <div class="admin-construction-meta">
                        <?php if (!empty($item['quote_amount'])): ?>
                            <span><i class="bi bi-cash-stack"></i> <?= e(money((float) $item['quote_amount'], (string) ($item['currency'] ?? 'XOF'))) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['delivery_date'])): ?>
                            <span><i class="bi bi-calendar-event"></i> <?= e(__('admin.construction_delivery', ['date' => (string) $item['delivery_date']])) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['client_name'])): ?>
                            <span><i class="bi bi-person"></i> <?= e((string) $item['client_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="admin-construction-progress" aria-label="<?= e(__('admin.construction_progress')) ?>">
                        <div class="admin-construction-progress-bar" style="width: <?= $progress ?>%"></div>
                        <span><?= $progress ?>%</span>
                    </div>

                    <div class="admin-construction-actions">
                        <a href="<?= url('/admin/construction/' . $projectId . '/edit') ?>" class="admin-btn admin-btn-sm admin-btn-ghost"><?= e(__('admin.construction_edit')) ?></a>
                        <form method="post" action="<?= url('/admin/construction/' . $projectId . '/delete') ?>" class="admin-construction-delete-form" data-confirm="<?= e(__('admin.construction_delete_confirm')) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger"><?= e(__('admin.construction_delete')) ?></button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
(function () {
    document.querySelectorAll('.admin-construction-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm') || '')) {
                event.preventDefault();
            }
        });
    });
})();
</script>
<?php include base_path('views/admin/partials/footer.php'); ?>
