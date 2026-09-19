<?php
/** @var list<array<string, mixed>> $conversations */
/** @var string|null $error */
/** @var bool $inHostShell */

$inHostShell = !empty($inHostShell);
$totalUnread = array_sum(array_map(static fn (array $item): int => (int) ($item['unread_count'] ?? 0), $conversations));
?>
<header class="<?= $inHostShell ? 'host-messages-head' : 'account-page-head' ?>">
    <h1><?= e(__('messages.title')) ?></h1>
    <?php if ($totalUnread > 0): ?>
        <span class="messages-head-unread"><?= e(__('messages.unread_badge', ['count' => $totalUnread])) ?></span>
    <?php elseif ($conversations !== []): ?>
        <span class="account-badge"><?= count($conversations) ?></span>
    <?php endif; ?>
</header>

<?php if (!empty($error)): ?>
    <div class="account-alert account-alert-error" role="alert"><?= e((string) $error) ?></div>
<?php endif; ?>

<?php if ($conversations === []): ?>
    <div class="account-empty">
        <i class="bi bi-chat-dots" aria-hidden="true"></i>
        <h2><?= e(__('messages.empty_title')) ?></h2>
        <p><?= e(__('messages.empty_lead')) ?></p>
        <a href="<?= url('/properties') ?>" class="btn btn-primary"><?= e(__('messages.explore')) ?></a>
    </div>
<?php else: ?>
    <div class="messages-inbox<?= $totalUnread > 0 ? ' has-unread' : '' ?>" id="messagesInbox" role="list">
        <?php foreach ($conversations as $item): ?>
            <a href="<?= url('/messages/' . (int) $item['id']) ?>"
               class="messages-inbox-item<?= !empty($item['unread_count']) ? ' is-unread' : '' ?>"
               data-conversation-id="<?= (int) $item['id'] ?>"
               data-unread-count="<?= (int) ($item['unread_count'] ?? 0) ?>"
               role="listitem">
                <span class="messages-avatar" aria-hidden="true">
                    <?php if (!empty($item['other_avatar'])): ?>
                        <img src="<?= e((string) $item['other_avatar']) ?>" alt="">
                    <?php else: ?>
                        <?= e((string) $item['other_initials']) ?>
                    <?php endif; ?>
                </span>
                <span class="messages-inbox-body">
                    <span class="messages-inbox-row">
                        <strong class="messages-inbox-name"><?= e((string) $item['other_name']) ?></strong>
                        <?php if ($item['last_at'] !== ''): ?>
                            <time class="messages-inbox-time"><?= e((string) $item['last_at']) ?></time>
                        <?php endif; ?>
                    </span>
                    <?php if ($item['listing_title'] !== ''): ?>
                        <span class="messages-inbox-listing"><?= e((string) $item['listing_title']) ?></span>
                    <?php endif; ?>
                    <span class="messages-inbox-preview">
                        <?= e($item['last_body'] !== '' ? (string) $item['last_body'] : __('messages.no_messages_yet')) ?>
                    </span>
                </span>
                <?php if (!empty($item['unread_count'])): ?>
                    <span class="messages-unread-dot is-pulse"><?= (int) $item['unread_count'] ?></span>
                <?php elseif (!empty($item['listing_image'])): ?>
                    <span class="messages-inbox-thumb" aria-hidden="true">
                        <img src="<?= e((string) $item['listing_image']) ?>" alt="">
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
