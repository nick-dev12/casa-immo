'use strict';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.pd-booking-calendar[data-config]').forEach((root) => {
        initBookingCalendar(root);
    });
});

function initBookingCalendar(root) {
    let config;
    try {
        config = JSON.parse(root.dataset.config || '{}');
    } catch {
        return;
    }

    const form = root.closest('.pd-booking-form');
    if (!form) {
        return;
    }

    const checkInInput = form.querySelector('[data-check-in]');
    const checkOutInput = form.querySelector('[data-check-out]');
    const unavailable = new Set(config.unavailable || []);
    const weekdays = config.weekdays || ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
    const months = config.months || [];
    const labels = config.labels || {};

    const monthLabel = root.querySelector('[data-month-label]');
    const gridEl = root.querySelector('[data-grid]');
    const weekdaysEl = root.querySelector('[data-weekdays]');

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();
    let checkIn = config.checkIn || '';
    let checkOut = config.checkOut || '';

    const pad = (n) => String(n).padStart(2, '0');
    const toISO = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;

    const parseISO = (iso) => {
        const [y, m, d] = iso.split('-').map(Number);
        return new Date(y, m - 1, d);
    };

    const compareISO = (a, b) => {
        if (a === b) {
            return 0;
        }
        return a < b ? -1 : 1;
    };

    const isPast = (iso) => parseISO(iso) < today;

    const rangeHasUnavailable = (start, end) => {
        const min = compareISO(start, end) <= 0 ? start : end;
        const max = compareISO(start, end) <= 0 ? end : start;
        let cursor = parseISO(min);
        const endDate = parseISO(max);
        while (cursor <= endDate) {
            const iso = toISO(cursor.getFullYear(), cursor.getMonth(), cursor.getDate());
            if (unavailable.has(iso)) {
                return true;
            }
            cursor.setDate(cursor.getDate() + 1);
        }
        return false;
    };

    const syncInputs = () => {
        if (checkInInput) {
            checkInInput.value = checkIn;
        }
        if (checkOutInput) {
            checkOutInput.value = checkOut;
        }
    };

    const renderWeekdays = () => {
        if (!weekdaysEl) {
            return;
        }
        weekdaysEl.innerHTML = weekdays
            .map((day) => `<span class="pd-booking-cal-weekday">${day}</span>`)
            .join('');
    };

    const dayStateClass = (iso) => {
        const past = isPast(iso);
        const reserved = unavailable.has(iso);

        if (past) {
            return 'pd-booking-cal-day-past';
        }
        if (reserved) {
            return 'pd-booking-cal-day-reserved';
        }
        if (checkIn && checkOut) {
            if (iso === checkIn || iso === checkOut) {
                return 'pd-booking-cal-day-selected';
            }
            if (compareISO(iso, checkIn) > 0 && compareISO(iso, checkOut) < 0) {
                return 'pd-booking-cal-day-in-range';
            }
        } else if (checkIn && iso === checkIn) {
            return 'pd-booking-cal-day-selected';
        }
        return 'pd-booking-cal-day-available';
    };

    const renderMonth = () => {
        if (!gridEl || !monthLabel) {
            return;
        }

        monthLabel.textContent = `${months[viewMonth] || ''} ${viewYear}`;

        const firstDay = new Date(viewYear, viewMonth, 1);
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        let startWeekday = firstDay.getDay();
        startWeekday = startWeekday === 0 ? 6 : startWeekday - 1;

        const cells = [];

        for (let i = 0; i < startWeekday; i++) {
            cells.push('<span class="pd-booking-cal-day pd-booking-cal-day-empty" aria-hidden="true"></span>');
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const iso = toISO(viewYear, viewMonth, day);
            const past = isPast(iso);
            const reserved = unavailable.has(iso);
            const stateClass = dayStateClass(iso);
            const disabled = past || reserved;
            const ariaSuffix = reserved ? ` — ${labels.reserved || 'Réservé'}` : '';

            cells.push(
                `<button type="button"
                    class="pd-booking-cal-day ${stateClass}"
                    data-date="${iso}"
                    ${disabled ? 'disabled' : ''}
                    aria-label="${iso}${ariaSuffix}">${day}</button>`
            );
        }

        gridEl.innerHTML = cells.join('');
    };

    root.addEventListener('click', (event) => {
        const navBtn = event.target.closest('[data-action]');
        if (navBtn) {
            const action = navBtn.dataset.action;
            if (action === 'prev-month') {
                viewMonth -= 1;
                if (viewMonth < 0) {
                    viewMonth = 11;
                    viewYear -= 1;
                }
                renderMonth();
            }
            if (action === 'next-month') {
                viewMonth += 1;
                if (viewMonth > 11) {
                    viewMonth = 0;
                    viewYear += 1;
                }
                renderMonth();
            }
            return;
        }

        const dayBtn = event.target.closest('.pd-booking-cal-day[data-date]');
        if (!dayBtn || dayBtn.disabled) {
            return;
        }

        const iso = dayBtn.dataset.date || '';
        if (iso === '') {
            return;
        }

        if (!checkIn || (checkIn && checkOut)) {
            checkIn = iso;
            checkOut = '';
        } else if (compareISO(iso, checkIn) <= 0) {
            checkIn = iso;
            checkOut = '';
        } else {
            if (rangeHasUnavailable(checkIn, iso)) {
                checkIn = iso;
                checkOut = '';
            } else {
                checkOut = iso;
            }
        }

        syncInputs();
        renderMonth();
    });

    renderWeekdays();
    syncInputs();
    renderMonth();
}
