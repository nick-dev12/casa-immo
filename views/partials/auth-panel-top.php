<?php
/**
 * Bandeau auth commun : retour + logo.
 *
 * @var string $authBackUrl
 * @var string $authBackLabel
 */
$authBackUrl = $authBackUrl ?? url('/');
$authBackLabel = $authBackLabel ?? __('auth.back_home');
?>
<div class="auth-panel-top">
    <a href="<?= e($authBackUrl) ?>" class="auth-panel-back" aria-label="<?= e($authBackLabel) ?>">
        <i class="bi bi-arrow-left"></i>
    </a>
    <a href="<?= url('/') ?>" class="auth-panel-brand brand-link" aria-label="<?= e(config('app', 'name')) ?>">
        <?php
        $brandVariant = 'logo';
        $brandClass = 'brand-mark-logo';
        include base_path('views/partials/brand-mark.php');
        ?>
    </a>
    <span class="auth-panel-top-spacer" aria-hidden="true"></span>
</div>
