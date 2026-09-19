<?php
/** @var array<string, mixed> $conversation */
/** @var array<string, mixed>|null $other */
/** @var array<string, mixed>|null $listing */
/** @var list<array<string, mixed>> $messages */

$otherName = $other !== null ? \App\Helpers\AuthHelper::fullName($other) : __('messages.unknown');
$otherInitials = user_initials($other);
$otherAvatar = user_avatar_url($other);
$conversationId = (int) ($conversation['id'] ?? 0);
?>
<section class="messages-thread"
         id="messagesThread"
         data-messages-enable-banner
         data-conversation-id="<?= $conversationId ?>"
         data-poll-url="<?= e(url('/messages/' . $conversationId . '/poll')) ?>"
         data-send-url="<?= e(url('/messages/' . $conversationId)) ?>"
         data-empty-error="<?= e(__('messages.error.empty')) ?>"
         data-send-error="<?= e(__('messages.error.send')) ?>">
    <header class="messages-thread-head">
        <a href="<?= url('/messages') ?>" class="messages-back" aria-label="<?= e(__('messages.back')) ?>">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </a>
        <span class="messages-avatar messages-avatar-sm" aria-hidden="true">
            <?php if ($otherAvatar): ?>
                <img src="<?= e($otherAvatar) ?>" alt="">
            <?php else: ?>
                <?= e($otherInitials) ?>
            <?php endif; ?>
        </span>
        <div class="messages-thread-who">
            <strong><?= e($otherName) ?></strong>
            <span><?= e(__('messages.role_hint')) ?></span>
        </div>
    </header>

    <?php if ($listing !== null): ?>
        <?php if ($listing['url'] !== ''): ?>
            <a href="<?= e((string) $listing['url']) ?>" class="messages-listing-card">
                <?php if ($listing['image'] !== ''): ?>
                    <img src="<?= e((string) $listing['image']) ?>" alt="">
                <?php endif; ?>
                <span>
                    <strong><?= e((string) $listing['title']) ?></strong>
                    <?php if ($listing['city'] !== ''): ?>
                        <em><?= e((string) $listing['city']) ?></em>
                    <?php endif; ?>
                </span>
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </a>
        <?php else: ?>
            <div class="messages-listing-card">
                <span>
                    <strong><?= e((string) $listing['title']) ?></strong>
                </span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="messages-feed" id="messagesFeed" aria-live="polite">
        <?php if ($messages === []): ?>
            <p class="messages-feed-empty" id="messagesEmpty"><?= e(__('messages.thread_empty')) ?></p>
        <?php endif; ?>
        <?php foreach ($messages as $message): ?>
            <article class="messages-bubble<?= !empty($message['mine']) ? ' is-mine' : '' ?>"
                     data-message-id="<?= (int) $message['id'] ?>">
                <p><?= nl2br(e((string) $message['body'])) ?></p>
                <time><?= e((string) $message['time_label']) ?></time>
            </article>
        <?php endforeach; ?>
    </div>

    <form class="messages-composer" id="messagesComposer" method="post" action="<?= e(url('/messages/' . $conversationId)) ?>">
        <?= csrf_field() ?>
        <label class="visually-hidden" for="messagesBody"><?= e(__('messages.placeholder')) ?></label>
        <textarea id="messagesBody"
                  name="body"
                  rows="1"
                  maxlength="2000"
                  required
                  placeholder="<?= e(__('messages.placeholder')) ?>"></textarea>
        <button type="submit" class="messages-send" aria-label="<?= e(__('messages.send')) ?>">
            <i class="bi bi-send-fill" aria-hidden="true"></i>
        </button>
    </form>
</section>
