<?php
/** @var string|null $authError */
/** @var string $resetStep email|code|password|done */
/** @var string $pendingEmail */
/** @var string $resetToken */
$resetStep = in_array(($resetStep ?? 'email'), ['email', 'code', 'password', 'done'], true)
    ? $resetStep
    : 'email';
$pendingEmail = is_string($pendingEmail ?? null) ? trim($pendingEmail) : '';
$resetToken = is_string($resetToken ?? null) ? trim($resetToken) : '';
$emailValue = $pendingEmail !== '' ? $pendingEmail : old('email');

$authBackUrl = url('/login');
$authBackLabel = __('auth.back_login');
$authHeroShowLogo = true;

if ($resetStep === 'code') {
    $authHeroTitle = __('auth.forgot_title');
    $authHeroLead = __('auth.forgot_lead_code');
} elseif ($resetStep === 'password') {
    $authHeroTitle = __('auth.reset_title');
    $authHeroLead = __('auth.reset_lead');
} elseif ($resetStep === 'done') {
    $authHeroTitle = __('auth.reset_done_title');
    $authHeroLead = __('auth.reset_done_lead');
} else {
    $authHeroTitle = __('auth.forgot_title');
    $authHeroLead = __('auth.forgot_lead');
}
?>
<div class="auth-screen auth-screen-login">
    <div class="auth-screen-backdrop" aria-hidden="true"></div>

    <?php include base_path('views/partials/auth-screen-hero.php'); ?>

    <div class="auth-screen-card">
        <?php if (!empty($authError)): ?>
            <div class="auth-alert auth-alert-error" role="alert"><?= e((string) $authError) ?></div>
        <?php endif; ?>

        <?php if ($resetStep === 'done'): ?>
            <div class="auth-inline-success" role="status">
                <div class="auth-inline-success-icon" aria-hidden="true">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h2 class="auth-inline-success-title"><?= e(__('auth.reset_done_title')) ?></h2>
                <p class="auth-inline-success-text"><?= e(__('auth.reset_done_text')) ?></p>
                <a href="<?= url('/') ?>" class="auth-submit auth-submit-link"><?= e(__('auth.reset_done_cta')) ?></a>
            </div>
        <?php elseif ($resetStep === 'password'): ?>
            <div class="auth-inline-success auth-inline-success-compact" role="status">
                <div class="auth-inline-success-icon" aria-hidden="true">
                    <i class="bi bi-check-lg"></i>
                </div>
                <p class="auth-inline-success-text"><?= e(__('auth.forgot_code_ok')) ?></p>
            </div>

            <form method="post" action="<?= url('/forgot-password/reset') ?>" class="auth-form">
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= e($pendingEmail) ?>">
                <input type="hidden" name="token" value="<?= e($resetToken) ?>">

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
        <?php elseif ($resetStep === 'code'): ?>
            <form method="post" action="<?= url('/forgot-password/verify') ?>" class="auth-form" data-auth-otp-form>
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= e($pendingEmail) ?>">

                <div class="auth-field-stack">
                    <label class="auth-label" for="authForgotEmailReadonly"><?= e(__('auth.email')) ?></label>
                    <label class="auth-field">
                        <input type="email"
                               id="authForgotEmailReadonly"
                               value="<?= e($pendingEmail) ?>"
                               readonly>
                    </label>
                </div>

                <div class="auth-field-stack">
                    <label class="auth-label" for="authOtp0"><?= e(__('auth.forgot_code_label')) ?></label>
                    <div class="auth-otp" data-auth-otp role="group" aria-label="<?= e(__('auth.forgot_code_label')) ?>">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="1"
                                   class="auth-otp-input"
                                   id="authOtp<?= $i ?>"
                                   name="code_digits[]"
                                   autocomplete="<?= $i === 0 ? 'one-time-code' : 'off' ?>"
                                   aria-label="<?= e(__('auth.forgot_code_digit', ['n' => $i + 1])) ?>">
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="code" id="authOtpCode" value="">
                </div>

                <button type="submit"
                        class="auth-submit auth-submit-secondary"
                        id="authOtpActionBtn"
                        data-label-verify="<?= e(__('auth.forgot_verify_submit')) ?>"
                        data-label-resend="<?= e(__('auth.forgot_resend')) ?>"
                        data-verify-action="<?= e(url('/forgot-password/verify')) ?>"
                        data-resend-action="<?= e(url('/forgot-password')) ?>">
                    <?= e(__('auth.forgot_resend')) ?>
                </button>
            </form>
        <?php else: ?>
            <form method="post" action="<?= url('/forgot-password') ?>" class="auth-form" id="authForgotSendForm">
                <?= csrf_field() ?>
                <div class="auth-field-stack">
                    <label class="auth-label" for="authForgotEmail"><?= e(__('auth.email')) ?></label>
                    <label class="auth-field">
                        <input type="email"
                               id="authForgotEmail"
                               name="email"
                               placeholder="<?= e(__('auth.email_placeholder')) ?>"
                               required
                               autocomplete="email"
                               value="<?= e($emailValue) ?>">
                    </label>
                </div>
                <button type="submit" class="auth-submit"><?= e(__('auth.forgot_submit')) ?></button>
            </form>
        <?php endif; ?>

        <?php if ($resetStep !== 'done'): ?>
            <p class="auth-switch-link-wrap">
                <a href="<?= url('/login') ?>" class="auth-switch-link"><?= e(__('auth.back_login')) ?></a>
            </p>
        <?php endif; ?>
    </div>
</div>
<?php if ($resetStep === 'code'): ?>
<script src="<?= asset('js/auth-otp.js') ?>" defer></script>
<?php endif; ?>
