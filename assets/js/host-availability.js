'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('hostAvailabilityCalendar');
    if (!root) {
        return;
    }

    let config;
    try {
        config = JSON.parse(root.dataset.config || '{}');
    } catch {
        return;
    }

    const blocked = new Set(config.blocked || []);
    const reserved = new Set(config.reserved || []);
    const selected = new Set();
    const labels = config.labels || {};
    const weekdays = config.weekdays || ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
    const months = config.months || [];

    const monthLabel = root.querySelector('[data-month-label]');
    const gridEl = root.querySelector('[data-grid]');
    const weekdaysEl = root.querySelector('[data-weekdays]');
    const blockForm = document.getElementById('hostAvailBlockForm');
    const blockBtn = root.querySelector('[data-block-btn]');
    const datesInputs = root.querySelector('[data-dates-inputs]');
    const unblockForm = document.getElementById('hostAvailUnblockForm');
    const unblockDateInput = unblockForm?.querySelector('[data-unblock-date]');

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();

    const pad = (n) => String(n).padStart(2, '0');
    const toISO = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;

    const parseISO = (iso) => {
        const [y, m, d] = iso.split('-').map(Number);
        return new Date(y, m - 1, d);
    };

    const isPast = (iso) => parseISO(iso) < today;

    const renderWeekdays = () => {
        if (!weekdaysEl) {
            return;
        }
        weekdaysEl.innerHTML = weekdays
            .map((day) => `<span class="host-avail-weekday">${day}</span>`)
            .join('');
    };

    const syncBlockForm = () => {
        if (!datesInputs || !blockBtn) {
            return;
        }
        datesInputs.innerHTML = '';
        selected.forEach((iso) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'dates[]';
            input.value = iso;
            datesInputs.appendChild(input);
        });
        blockBtn.disabled = selected.size === 0;
        blockBtn.textContent = selected.size > 0
            ? `${labels.block || 'Bloquer'} (${selected.size})`
            : (labels.block || 'Bloquer');
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
            cells.push('<span class="host-avail-day host-avail-day-empty" aria-hidden="true"></span>');
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const iso = toISO(viewYear, viewMonth, day);
            const past = isPast(iso);
            const isReserved = reserved.has(iso);
            const isBlocked = blocked.has(iso);
            const isSelected = selected.has(iso);

            let stateClass = 'host-avail-day-available';
            let ariaLabel = iso;

            if (past) {
                stateClass = 'host-avail-day-past';
            } else if (isReserved) {
                stateClass = 'host-avail-day-reserved';
                ariaLabel += ' — réservé';
            } else if (isBlocked) {
                stateClass = 'host-avail-day-blocked';
                ariaLabel += ' — bloqué';
            } else if (isSelected) {
                stateClass = 'host-avail-day-selected';
                ariaLabel += ' — sélectionné';
            }

            const disabled = past || isReserved;
            cells.push(
                `<button type="button"
                    class="host-avail-day ${stateClass}"
                    data-date="${iso}"
                    ${disabled ? 'disabled' : ''}
                    aria-label="${ariaLabel}"
                    aria-pressed="${isSelected ? 'true' : 'false'}">${day}</button>`
            );
        }

        gridEl.innerHTML = cells.join('');
    };

    root.addEventListener('click', (event) => {
        const target = event.target.closest('[data-action]');
        if (target) {
            const action = target.dataset.action;
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

        const dayBtn = event.target.closest('.host-avail-day[data-date]');
        if (!dayBtn || dayBtn.disabled) {
            return;
        }

        const iso = dayBtn.dataset.date || '';
        if (iso === '') {
            return;
        }

        if (blocked.has(iso)) {
            if (unblockForm && unblockDateInput) {
                unblockDateInput.value = iso;
                unblockForm.submit();
            }
            return;
        }

        if (selected.has(iso)) {
            selected.delete(iso);
        } else {
            selected.add(iso);
        }

        syncBlockForm();
        renderMonth();
    });

    renderWeekdays();
    renderMonth();
    syncBlockForm();
});
