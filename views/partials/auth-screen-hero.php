<?php
/**
 * En-tête hero auth (style mockup).
 *
 * @var string $authHeroTitle
 * @var string $authHeroLead
 * @var bool   $authHeroShowLogo
 * @var string $authBackUrl
 * @var string $authBackLabel
 */
$authHeroTitle = $authHeroTitle ?? '';
$authHeroLead = $authHeroLead ?? '';
$authHeroShowLogo = $authHeroShowLogo ?? true;
$authBackUrl = $authBackUrl ?? url('/');
$authBackLabel = $authBackLabel ?? __('auth.back_home');
?>
<a href="<?= e($authBackUrl) ?>" class="auth-screen-back" aria-label="<?= e($authBackLabel) ?>">
    <i class="bi bi-arrow-left"></i>
</a>

<header class="auth-screen-hero">
    <?php if ($authHeroShowLogo): ?>
        <a href="<?= url('/') ?>" class="auth-screen-logo brand-link" aria-label="<?= e(config('app', 'name')) ?>">
            <span class="auth-screen-logo-ring">
                <?php
                $brandVariant = 'icon';
                $brandClass = 'brand-mark-icon';
                include base_path('views/partials/brand-mark.php');
                ?>
            </span>
        </a>
    <?php endif; ?>
    <h1 class="auth-screen-title"><?= e($authHeroTitle) ?></h1>
    <?php if ($authHeroLead !== ''): ?>
        <p class="auth-screen-lead"><?= e($authHeroLead) ?></p>
    <?php endif; ?>
</header>
