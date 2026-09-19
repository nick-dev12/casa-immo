<?php
/** @var array<string, mixed> $user */
/** @var string|null $success */
/** @var string|null $error */
?>
<div class="profile-panel">
    <?php if ($success): ?>
        <div class="profile-alert profile-alert-success" role="status"><?= e((string) $success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="profile-alert profile-alert-error" role="alert"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <p class="profile-panel-lead"><?= e(__('profile.security_lead')) ?></p>

    <form method="post" action="<?= url('/profile/security') ?>" class="profile-form">
        <?= csrf_field() ?>
        <label class="profile-field">
            <span><?= e(__('profile.current_password')) ?></span>
            <input type="password" name="current_password" required autocomplete="current-password">
        </label>
        <label class="profile-field">
            <span><?= e(__('profile.new_password')) ?></span>
            <input type="password" name="new_password" required autocomplete="new-password" minlength="8">
        </label>
        <label class="profile-field">
            <span><?= e(__('profile.confirm_password')) ?></span>
            <input type="password" name="new_password_confirmation" required autocomplete="new-password" minlength="8">
        </label>
        <button type="submit" class="profile-submit"><?= e(__('profile.update_password')) ?></button>
    </form>
</div>
