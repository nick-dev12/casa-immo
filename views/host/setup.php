<?php
/** @var array<string, mixed>|null $user */
/** @var string|null $error */

$pageTitle = __('host.setup_title');
$selectedCity = config('app', 'default_city', 'Ziguinchor');
$isSuperAdmin = \App\Helpers\AuthHelper::isAdmin();
?>
<section class="host-page host-setup-page">
    <?php if ($isSuperAdmin): ?>
        <a href="<?= url('/admin') ?>" class="host-setup-back">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <?= e(__('host.nav_back_superadmin')) ?>
        </a>
    <?php endif; ?>
    <header class="host-head host-head-simple">
        <h1><?= e($pageTitle) ?></h1>
        <p class="host-lead"><?= e(__('host.setup_lead_short')) ?></p>
    </header>

    <?php if ($error): ?>
        <div class="host-alert host-alert-error" role="alert"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= url('/host/setup') ?>" class="host-form host-panel host-form-simple">
        <?= csrf_field() ?>
        <label class="host-field">
            <span><?= e(__('host.field.name')) ?></span>
            <input type="text" name="name" required autofocus placeholder="<?= e(__('host.field.name_placeholder')) ?>">
        </label>
        <label class="host-field">
            <span><?= e(__('host.field.city')) ?></span>
            <?php include base_path('views/host/partials/city-select.php'); ?>
        </label>
        <button type="submit" class="host-btn host-btn-primary host-btn-block"><?= e(__('host.create_establishment')) ?></button>
    </form>
</section>
