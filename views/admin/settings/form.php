<?php

$activeNav = 'admins';
include base_path('views/admin/partials/header.php');
?>
<a href="<?= url('/admin/settings') ?>" class="admin-listing-back">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    <?= e(__('admin.admins_back')) ?>
</a>

<section class="admin-panel admin-form-panel">
    <h2 class="admin-panel-title"><?= e(__('admin.admins_create_title')) ?></h2>
    <p class="admin-panel-lead"><?= e(__('admin.admins_create_lead')) ?></p>

    <?php if (!empty($error)): ?>
        <div class="admin-alert admin-alert-error" role="alert"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= url('/admin/settings') ?>" class="admin-form admin-admins-form">
        <?= csrf_field() ?>
        <div class="admin-form-grid">
            <label class="admin-form-field">
                <span><?= e(__('admin.admins_field.first_name')) ?></span>
                <input type="text" name="first_name" required autocomplete="given-name">
            </label>
            <label class="admin-form-field">
                <span><?= e(__('admin.admins_field.last_name')) ?></span>
                <input type="text" name="last_name" required autocomplete="family-name">
            </label>
            <label class="admin-form-field">
                <span><?= e(__('admin.admins_field.email')) ?></span>
                <input type="email" name="email" required autocomplete="email">
            </label>
            <label class="admin-form-field">
                <span><?= e(__('admin.admins_field.phone')) ?></span>
                <input type="tel" name="phone" autocomplete="tel">
            </label>
            <label class="admin-form-field">
                <span><?= e(__('admin.admins_field.password')) ?></span>
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label>
            <label class="admin-form-field">
                <span><?= e(__('admin.admins_field.password_confirm')) ?></span>
                <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
            </label>
        </div>
        <div class="admin-form-actions">
            <a href="<?= url('/admin/settings') ?>" class="admin-btn admin-btn-ghost"><?= e(__('admin.construction_cancel')) ?></a>
            <button type="submit" class="admin-btn admin-btn-primary">
                <i class="bi bi-shield-plus" aria-hidden="true"></i>
                <?= e(__('admin.admins_create_btn')) ?>
            </button>
        </div>
    </form>
</section>
<?php include base_path('views/admin/partials/footer.php'); ?>
