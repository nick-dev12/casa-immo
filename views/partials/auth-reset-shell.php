<?php
/** @var string $email */
/** @var string $token */
/** @var string|null $authError */
$authBackUrl = url('/login');
$authBackLabel = __('auth.back_login');
$authHeroTitle = __('auth.reset_title');
$authHeroLead = __('auth.reset_lead');
$authHeroShowLogo = true;
?>
<div class="auth-screen auth-screen-login">
    <div class="auth-screen-backdrop" aria-hidden="true"></div>

    <?php include base_path('views/partials/auth-screen-hero.php'); ?>

    <div class="auth-screen-card">
        <?php if (!empty($authError)): ?>
            <div class="auth-alert auth-alert-error" role="alert"><?= e((string) $authError) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= url('/reset-password') ?>" class="auth-form">
            <?= csrf_field() ?>
            <input type="hidden" name="email" value="<?= e($email) ?>">
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="auth-field-stack">
                <label class="auth-label" for="authResetPassword"><?= e(__('auth.reset_password_new')) ?></label>
                <label class="auth-field">
                    <input type="password"
                           id="authResetPassword"
                           name="password"
                           placeholder="••••••••"
                           required
                           minlength="8"
                           autocomplete="new-password"
                           data-auth-password>
                    <button type="button" class="auth-field-toggle" data-auth-toggle-password aria-label="<?= e(__('auth.show_password')) ?>">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </label>
            </div>

            <div class="auth-field-stack">
                <label class="auth-label" for="authResetPasswordConfirm"><?= e(__('auth.reset_password_confirm')) ?></label>
                <label class="auth-field">
                    <input type="password"
                           id="authResetPasswordConfirm"
                           name="password_confirmation"
                           placeholder="••••••••"
                           required
                           minlength="8"
                           autocomplete="new-password"
                           data-auth-password>
                    <button type="button" class="auth-field-toggle" data-auth-toggle-password aria-label="<?= e(__('auth.show_password')) ?>">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </label>
            </div>

            <button type="submit" class="auth-submit"><?= e(__('auth.reset_submit')) ?></button>
        </form>
    </div>
</div>
