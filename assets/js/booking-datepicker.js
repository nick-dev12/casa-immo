'use strict';

(function () {
    const MOBILE_BP = 767;

    const MONTHS_FULL = [
        'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
    ];

    const MONTHS_SHORT = [
        'jan.', 'fév.', 'mars', 'avr.', 'mai', 'juin',
        'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.',
    ];

    const WEEKDAYS = ['lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.', 'dim.'];

    const FLEX_OPTIONS = [
        { value: 0, label: 'Dates exactes' },
        { value: 1, label: '± 1 jour' },
        { value: 2, label: '± 2 jours' },
        { value: 3, label: '± 3 jours' },
        { value: 7, label: '± 7 jours' },
    ];

    const DURATION_OPTIONS = [
        { value: 'weekend', label: 'Un week-end', nights: 2 },
        { value: 'week', label: 'Une semaine', nights: 7 },
        { value: 'month', label: 'Un mois', nights: 30 },
    ];

    const isMobile = () => window.BookingMobile?.isMobile?.() ?? window.innerWidth <= MOBILE_BP;

    const backdrop = document.getElementById('bookingSearchBackdrop');

    const pad = (n) => String(n).padStart(2, '0');

    const toISO = (year, month, day) => `${year}-${pad(month + 1)}-${pad(day)}`;

    const parseISO = (iso) => {
        const [y, m, d] = iso.split('-').map(Number);
        return new Date(y, m - 1, d);
    };

    const todayISO = () => {
        const now = new Date();
        return toISO(now.getFullYear(), now.getMonth(), now.getDate());
    };

    const compareISO = (a, b) => {
        if (a === b) return 0;
        return a < b ? -1 : 1;
    };

    const addMonths = (year, month, delta) => {
        const date = new Date(year, month + delta, 1);
        return { year: date.getFullYear(), month: date.getMonth() };
    };

    const addDaysISO = (iso, days) => {
        const date = parseISO(iso);
        date.setDate(date.getDate() + days);
        return toISO(date.getFullYear(), date.getMonth(), date.getDate());
    };

    const monthKey = (year, month) => year * 12 + month;

    const daysInMonth = (year, month) => new Date(year, month + 1, 0).getDate();

    const startWeekdayMonday = (year, month) => {
        const day = new Date(year, month, 1).getDay();
        return day === 0 ? 6 : day - 1;
    };

    const formatDisplayDate = (iso) => {
        if (!iso) return '';
        const date = parseISO(iso);
        return `${date.getDate()} ${MONTHS_SHORT[date.getMonth()]} ${date.getFullYear()}`;
    };

    const formatTriggerLabel = (checkIn, checkOut, flexibleLabel) => {
        if (flexibleLabel) return flexibleLabel;
        if (checkIn && checkOut) {
            return `${formatDisplayDate(checkIn)} — ${formatDisplayDate(checkOut)}`;
        }
        if (checkIn) {
            return `${formatDisplayDate(checkIn)} — Date de départ`;
        }
        return 'Date d\'arrivée — Date de départ';
    };

    const showBackdrop = () => {
        if (!backdrop) return;
        backdrop.hidden = false;
        backdrop.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => backdrop.classList.add('is-visible'));
        document.body.classList.add('booking-search-overlay-open');
    };

    const hideBackdropIfIdle = () => {
        const guestsOpen = document.querySelector('.booking-guests-panel.is-open');
        const suggestionsOpen = document.querySelector('.search-suggestions:not([hidden])');
        const datesOpen = document.querySelector('.booking-datepicker-panel.is-open');
        if (guestsOpen || suggestionsOpen || datesOpen) return;

        if (!backdrop) return;
        backdrop.classList.remove('is-visible');
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('booking-search-overlay-open');
        setTimeout(() => {
            if (!backdrop.classList.contains('is-visible')) {
                backdrop.hidden = true;
            }
        }, 260);
    };

    const closeOtherPanels = (currentPanel) => {
        document.querySelectorAll('.booking-guests-panel.is-open').forEach((panel) => {
            panel.classList.remove('is-open');
            (panel._portalField || panel.closest('.booking-field-guests'))?.classList.remove('is-open');
            if (isMobile()) window.BookingMobile?.restore(panel);
            setTimeout(() => {
                if (!panel.classList.contains('is-open')) panel.hidden = true;
            }, 280);
        });

        document.querySelectorAll('.booking-datepicker-panel.is-open').forEach((panel) => {
            if (panel !== currentPanel) {
                panel.classList.remove('is-open');
                const fieldEl = panel._portalField || panel.closest('.booking-field-dates');
                fieldEl?.classList.remove('is-open', 'is-selecting-end');
                fieldEl?.querySelector('.booking-dates-trigger')?.setAttribute('aria-expanded', 'false');
                if (isMobile()) window.BookingMobile?.restore(panel);
                setTimeout(() => {
                    if (!panel.classList.contains('is-open')) panel.hidden = true;
                }, 280);
            }
        });

        document.querySelectorAll('.search-suggestions:not([hidden])').forEach((panel) => {
            panel.hidden = true;
            panel.innerHTML = '';
            (panel._portalField || panel.closest('.booking-field-dest'))?.classList.remove('is-open');
            if (isMobile()) window.BookingMobile?.restore(panel);
        });
    };

    const initDatepicker = (field) => {
        const trigger = field.querySelector('.booking-dates-trigger');
        const panel = field.querySelector('.booking-datepicker-panel');
        const display = field.querySelector('.booking-dates-value');
        const calendarsEl = field.querySelector('.booking-dp-calendars');
        const flexChipsEl = field.querySelector('.booking-dp-flex-chips');
        const flexMonthsEl = field.querySelector('.booking-dp-flex-months');
        const durationChipsEl = field.querySelector('.booking-dp-duration-chips');
        const checkInInput = field.querySelector('input[name="check_in"]');
        const checkOutInput = field.querySelector('input[name="check_out"]');
        const flexInput = field.querySelector('input[name="date_flex"]');

        if (!trigger || !panel || !calendarsEl) return;

        panel._portalField = field;

        const today = todayISO();
        const todayParts = parseISO(today);
        const initialIn = checkInInput?.value || null;
        const initialAnchor = initialIn ? parseISO(initialIn) : todayParts;

        const state = {
            viewYear: initialAnchor.getFullYear(),
            viewMonth: initialAnchor.getMonth(),
            checkIn: initialIn,
            checkOut: checkOutInput?.value || null,
            hover: null,
            flex: parseInt(flexInput?.value || '0', 10) || 0,
            tab: 'calendar',
            flexMonths: [],
            duration: '',
            flexibleLabel: '',
            mode: 'calendar',
        };

        const isSelectingEnd = () => Boolean(state.checkIn && !state.checkOut && state.mode === 'calendar');

        const syncInputs = () => {
            if (checkInInput) checkInInput.value = state.checkIn || '';
            if (checkOutInput) checkOutInput.value = state.checkOut || '';
            if (flexInput) flexInput.value = String(state.flex);
            if (display) {
                display.textContent = formatTriggerLabel(state.checkIn, state.checkOut, state.flexibleLabel);
            }
        };

        const syncSelectionState = () => {
            syncInputs();
            field.classList.toggle('is-selecting-end', isSelectingEnd());
        };

        const canNavigatePrev = () => {
            const min = { year: todayParts.getFullYear(), month: todayParts.getMonth() };
            return state.viewYear > min.year || (state.viewYear === min.year && state.viewMonth > min.month);
        };

        const ensureViewContains = (iso) => {
            if (!iso) return;
            const date = parseISO(iso);
            const target = monthKey(date.getFullYear(), date.getMonth());
            const left = monthKey(state.viewYear, state.viewMonth);
            const right = addMonths(state.viewYear, state.viewMonth, 1);
            const rightKey = monthKey(right.year, right.month);

            if (target < left) {
                state.viewYear = date.getFullYear();
                state.viewMonth = date.getMonth();
            } else if (target > rightKey) {
                const prev = addMonths(date.getFullYear(), date.getMonth(), -1);
                state.viewYear = prev.year;
                state.viewMonth = prev.month;
            }
        };

        const getDayClasses = (iso, isPast) => {
            const classes = ['booking-dp-day'];
            if (isPast) classes.push('is-disabled');

            if (state.checkIn && state.checkOut) {
                if (iso === state.checkIn) classes.push('is-range-start', 'is-selected');
                else if (iso === state.checkOut) classes.push('is-range-end', 'is-selected');
                else if (compareISO(iso, state.checkIn) > 0 && compareISO(iso, state.checkOut) < 0) {
                    classes.push('is-in-range');
                }
            } else if (state.checkIn && iso === state.checkIn) {
                classes.push('is-selected', 'is-range-start');
            } else if (state.checkIn && !state.checkOut && state.hover) {
                const start = state.checkIn;
                const end = state.hover;
                const min = compareISO(start, end) <= 0 ? start : end;
                const max = compareISO(start, end) <= 0 ? end : start;
                if (iso === min) classes.push('is-range-start', 'is-selected');
                else if (iso === max) classes.push('is-range-end', 'is-selected');
                else if (compareISO(iso, min) > 0 && compareISO(iso, max) < 0) classes.push('is-in-range');
            }

            if (iso === today) classes.push('is-today');
            return classes;
        };

        const renderMonth = (year, month, options = {}) => {
            const { showPrev = false, showNext = false } = options;
            const totalDays = daysInMonth(year, month);
            const offset = startWeekdayMonday(year, month);
            const cells = [];

            for (let i = 0; i < offset; i += 1) {
                cells.push('<span class="booking-dp-day is-empty" aria-hidden="true"></span>');
            }

            for (let day = 1; day <= totalDays; day += 1) {
                const iso = toISO(year, month, day);
                const isPast = compareISO(iso, today) < 0;
                const classes = getDayClasses(iso, isPast);
                cells.push(
                    `<button type="button" class="${classes.join(' ')}" data-date="${iso}" ${isPast ? 'disabled' : ''} aria-label="${day} ${MONTHS_FULL[month]} ${year}">${day}</button>`
                );
            }

            const weekdays = WEEKDAYS.map((d) => `<span class="booking-dp-weekday">${d}</span>`).join('');

            return `
                <div class="booking-dp-month" data-month="${month}" data-year="${year}">
                    <div class="booking-dp-month-head">
                        ${showPrev ? '<button type="button" class="booking-dp-nav booking-dp-nav-prev" aria-label="Mois précédent"><i class="bi bi-chevron-left"></i></button>' : '<span class="booking-dp-nav-spacer"></span>'}
                        <span class="booking-dp-month-title">${MONTHS_FULL[month]} ${year}</span>
                        ${showNext ? '<button type="button" class="booking-dp-nav booking-dp-nav-next" aria-label="Mois suivant"><i class="bi bi-chevron-right"></i></button>' : '<span class="booking-dp-nav-spacer"></span>'}
                    </div>
                    <div class="booking-dp-weekdays">${weekdays}</div>
                    <div class="booking-dp-days">${cells.join('')}</div>
                </div>`;
        };

        const renderCalendars = () => {
            const right = addMonths(state.viewYear, state.viewMonth, 1);
            calendarsEl.innerHTML =
                renderMonth(state.viewYear, state.viewMonth, { showPrev: canNavigatePrev() }) +
                renderMonth(right.year, right.month, { showNext: true });
        };

        const updateDayHighlights = () => {
            calendarsEl.querySelectorAll('.booking-dp-day[data-date]').forEach((btn) => {
                const iso = btn.dataset.date || '';
                const isPast = compareISO(iso, today) < 0;
                btn.className = getDayClasses(iso, isPast).join(' ');
            });
        };

        const renderFlexChips = () => {
            if (!flexChipsEl) return;
            flexChipsEl.innerHTML = FLEX_OPTIONS.map((opt) => (
                `<button type="button" class="booking-dp-chip${state.flex === opt.value ? ' is-active' : ''}" data-flex="${opt.value}">${opt.label}</button>`
            )).join('');
        };

        const renderFlexMonths = () => {
            if (!flexMonthsEl) return;
            const items = [];
            let { year, month } = { year: todayParts.getFullYear(), month: todayParts.getMonth() };

            for (let i = 0; i < 12; i += 1) {
                const key = `${year}-${pad(month + 1)}`;
                const active = state.flexMonths.includes(key);
                items.push(
                    `<button type="button" class="booking-dp-month-chip${active ? ' is-active' : ''}" data-flex-month="${key}">${MONTHS_FULL[month]} ${year}</button>`
                );
                ({ year, month } = addMonths(year, month, 1));
            }

            flexMonthsEl.innerHTML = items.join('');
        };

        const renderDurationChips = () => {
            if (!durationChipsEl) return;
            durationChipsEl.innerHTML = DURATION_OPTIONS.map((opt) => (
                `<button type="button" class="booking-dp-chip${state.duration === opt.value ? ' is-active' : ''}" data-duration="${opt.value}">${opt.label}</button>`
            )).join('');
        };

        const setTab = (tab) => {
            state.tab = tab;
            panel.querySelectorAll('.booking-dp-tab').forEach((btn) => {
                const active = btn.dataset.tab === tab;
                btn.classList.toggle('is-active', active);
                btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panel.querySelectorAll('.booking-dp-pane').forEach((pane) => {
                pane.hidden = pane.dataset.pane !== tab;
            });
        };

        const applyFlexibleSelection = () => {
            if (!state.flexMonths.length || !state.duration) return false;

            const sortedMonths = [...state.flexMonths].sort();
            const [yearStr, monthStr] = sortedMonths[0].split('-');
            const year = parseInt(yearStr, 10);
            const month = parseInt(monthStr, 10) - 1;
            let checkIn = toISO(year, month, 1);
            if (compareISO(checkIn, today) < 0) checkIn = today;

            const durationOpt = DURATION_OPTIONS.find((o) => o.value === state.duration);
            const nights = durationOpt?.nights || 7;
            const checkOut = addDaysISO(checkIn, nights);

            state.mode = 'flexible';
            state.checkIn = checkIn;
            state.checkOut = checkOut;
            state.flexibleLabel = `${durationOpt?.label || 'Séjour'} · ${MONTHS_FULL[month]} ${year}`;
            state.flex = 0;
            syncSelectionState();
            return true;
        };

        const resetFlexibleMode = () => {
            state.mode = 'calendar';
            state.flexibleLabel = '';
        };

        const renderAll = () => {
            renderCalendars();
            renderFlexChips();
            renderFlexMonths();
            renderDurationChips();
            syncSelectionState();
        };

        const closePanel = (hideOverlay = true) => {
            panel.classList.remove('is-open');
            field.classList.remove('is-open', 'is-selecting-end');
            trigger.setAttribute('aria-expanded', 'false');
            state.hover = null;
            if (isMobile()) window.BookingMobile?.restore(panel);
            setTimeout(() => {
                if (!panel.classList.contains('is-open')) panel.hidden = true;
            }, 280);
            if (hideOverlay && isMobile()) hideBackdropIfIdle();
        };

        const tryClosePanel = (hideOverlay = true) => {
            if (isSelectingEnd()) return false;
            closePanel(hideOverlay);
            return true;
        };

        const openPanel = () => {
            closeOtherPanels(panel);
            if (state.checkIn) ensureViewContains(state.checkIn);
            if (state.checkOut) ensureViewContains(state.checkOut);
            field.classList.add('is-open');
            panel.hidden = false;
            if (isMobile()) window.BookingMobile?.attachToBody(panel);
            trigger.setAttribute('aria-expanded', 'true');
            requestAnimationFrame(() => panel.classList.add('is-open'));
            if (isMobile()) showBackdrop();
            renderAll();
            field.classList.toggle('is-selecting-end', isSelectingEnd());
        };

        const completeRangeSelection = () => {
            if (!state.checkIn || !state.checkOut) return;
            resetFlexibleMode();
            syncSelectionState();
            requestAnimationFrame(() => {
                setTimeout(() => closePanel(true), 180);
            });
        };

        const selectDate = (iso) => {
            resetFlexibleMode();

            if (!state.checkIn || (state.checkIn && state.checkOut)) {
                state.checkIn = iso;
                state.checkOut = null;
            } else if (compareISO(iso, state.checkIn) <= 0) {
                state.checkIn = iso;
                state.checkOut = null;
            } else {
                state.checkOut = iso;
            }

            state.hover = null;
            ensureViewContains(iso);
            renderCalendars();
            syncSelectionState();

            if (state.checkIn && state.checkOut) {
                completeRangeSelection();
            }
        };

        const navigateMonth = (delta) => {
            if (delta < 0 && !canNavigatePrev()) return;
            ({ year: state.viewYear, month: state.viewMonth } = addMonths(state.viewYear, state.viewMonth, delta));
            state.hover = null;
            renderCalendars();
        };

        field.addEventListener('click', (e) => {
            if (panel.contains(e.target)) return;
            e.stopPropagation();
            if (!panel.classList.contains('is-open')) openPanel();
        });

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (panel.classList.contains('is-open')) tryClosePanel();
            else openPanel();
        });

        panel.addEventListener('click', (e) => {
            e.stopPropagation();

            const dayBtn = e.target.closest('.booking-dp-day[data-date]');
            if (dayBtn && !dayBtn.disabled) {
                selectDate(dayBtn.dataset.date);
                return;
            }

            if (e.target.closest('.booking-dp-nav-prev')) {
                navigateMonth(-1);
                return;
            }

            if (e.target.closest('.booking-dp-nav-next')) {
                navigateMonth(1);
                return;
            }

            const tabBtn = e.target.closest('.booking-dp-tab');
            if (tabBtn) {
                setTab(tabBtn.dataset.tab || 'calendar');
                return;
            }

            const flexBtn = e.target.closest('[data-flex]');
            if (flexBtn) {
                state.flex = parseInt(flexBtn.dataset.flex || '0', 10);
                renderFlexChips();
                syncInputs();
                return;
            }

            const monthChip = e.target.closest('[data-flex-month]');
            if (monthChip) {
                const key = monthChip.dataset.flexMonth || '';
                if (state.flexMonths.includes(key)) {
                    state.flexMonths = state.flexMonths.filter((m) => m !== key);
                } else {
                    state.flexMonths.push(key);
                }
                renderFlexMonths();
                if (state.flexMonths.length && state.duration) {
                    if (applyFlexibleSelection()) {
                        requestAnimationFrame(() => setTimeout(() => closePanel(true), 220));
                    }
                } else {
                    state.flexibleLabel = '';
                    if (state.mode === 'flexible') {
                        state.checkIn = null;
                        state.checkOut = null;
                        state.mode = 'calendar';
                    }
                    syncSelectionState();
                }
                return;
            }

            const durationBtn = e.target.closest('[data-duration]');
            if (durationBtn) {
                state.duration = durationBtn.dataset.duration || '';
                renderDurationChips();
                if (state.flexMonths.length && state.duration) {
                    if (applyFlexibleSelection()) {
                        requestAnimationFrame(() => setTimeout(() => closePanel(true), 220));
                    }
                }
            }
        });

        panel.addEventListener('mouseover', (e) => {
            const dayBtn = e.target.closest('.booking-dp-day[data-date]');
            if (!dayBtn || dayBtn.disabled || !state.checkIn || state.checkOut) return;
            const nextHover = dayBtn.dataset.date || null;
            if (nextHover === state.hover) return;
            state.hover = nextHover;
            updateDayHighlights();
        });

        panel.addEventListener('mouseleave', () => {
            if (!state.hover) return;
            state.hover = null;
            updateDayHighlights();
        });

        panel.addEventListener('touchstart', (e) => {
            const dayBtn = e.target.closest('.booking-dp-day[data-date]');
            if (!dayBtn || dayBtn.disabled || !state.checkIn || state.checkOut) return;
            state.hover = dayBtn.dataset.date || null;
            updateDayHighlights();
        }, { passive: true });

        panel.querySelector('.booking-dp-done')?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (state.tab === 'flexible' && applyFlexibleSelection()) {
                closePanel(true);
                return;
            }
            if (state.checkIn && state.checkOut) closePanel(true);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape' || !panel.classList.contains('is-open')) return;
            if (isSelectingEnd()) {
                state.checkIn = null;
                state.hover = null;
                renderCalendars();
                syncSelectionState();
                return;
            }
            tryClosePanel(true);
        });

        document.addEventListener('click', (e) => {
            if (!panel.classList.contains('is-open')) return;
            const inside = field.contains(e.target) || panel.contains(e.target);
            if (!inside) tryClosePanel(isMobile());
        });

        backdrop?.addEventListener('click', () => {
            if (panel.classList.contains('is-open')) tryClosePanel(true);
        });

        window.addEventListener('resize', () => {
            if (panel.classList.contains('is-open') && isMobile()) showBackdrop();
        });

        renderAll();
    };

    const bootDatepickers = () => {
        document.querySelectorAll('.booking-field-dates').forEach(initDatepicker);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootDatepickers);
    } else {
        bootDatepickers();
    }

    window.BookingDatepicker = {
        closeAll() {
            document.querySelectorAll('.booking-datepicker-panel.is-open').forEach((panel) => {
                panel.classList.remove('is-open');
                const fieldEl = panel._portalField || panel.closest('.booking-field-dates');
                fieldEl?.classList.remove('is-open', 'is-selecting-end');
                fieldEl?.querySelector('.booking-dates-trigger')?.setAttribute('aria-expanded', 'false');
                if (window.BookingMobile?.isMobile?.()) window.BookingMobile.restore(panel);
                setTimeout(() => {
                    if (!panel.classList.contains('is-open')) panel.hidden = true;
                }, 280);
            });
            hideBackdropIfIdle();
        },
    };
})();
