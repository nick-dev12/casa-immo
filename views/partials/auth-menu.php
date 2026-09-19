<?php
/** @var string $variant desktop|compact|icon */

use App\Helpers\AuthHelper;

$variant = $variant ?? 'desktop';
$isLoggedIn = AuthHelper::check();
$user = $isLoggedIn ? AuthHelper::user() : null;
$firstName = trim((string) ($user['first_name'] ?? ''));
$displayName = $firstName !== '' ? $firstName : AuthHelper::fullName($user);
if ($variant === 'icon') {
    $triggerClass = 'auth-menu-trigger-icon auth-menu-trigger';
} elseif ($variant === 'compact') {
    $triggerClass = 'auth-menu-trigger-compact auth-menu-trigger';
} else {
    $triggerClass = 'booking-btn-outline auth-menu-trigger';
}
?>
<div class="auth-menu auth-menu--<?= e($variant) ?><?= $isLoggedIn ? ' auth-menu--logged-in' : '' ?>" data-auth-menu>
    <button type="button"
            class="<?= e($triggerClass) ?>"
            aria-expanded="false"
            aria-haspopup="menu"
            aria-label="<?= e($isLoggedIn ? __('nav.account_menu_logged_in', ['name' => $displayName]) : __('nav.account_menu')) ?>">
        <?php if ($variant === 'icon'): ?>
            <i class="bi bi-person" aria-hidden="true"></i>
        <?php elseif ($isLoggedIn): ?>
            <span class="auth-menu-trigger-label"><?= e($displayName) ?> · <?= e(__('profile.menu')) ?></span>
        <?php else: ?>
            <span><?= e(__('nav.login')) ?></span>
            <i class="bi bi-chevron-down auth-menu-chevron" aria-hidden="true"></i>
        <?php endif; ?>
    </button>
    <div class="auth-menu-dropdown" role="menu" hidden>
        <?php if ($isLoggedIn): ?>
            <div class="auth-menu-user-head">
                <strong><?= e(AuthHelper::fullName($user)) ?></strong>
                <?php if (!empty($user['email'])): ?>
                    <span><?= e((string) $user['email']) ?></span>
                <?php endif; ?>
            </div>
            <a href="<?= url('/profile') ?>" class="auth-menu-option" role="menuitem">
                <i class="bi bi-person" aria-hidden="true"></i>
                <?= e(__('profile.menu')) ?>
            </a>
            <a href="<?= url('/messages') ?>" class="auth-menu-option" role="menuitem">
                <i class="bi bi-chat-dots" aria-hidden="true"></i>
                <?= e(__('nav.messages')) ?>
            </a>
            <a href="<?= url('/reservations') ?>" class="auth-menu-option" role="menuitem">
                <i class="bi bi-calendar-check" aria-hidden="true"></i>
                <?= e(__('nav.reservations')) ?>
            </a>
            <a href="<?= url('/favorites') ?>" class="auth-menu-option" role="menuitem">
                <i class="bi bi-heart" aria-hidden="true"></i>
                <?= e(__('nav.favorites')) ?>
            </a>
            <form method="post" action="<?= url('/logout') ?>" class="auth-menu-logout">
                <?= csrf_field() ?>
                <button type="submit" class="auth-menu-option auth-menu-option-logout" role="menuitem">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    <?= e(__('profile.logout')) ?>
                </button>
            </form>
        <?php else: ?>
            <a href="<?= url('/login') ?>" class="auth-menu-option" role="menuitem">
                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                <?= e(__('nav.login')) ?>
            </a>
            <a href="<?= url('/register') ?>" class="auth-menu-option auth-menu-option-accent" role="menuitem">
                <i class="bi bi-person-plus" aria-hidden="true"></i>
                <?= e(__('nav.signup')) ?>
            </a>
        <?php endif; ?>
    </div>
</div>
