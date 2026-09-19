<?php
$appName = (string) config('app', 'name');
$helpEmails = [
    'support@casa-blog-immo.com',
    'mourtallafalloudiou@gmail.com',
];
$helpPhone = '+221 77 870 06 70';
$helpPhoneTel = '+221778700670';
?>
<div class="help-contact">
    <p class="help-contact-lead"><?= e(__('profile.help_lead')) ?></p>

    <div class="help-contact-channels" role="list">
        <?php foreach ($helpEmails as $helpEmail): ?>
        <a class="help-channel" href="mailto:<?= e($helpEmail) ?>" role="listitem">
            <span class="help-channel-icon" aria-hidden="true"><i class="bi bi-envelope-fill"></i></span>
            <span class="help-channel-body">
                <span class="help-channel-label"><?= e(__('profile.help_email')) ?></span>
                <span class="help-channel-value"><?= e($helpEmail) ?></span>
            </span>
            <span class="help-channel-chevron" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
        </a>
        <?php endforeach; ?>

        <a class="help-channel" href="tel:<?= e($helpPhoneTel) ?>" role="listitem">
            <span class="help-channel-icon" aria-hidden="true"><i class="bi bi-telephone-fill"></i></span>
            <span class="help-channel-body">
                <span class="help-channel-label"><?= e(__('profile.help_phone')) ?></span>
                <span class="help-channel-value"><?= e($helpPhone) ?></span>
            </span>
            <span class="help-channel-chevron" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
        </a>

        <div class="help-channel help-channel--static" role="listitem">
            <span class="help-channel-icon" aria-hidden="true"><i class="bi bi-clock-fill"></i></span>
            <span class="help-channel-body">
                <span class="help-channel-label"><?= e(__('profile.help_hours_label')) ?></span>
                <span class="help-channel-value"><?= e(__('profile.help_hours')) ?></span>
            </span>
        </div>
    </div>
</div>

<?php
$emergencyCity = (string) config('app', 'default_city', 'Ziguinchor');
include base_path('views/partials/help-emergency-services.php');
?>

<article class="help-legal-notice legal-content" aria-labelledby="help-scope-title">
    <section>
        <h2 id="help-scope-title" class="help-legal-notice-title"><?= e(__('help.scope_title')) ?></h2>
        <p><?= e(__('help.scope_p1', ['name' => $appName])) ?></p>
        <p><?= e(__('help.scope_p2', ['name' => $appName])) ?></p>
    </section>

    <section>
        <h2 class="help-legal-notice-title"><?= e(__('help.not_title')) ?></h2>
        <ul>
            <li><?= e(__('help.not_li1')) ?></li>
            <li><?= e(__('help.not_li2')) ?></li>
            <li><?= e(__('help.not_li3')) ?></li>
            <li><?= e(__('help.not_li4')) ?></li>
        </ul>
    </section>

    <section>
        <h2 class="help-legal-notice-title"><?= e(__('help.urgency_title')) ?></h2>
        <p><?= e(__('help.urgency_p1')) ?></p>
    </section>

    <p class="help-legal-links legal-links">
        <?= e(__('help.legal_note')) ?>
        <a href="<?= url('/terms') ?>"><?= e(__('legal.terms_short')) ?></a>
        <?= e(__('help.legal_and')) ?>
        <a href="<?= url('/privacy') ?>"><?= e(__('legal.privacy_short')) ?></a>.
    </p>
</article>
