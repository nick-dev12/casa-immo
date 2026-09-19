'use strict';

(function () {
    const root = document.getElementById('messagesThread');
    const feed = document.getElementById('messagesFeed');
    const form = document.getElementById('messagesComposer');
    const input = document.getElementById('messagesBody');

    if (!root || !feed || !form || !input) {
        return;
    }

    const pollUrl = root.dataset.pollUrl || '';
    const sendUrl = root.dataset.sendUrl || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || form.querySelector('[name="_token"]')?.value
        || '';
    const emptyError = root.dataset.emptyError || 'Écrivez un message.';
    const sendError = root.dataset.sendError || 'Impossible d\'envoyer le message.';
    let lastId = lastMessageId();
    let sending = false;

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    function lastMessageId() {
        const nodes = feed.querySelectorAll('[data-message-id]');
        if (!nodes.length) {
            return 0;
        }
        return parseInt(nodes[nodes.length - 1].getAttribute('data-message-id') || '0', 10);
    }

    const scrollToBottom = () => {
        feed.scrollTop = feed.scrollHeight;
        window.scrollTo(0, document.body.scrollHeight);
    };

    const appendMessage = (message, options = {}) => {
        if (!message || !message.id) {
            return;
        }
        if (feed.querySelector('[data-message-id="' + message.id + '"]')) {
            return;
        }

        const empty = document.getElementById('messagesEmpty');
        if (empty) {
            empty.remove();
        }

        const article = document.createElement('article');
        article.className = 'messages-bubble' + (message.mine ? ' is-mine' : '');
        if (options.incoming) {
            article.classList.add('is-new');
        }
        article.dataset.messageId = String(message.id);
        article.innerHTML = '<p>' + escapeHtml(message.body).replace(/\n/g, '<br>') + '</p>'
            + '<time>' + escapeHtml(message.time_label || '') + '</time>';
        feed.appendChild(article);
        lastId = Math.max(lastId, parseInt(String(message.id), 10));
        scrollToBottom();

        if (options.incoming) {
            window.setTimeout(() => article.classList.remove('is-new'), 1600);
        }
    };

    const resizeInput = () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 112) + 'px';
    };

    const sendMessage = async () => {
        const body = input.value.trim();
        if (body === '' || sending) {
            if (body === '') {
                alert(emptyError);
            }
            return;
        }

        sending = true;
        form.querySelector('.messages-send')?.setAttribute('disabled', 'disabled');

        const payload = new FormData();
        payload.append('_token', csrfToken);
        payload.append('body', body);

        try {
            const response = await fetch(sendUrl, {
                method: 'POST',
                body: payload,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });
            const json = await response.json();
            if (!response.ok || json.success === false) {
                throw new Error(json.message || sendError);
            }
            input.value = '';
            resizeInput();
            appendMessage(json.message);
        } catch (err) {
            alert(err.message || sendError);
        } finally {
            sending = false;
            form.querySelector('.messages-send')?.removeAttribute('disabled');
            input.focus();
        }
    };

    const poll = async () => {
        if (!pollUrl || document.hidden) {
            return;
        }

        try {
            const response = await fetch(pollUrl + (pollUrl.includes('?') ? '&' : '?') + 'after=' + lastId, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const json = await response.json();
            if (!json.success || !Array.isArray(json.messages)) {
                return;
            }

            json.messages.forEach((message) => {
                if (!message.mine) {
                    appendMessage(message, { incoming: true });
                } else {
                    appendMessage(message);
                }
            });

            if (window.ZigMessagesNotify && typeof json.unread_count === 'number') {
                window.ZigMessagesNotify.updateBadges(json.unread_count);
            }
        } catch {
            /* ignore polling errors */
        }
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        sendMessage();
    });

    input.addEventListener('input', resizeInput);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    scrollToBottom();
    setInterval(poll, 4000);
})();
