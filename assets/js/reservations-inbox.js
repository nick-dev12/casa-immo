'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('reservationsList');
    if (!list) {
        return;
    }

    const markUnread = (bookingId) => {
        const row = list.querySelector('[data-booking-id="' + bookingId + '"]');
        if (!row) {
            return;
        }

        row.classList.add('is-unread', 'is-new-inbox');
        window.setTimeout(() => row.classList.remove('is-new-inbox'), 1800);

        let badge = row.querySelector('.reservations-unread-dot');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'reservations-unread-dot';
            row.appendChild(badge);
        }
        badge.textContent = '1';
    };

    document.addEventListener('zig:reservations-unread', (event) => {
        const bookingIds = event.detail?.booking_ids || [];
        bookingIds.forEach((id) => markUnread(id));
        list.classList.toggle('has-unread', bookingIds.length > 0);
    });
});
