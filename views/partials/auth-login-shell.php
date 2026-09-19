<?php
/** @var string $redirect */
$redirect = $redirect ?? '';
$registerUrl = url('/register' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''));
$authBackUrl = url('/');
$authBackLabel = __('auth.back_home');
$authHeroTitle = __('auth.login_hero_title');
$authHeroLead = __('auth.login_hero_lead');
$authHeroShowLogo = true;
?>
<div class="auth-screen auth-screen-login" data-auth-panel-root>
    <div class="auth-screen-backdrop" aria-hidden="true"></div>

    <?php include base_path('views/partials/auth-screen-hero.php'); ?>

    <div class="auth-screen-card">
        <?php if (!empty($authError)): ?>
            <div class="auth-alert auth-alert-error" role="alert"><?= e($authError) ?></div>
        <?php endif; ?>
        <?php if (!empty($authSuccess)): ?>
            <div class="auth-alert auth-alert-success" role="status"><?= e((string) $authSuccess) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= url('/login') ?>" class="auth-form" id="authLoginForm">
            <?= csrf_field() ?>
            <?php if ($redirect !== ''): ?>
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
            <?php endif; ?>

            <div class="auth-field-stack">
                <label class="auth-label" for="authLoginEmail"><?= e(__('auth.email')) ?></label>
                <label class="auth-field">
                    <input type="email"
                           id="authLoginEmail"
                           name="email"
                           placeholder="<?= e(__('auth.email_placeholder')) ?>"
                           required
                           autocomplete="email"
                           value="<?= e(old('email')) ?>">
                </label>
            </div>

            <div class="auth-field-stack">
                <label class="auth-label" for="authLoginPassword"><?= e(__('auth.password')) ?></label>
                <label class="auth-field">
                    <input type="password"
                           id="authLoginPassword"
                           name="password"
                           placeholder="••••••••"
                           required
                           autocomplete="current-password"
                           data-auth-password>
                    <button type="button" class="auth-field-toggle" data-auth-toggle-password aria-label="<?= e(__('auth.show_password')) ?>">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </label>
            </div>

            <div class="auth-form-row">
                <label class="auth-terms auth-terms-inline">
                    <input type="checkbox" name="terms" value="1" required>
                    <span>
                        <?= e(__('auth.agree_short')) ?>
                        <a href="<?= url('/terms') ?>"><?= e(__('auth.terms_link_short')) ?></a>
                    </span>
                </label>
                <a href="<?= url('/forgot-password') ?>" class="auth-forgot"><?= e(__('auth.forget_password')) ?></a>
            </div>

            <button type="submit" class="auth-submit"><?= e(__('auth.sign_in_tab')) ?></button>
        </form>

        <p class="auth-switch-link-wrap">
            <?= e(__('auth.no_account')) ?>
            <a href="<?= e($registerUrl) ?>" class="auth-switch-link"><?= e(__('auth.sign_up_tab')) ?></a>
        </p>
    </div>
</div>
