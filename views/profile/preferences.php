<?php
/** @var array<string, mixed> $preferences */
/** @var string $section */
/** @var string|null $success */
?>
<div class="profile-panel">
    <?php if ($success): ?>
        <div class="profile-alert profile-alert-success" role="status"><?= e((string) $success) ?></div>
    <?php endif; ?>

    <div class="profile-pref-tabs">
        <a href="<?= url('/profile/preferences?section=device') ?>" class="profile-pref-tab<?= $section === 'device' ? ' is-active' : '' ?>">
            <?= e(__('profile.device_settings')) ?>
        </a>
        <a href="<?= url('/profile/preferences?section=accessibility') ?>" class="profile-pref-tab<?= $section === 'accessibility' ? ' is-active' : '' ?>">
            <?= e(__('profile.accessibility')) ?>
        </a>
        <a href="<?= url('/profile/preferences?section=communication') ?>" class="profile-pref-tab<?= $section === 'communication' ? ' is-active' : '' ?>">
            <?= e(__('profile.communication')) ?>
        </a>
    </div>

    <form method="post" action="<?= url('/profile/preferences') ?>" class="profile-form">
        <?= csrf_field() ?>
        <input type="hidden" name="section" value="<?= e($section) ?>">

        <?php if ($section === 'device'): ?>
            <label class="profile-toggle">
                <span><?= e(__('profile.pref.push')) ?></span>
                <input type="checkbox" name="push_notifications"<?= !empty($preferences['push_notifications']) ? ' checked' : '' ?>>
            </label>
            <label class="profile-toggle">
                <span><?= e(__('profile.pref.email')) ?></span>
                <input type="checkbox" name="email_notifications"<?= !empty($preferences['email_notifications']) ? ' checked' : '' ?>>
            </label>
            <label class="profile-toggle">
                <span><?= e(__('profile.pref.sms')) ?></span>
                <input type="checkbox" name="sms_notifications"<?= !empty($preferences['sms_notifications']) ? ' checked' : '' ?>>
            </label>
        <?php elseif ($section === 'accessibility'): ?>
            <label class="profile-toggle">
                <span><?= e(__('profile.pref.large_text')) ?></span>
                <input type="checkbox" name="large_text"<?= !empty($preferences['large_text']) ? ' checked' : '' ?>>
            </label>
            <label class="profile-toggle">
                <span><?= e(__('profile.pref.high_contrast')) ?></span>
                <input type="checkbox" name="high_contrast"<?= !empty($preferences['high_contrast']) ? ' checked' : '' ?>>
            </label>
        <?php else: ?>
            <label class="profile-toggle">
                <span><?= e(__('profile.pref.marketing')) ?></span>
                <input type="checkbox" name="marketing_emails"<?= !empty($preferences['marketing_emails']) ? ' checked' : '' ?>>
            </label>
            <label class="profile-toggle">
                <span><?= e(__('profile.pref.booking_updates')) ?></span>
                <input type="checkbox" name="booking_updates"<?= !empty($preferences['booking_updates']) ? ' checked' : '' ?>>
            </label>
            <label class="profile-toggle">
                <span><?= e(__('profile.pref.promotions')) ?></span>
                <input type="checkbox" name="promotional_offers"<?= !empty($preferences['promotional_offers']) ? ' checked' : '' ?>>
            </label>
        <?php endif; ?>

        <button type="submit" class="profile-submit"><?= e(__('profile.save')) ?></button>
    </form>
</div>
