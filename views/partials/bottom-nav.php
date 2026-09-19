<?php
/** @var bool $isHome */
/** @var bool $isPropertiesList */
/** @var bool $isDestination */
/** @var bool $isFavorites */
/** @var bool $isReservations */
/** @var bool $isMessages */
/** @var bool $isProfile */

use App\Helpers\AuthHelper;

$messagesUnread = unread_messages_count();
$reservationsUnread = unread_reservations_count();
$messagesHref = url(AuthHelper::check() ? '/messages' : '/login?redirect=/messages');
$isLoggedIn = AuthHelper::check();
$profilePath = $isLoggedIn ? AuthHelper::homePath() : '/profile';
$profileHref = url($isLoggedIn ? $profilePath : '/login');
$profileActive = !empty($isProfile) || !empty($isAuthPage) || (!empty($isHostPage) && $profilePath === '/host');
$profileNavKind = AuthHelper::bottomNavProfileKind();
if ($profileNavKind === 'host') {
    $profileIconClass = 'bi-building' . ($profileActive ? '-fill' : '');
    $profileLabel = __('nav.agency');
} elseif ($profileNavKind === 'admin') {
    $profileIconClass = 'bi-shield-lock' . ($profileActive ? '-fill' : '');
    $profileLabel = __('nav.admin_short');
} else {
    $profileIconClass = 'bi-person' . ($profileActive ? '-fill' : '');
    $profileLabel = __('nav.profile');
}
?>
<nav class="bottom-nav d-lg-none" aria-label="<?= e(__('layout.main_nav')) ?>">
    <a href="<?= url('/') ?>" class="bottom-nav-item<?= !empty($isHome) ? ' active' : '' ?>">
        <i class="bi bi-house-door<?= !empty($isHome) ? '-fill' : '' ?>"></i>
        <span><?= e(__('nav.home')) ?></span>
    </a>
    <a href="<?= url(AuthHelper::check() ? '/reservations' : '/login?redirect=/reservations') ?>"
       class="bottom-nav-item<?= !empty($isReservations) ? ' active' : '' ?>"
       data-reservations-nav>
        <i class="bi bi-calendar-check<?= !empty($isReservations) ? '-fill' : '' ?>"></i>
        <span><?= e(__('nav.reservations')) ?></span>
        <span class="bottom-nav-badge<?= $reservationsUnread > 0 ? ' is-visible' : '' ?>" data-reservations-badge<?= $reservationsUnread <= 0 ? ' hidden' : '' ?>><?= $reservationsUnread > 0 ? ($reservationsUnread > 9 ? '9+' : $reservationsUnread) : '' ?></span>
    </a>
    <a href="<?= url(AuthHelper::check() ? '/favorites' : '/login?redirect=/favorites') ?>" class="bottom-nav-item<?= !empty($isFavorites) ? ' active' : '' ?>">
        <i class="bi bi-heart<?= !empty($isFavorites) ? '-fill' : '' ?>"></i>
        <span><?= e(__('nav.favorites')) ?></span>
    </a>
    <a href="<?= e($messagesHref) ?>" class="bottom-nav-item<?= !empty($isMessages) ? ' active' : '' ?>" data-messages-nav>
        <i class="bi bi-chat-dots<?= !empty($isMessages) ? '-fill' : '' ?>"></i>
        <span><?= e(__('nav.messages')) ?></span>
        <span class="bottom-nav-badge<?= $messagesUnread > 0 ? ' is-visible' : '' ?>" data-messages-badge<?= $messagesUnread <= 0 ? ' hidden' : '' ?>><?= $messagesUnread > 0 ? ($messagesUnread > 9 ? '9+' : $messagesUnread) : '' ?></span>
    </a>
    <a href="<?= e($profileHref) ?>" class="bottom-nav-item<?= $profileActive ? ' active' : '' ?>" data-nav-profile="<?= e($profileNavKind) ?>">
        <i class="bi <?= e($profileIconClass) ?>" data-nav-profile-icon></i>
        <span data-nav-profile-label><?= e($profileLabel) ?></span>
    </a>
</nav>
