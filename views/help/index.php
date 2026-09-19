<?php
$appName = (string) ($appName ?? config('app', 'name'));
?>
<section class="help-page">
    <div class="help-page-bg" aria-hidden="true"></div>
    <div class="container app-container help-page-inner">
        <header class="help-page-hero">
            <a href="<?= url('/') ?>" class="help-page-brand brand-link" aria-label="<?= e($appName) ?>">
                <?php
                $brandVariant = 'logo';
                $brandClass = 'brand-mark-logo';
                include base_path('views/partials/brand-mark.php');
                ?>
            </a>
            <p class="help-page-kicker"><?= e(__('nav.help')) ?></p>
            <h1 class="help-page-title"><?= e(__('profile.contact_support')) ?></h1>
        </header>

        <?php include base_path('views/profile/help.php'); ?>

        <p class="help-page-login-hint">
            <a href="<?= url('/login?redirect=/profile/help') ?>" class="help-page-login-btn"><?= e(__('nav.login')) ?></a>
            <span class="help-page-login-text"><?= e(__('help.login_hint')) ?></span>
        </p>

        <p class="legal-links help-page-legal-footer">
            <a href="<?= url('/terms') ?>"><?= e(__('legal.terms_title')) ?></a>
            <a href="<?= url('/privacy') ?>"><?= e(__('legal.privacy_title')) ?></a>
        </p>
    </div>
</section>
