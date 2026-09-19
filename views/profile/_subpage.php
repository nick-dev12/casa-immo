<?php
/** @var array<string, mixed> $user */
/** @var string $pageTitle */
/** @var string $profileContentView */
/** @var array<string, mixed> $profileContentData */
?>
<section class="profile-page profile-subpage">
    <header class="profile-subhead">
        <a href="<?= url('/profile') ?>" class="profile-back" aria-label="<?= e(__('profile.back')) ?>">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </a>
        <h1><?= e($pageTitle) ?></h1>
    </header>

    <?php
    extract($profileContentData, EXTR_SKIP);
    include base_path('views/profile/' . $profileContentView . '.php');
    ?>
</section>
