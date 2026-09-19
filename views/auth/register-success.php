<?php
/** @var string $accountType */
/** @var string $continueUrl */
$isAgency = $accountType === 'agency';
?>
<div class="auth-success">
    <div class="auth-success-card">
        <div class="auth-success-icon" aria-hidden="true">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1 class="auth-success-title"><?= e(__('auth.success_title')) ?></h1>

        <p class="auth-success-lead">
            <?= e($isAgency ? __('auth.success_lead_agency') : __('auth.success_lead_client')) ?>
            <?php if (!$isAgency): ?>
                <a href="<?= url('/profile') ?>" class="auth-success-inline-link"><?= e(__('auth.success_profile_link')) ?></a>
            <?php else: ?>
                <a href="<?= url('/host') ?>" class="auth-success-inline-link"><?= e(__('auth.success_agency_link')) ?></a>
            <?php endif; ?>
        </p>

        <p class="auth-success-meta">
            <?= e($isAgency ? __('auth.success_meta_agency') : __('auth.success_meta_client')) ?>
        </p>

        <?php if (!$isAgency): ?>
            <a href="<?= url('/reservations') ?>" class="auth-success-secondary"><?= e(__('auth.success_view_reservations')) ?></a>
        <?php else: ?>
            <a href="<?= url('/host/properties/new') ?>" class="auth-success-secondary"><?= e(__('auth.success_add_listing')) ?></a>
        <?php endif; ?>

        <a href="<?= e($continueUrl) ?>" class="auth-success-cta">
            <?= e($isAgency ? __('auth.success_cta_agency') : __('auth.success_cta_client')) ?>
        </a>
    </div>
</div>
