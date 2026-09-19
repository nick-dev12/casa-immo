<?php
/** @var list<array<string, mixed>> $items */

$activeNav = 'properties';
include base_path('views/admin/partials/header.php');
?>
<section class="admin-panel">
    <div class="admin-panel-toolbar">
        <p class="admin-panel-lead"><?= e(__('admin.properties_lead')) ?></p>
        <a href="<?= url('/host/properties/new') ?>" class="admin-btn admin-btn-primary"><?= e(__('admin.nav_publish')) ?></a>
    </div>
    <?php if ($items === []): ?>
        <div class="admin-empty"><p><?= e(__('admin.empty_properties')) ?></p></div>
    <?php else: ?>
        <div class="admin-properties-grid">
            <?php foreach ($items as $item): ?>
                <?php include base_path('views/admin/partials/property-card.php'); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include base_path('views/admin/partials/footer.php'); ?>
