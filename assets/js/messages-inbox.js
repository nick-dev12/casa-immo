'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const inbox = document.getElementById('messagesInbox');
    if (!inbox) {
        return;
    }

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const updateInboxItem = (conversation) => {
        const item = inbox.querySelector('[data-conversation-id="' + conversation.id + '"]');
        if (!item) {
            return;
        }

        item.classList.add('is-unread', 'is-new-inbox');
        item.dataset.unreadCount = String(conversation.unread_count || 0);

        const preview = item.querySelector('.messages-inbox-preview');
        if (preview && conversation.last_body) {
            preview.textContent = conversation.last_body;
        }

        const time = item.querySelector('.messages-inbox-time');
        if (time && conversation.last_at) {
            time.textContent = conversation.last_at;
        }

        let badge = item.querySelector('.messages-unread-dot');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'messages-unread-dot';
            item.appendChild(badge);
        }
        badge.textContent = conversation.unread_count > 9 ? '9+' : String(conversation.unread_count);

        const thumb = item.querySelector('.messages-inbox-thumb');
        if (thumb) {
            thumb.remove();
        }

        window.setTimeout(() => item.classList.remove('is-new-inbox'), 1800);
    };

    document.addEventListener('zig:messages-unread', (event) => {
        const conversations = event.detail?.conversations || [];
        conversations.forEach(updateInboxItem);

        if (conversations.length > 0) {
            inbox.classList.add('has-unread');
        } else {
            inbox.classList.remove('has-unread');
        }
    });
});
