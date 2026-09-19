'use strict';

window.ZigReservationsNotify = (function () {
    const STORAGE_KEY = 'zig_last_incoming_booking_id';
    const POLL_MS = 12000;

    let pollTimer = null;
    let lastIncomingId = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10) || 0;
    let initialized = false;
    let baselineReady = lastIncomingId > 0;

    const getConfig = () => {
        const body = document.body;
        return {
            auth: body?.dataset.auth === '1',
            pollUrl: body?.dataset.reservationsPollUrl || '/api/reservations/unread',
            appUrl: (body?.dataset.appUrl || '').replace(/\/$/, ''),
            notifyTitle: body?.dataset.reservationsNotifyTitle || 'Nouvelle réservation',
            notifyEnable: body?.dataset.reservationsNotifyEnable || 'Activer les notifications',
            notifyDismiss: body?.dataset.reservationsNotifyDismiss || 'Plus tard',
        };
    };

    const formatBadge = (count) => (count > 9 ? '9+' : String(count));

    const updateBadges = (count) => {
        document.querySelectorAll('[data-reservations-badge]').forEach((badge) => {
            if (count > 0) {
                badge.textContent = formatBadge(count);
                badge.hidden = false;
                badge.classList.add('is-visible');
            } else {
                badge.textContent = '';
                badge.hidden = true;
                badge.classList.remove('is-visible');
            }
        });

        document.querySelectorAll('[data-reservations-nav]').forEach((link) => {
            link.classList.toggle('has-unread', count > 0);
        });
    };

    const ensureToastRoot = () => {
        let root = document.getElementById('reservationsToastRoot');
        if (!root) {
            root = document.createElement('div');
            root.id = 'reservationsToastRoot';
            root.className = 'messages-toast-root';
            root.setAttribute('aria-live', 'polite');
            document.body.appendChild(root);
        }
        return root;
    };

    const showInAppToast = (payload) => {
        const root = ensureToastRoot();
        const toast = document.createElement('a');
        toast.href = payload.url || '/reservations';
        toast.className = 'messages-toast';
        toast.innerHTML = ''
            + '<span class="messages-toast-icon" aria-hidden="true"><i class="bi bi-calendar-check-fill"></i></span>'
            + '<span class="messages-toast-body">'
            + '<strong>' + escapeHtml(payload.title || payload.sender_name || '') + '</strong>'
            + '<span>' + escapeHtml(payload.body || '') + '</span>'
            + '</span>'
            + '<i class="bi bi-chevron-right messages-toast-arrow" aria-hidden="true"></i>';

        root.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('is-visible'));

        window.setTimeout(() => {
            toast.classList.remove('is-visible');
            window.setTimeout(() => toast.remove(), 280);
        }, 6000);
    };

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const showBrowserNotification = (payload) => {
        if (!('Notification' in window) || Notification.permission !== 'granted') {
            return;
        }

        const cfg = getConfig();
        const title = payload.title || cfg.notifyTitle;
        const notification = new Notification(title, {
            body: payload.body || '',
            icon: cfg.appUrl ? cfg.appUrl + '/assets/img/icon-192.png' : undefined,
            tag: 'zig-booking-' + (payload.booking_id || payload.id || Date.now()),
            renotify: true,
        });

        notification.onclick = () => {
            window.focus();
            if (payload.url) {
                window.location.href = payload.url;
            }
            notification.close();
        };
    };

    const handleIncoming = (payload, options = {}) => {
        if (!payload || !payload.id) {
            return;
        }

        const notificationId = parseInt(String(payload.id), 10);
        if (!Number.isFinite(notificationId) || notificationId <= 0) {
            return;
        }

        if (notificationId <= lastIncomingId && !options.force) {
            return;
        }

        const detailRoot = document.querySelector('[data-reservation-detail-id]');
        const activeBookingId = detailRoot
            ? parseInt(detailRoot.dataset.reservationDetailId || '0', 10)
            : 0;
        const sameOpenDetail = activeBookingId > 0
            && payload.booking_id === activeBookingId
            && !document.hidden;

        if (!sameOpenDetail) {
            showInAppToast(payload);
            showBrowserNotification(payload);
            document.dispatchEvent(new CustomEvent('zig:new-booking', { detail: payload }));
        }

        lastIncomingId = Math.max(lastIncomingId, notificationId);
        localStorage.setItem(STORAGE_KEY, String(lastIncomingId));
    };

    const poll = async () => {
        const cfg = getConfig();
        if (!cfg.auth) {
            return;
        }

        try {
            const response = await fetch(cfg.pollUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            const json = await response.json();
            if (!response.ok || !json.success) {
                return;
            }

            updateBadges(parseInt(String(json.count || 0), 10) || 0);

            if (json.latest_incoming) {
                if (!baselineReady) {
                    lastIncomingId = parseInt(String(json.latest_incoming.id || 0), 10) || 0;
                    localStorage.setItem(STORAGE_KEY, String(lastIncomingId));
                    baselineReady = true;
                } else {
                    handleIncoming(json.latest_incoming);
                }
            }

            document.dispatchEvent(new CustomEvent('zig:reservations-unread', {
                detail: {
                    count: json.count || 0,
                    booking_ids: json.booking_ids || [],
                },
            }));
        } catch {
            /* ignore */
        }
    };

    const requestPermission = async () => {
        if (!('Notification' in window)) {
            return false;
        }
        if (Notification.permission === 'granted') {
            return true;
        }
        if (Notification.permission === 'denied') {
            return false;
        }
        const result = await Notification.requestPermission();
        return result === 'granted';
    };

    const setupPermissionBanner = () => {
        if (!('Notification' in window) || Notification.permission !== 'default') {
            return;
        }

        const page = document.querySelector('[data-reservations-enable-banner]');
        if (!page || page.dataset.bannerReady === '1') {
            return;
        }
        page.dataset.bannerReady = '1';

        const cfg = getConfig();
        const banner = document.createElement('div');
        banner.className = 'messages-permission-banner';
        banner.innerHTML = ''
            + '<div class="messages-permission-banner-text">'
            + '<i class="bi bi-bell-fill" aria-hidden="true"></i>'
            + '<span>' + escapeHtml(cfg.notifyEnable) + '</span>'
            + '</div>'
            + '<div class="messages-permission-banner-actions">'
            + '<button type="button" class="messages-permission-enable">' + escapeHtml(cfg.notifyEnable) + '</button>'
            + '<button type="button" class="messages-permission-dismiss">' + escapeHtml(cfg.notifyDismiss) + '</button>'
            + '</div>';

        page.prepend(banner);

        banner.querySelector('.messages-permission-enable')?.addEventListener('click', async () => {
            await requestPermission();
            banner.remove();
        });
        banner.querySelector('.messages-permission-dismiss')?.addEventListener('click', () => {
            banner.remove();
        });
    };

    const bootstrap = () => {
        const cfg = getConfig();
        if (!cfg.auth || initialized) {
            return;
        }
        initialized = true;
        pollTimer = window.setInterval(poll, POLL_MS);
        setupPermissionBanner();
        poll();

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                poll();
            }
        });
    };

    return {
        bootstrap,
        poll,
        updateBadges,
        handleIncoming,
        requestPermission,
    };
})();

document.addEventListener('DOMContentLoaded', () => {
    window.ZigReservationsNotify.bootstrap();
});
