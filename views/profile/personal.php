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

    <form method="post" action="<?= url('/profile/personal') ?>" class="profile-form">
        <?= csrf_field() ?>
        <div class="profile-form-grid">
            <label class="profile-field">
                <span><?= e(__('profile.first_name')) ?></span>
                <input type="text" name="first_name" value="<?= e((string) $user['first_name']) ?>" required autocomplete="given-name">
            </label>
            <label class="profile-field">
                <span><?= e(__('profile.last_name')) ?></span>
                <input type="text" name="last_name" value="<?= e((string) $user['last_name']) ?>" required autocomplete="family-name">
            </label>
            <label class="profile-field profile-field-full">
                <span><?= e(__('profile.email')) ?></span>
                <input type="email" name="email" value="<?= e((string) $user['email']) ?>" required autocomplete="email">
            </label>
            <label class="profile-field profile-field-full">
                <span><?= e(__('profile.phone')) ?></span>
                <input type="tel" name="phone" value="<?= e((string) ($user['phone'] ?? '')) ?>" autocomplete="tel">
            </label>
        </div>
        <button type="submit" class="profile-submit"><?= e(__('profile.save')) ?></button>
    </form>
</div>
