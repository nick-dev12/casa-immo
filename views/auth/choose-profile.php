<?php
/** @var string $redirect */
$redirect = $redirect ?? '';
$loginUrl = url('/login' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
$clientFormUrl = url('/register/form?' . http_build_query(array_filter([
    'type' => 'client',
    'redirect' => $redirect !== '' ? $redirect : null,
])));
$agencyFormUrl = url('/register/form?' . http_build_query(array_filter([
    'type' => 'agency',
    'redirect' => $redirect !== '' ? $redirect : null,
])));
$authBackUrl = url('/');
$authBackLabel = __('auth.back_home');
$authHeroTitle = __('auth.choose_hero_title');
$authHeroLead = __('auth.choose_hero_lead');
$authHeroShowLogo = true;
?>
<div class="auth-screen auth-screen-choose" data-auth-choose-root>
    <div class="auth-screen-backdrop" aria-hidden="true"></div>

    <?php include base_path('views/partials/auth-screen-hero.php'); ?>

    <div class="auth-screen-card">
        <div class="auth-choose-grid">
            <a href="<?= e($clientFormUrl) ?>" class="auth-profile-card auth-profile-card-client">
                <span class="auth-profile-card-icon" aria-hidden="true">
                    <i class="bi bi-person-fill"></i>
                </span>
                <span class="auth-profile-card-tag"><?= e(__('auth.profile_client_tag')) ?></span>
                <strong class="auth-profile-card-title"><?= e(__('auth.profile_client_title')) ?></strong>
                <span class="auth-profile-card-desc"><?= e(__('auth.profile_client_desc_short')) ?></span>
                <span class="auth-profile-card-go" aria-hidden="true">
                    <i class="bi bi-arrow-right"></i>
                </span>
            </a>

            <a href="<?= e($agencyFormUrl) ?>" class="auth-profile-card auth-profile-card-agency">
                <span class="auth-profile-card-icon" aria-hidden="true">
                    <i class="bi bi-building-fill"></i>
                </span>
                <span class="auth-profile-card-tag"><?= e(__('auth.profile_agency_tag')) ?></span>
                <strong class="auth-profile-card-title"><?= e(__('auth.profile_agency_title')) ?></strong>
                <span class="auth-profile-card-desc"><?= e(__('auth.profile_agency_desc_short')) ?></span>
                <span class="auth-profile-card-go" aria-hidden="true">
                    <i class="bi bi-arrow-right"></i>
                </span>
            </a>
        </div>

        <p class="auth-switch-link-wrap">
            <?= e(__('auth.have_account')) ?>
            <a href="<?= e($loginUrl) ?>" class="auth-switch-link"><?= e(__('auth.sign_in_tab')) ?></a>
        </p>
    </div>
</div>
