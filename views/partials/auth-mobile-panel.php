<?php

use App\Helpers\AuthHelper;

$isLoggedIn = AuthHelper::check();
$user = $isLoggedIn ? AuthHelper::user() : null;
?>
<div class="booking-mobile-drawer-auth">
    <?php if ($isLoggedIn): ?>
        <div class="booking-mobile-user">
            <strong><?= e(AuthHelper::fullName($user)) ?></strong>
            <?php if (!empty($user['email'])): ?>
                <span><?= e((string) $user['email']) ?></span>
            <?php endif; ?>
        </div>
        <a href="<?= url('/profile') ?>" class="booking-mobile-btn booking-mobile-btn-primary"><?= e(__('profile.menu')) ?></a>
        <a href="<?= url('/reservations') ?>" class="booking-mobile-btn booking-mobile-btn-outline"><?= e(__('nav.reservations')) ?></a>
        <form method="post" action="<?= url('/logout') ?>" class="booking-mobile-logout">
            <?= csrf_field() ?>
            <button type="submit" class="booking-mobile-btn booking-mobile-btn-outline booking-mobile-btn-logout">
                <?= e(__('profile.logout')) ?>
            </button>
        </form>
    <?php else: ?>
        <a href="<?= url('/login') ?>" class="booking-mobile-btn booking-mobile-btn-primary"><?= e(__('nav.login')) ?></a>
        <a href="<?= url('/register') ?>" class="booking-mobile-btn booking-mobile-btn-outline"><?= e(__('nav.signup')) ?></a>
    <?php endif; ?>
</div>
