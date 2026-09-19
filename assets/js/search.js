'use strict';

(function () {
    const DEBOUNCE_MS = 200;
    const MOBILE_BP = 767;

    const debounce = (fn, ms) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), ms);
        };
    };

    const isMobile = () => window.BookingMobile?.isMobile?.() ?? window.innerWidth <= MOBILE_BP;

    const escapeHtml = (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    const backdrop = document.getElementById('bookingSearchBackdrop');

    const showBackdrop = () => {
        if (!backdrop) return;
        backdrop.hidden = false;
        backdrop.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => backdrop.classList.add('is-visible'));
        document.body.classList.add('booking-search-overlay-open');
    };

    const hideBackdrop = () => {
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

    const getFormCity = (input) => {
        const form = input.closest('form');
        const hidden = form?.querySelector('input[name="city"]');
        if (hidden) return hidden.value || '';
        const select = form?.querySelector('select[name="city"]');
        return select?.value || '';
    };

    const setFormCity = (input, city) => {
        const form = input.closest('form');
        const hidden = form?.querySelector('input[name="city"]');
        if (hidden) hidden.value = city;
        const select = form?.querySelector('select[name="city"]');
        if (select) select.value = city;
    };

    const setFormDistrict = (input, district) => {
        const form = input.closest('form');
        const hidden = form?.querySelector('input[name="district"]');
        if (hidden) hidden.value = district;
    };

    const getFormDistrict = (input) => {
        const form = input.closest('form');
        const hidden = form?.querySelector('input[name="district"]');
        return hidden?.value || '';
    };

    const positionSuggestionsPanel = (input, panel) => {
        if (!isMobile()) {
            panel.style.top = '';
            panel.style.left = '';
            panel.style.width = '';
            panel.classList.remove('is-positioned');
            return;
        }

        const rect = input.getBoundingClientRect();
        const inset = 16;
        const width = Math.min(rect.width, window.innerWidth - inset * 2);

        panel.style.top = `${rect.bottom + 6}px`;
        panel.style.left = `${Math.max(inset, rect.left)}px`;
        panel.style.width = `${width}px`;
        panel.classList.add('is-positioned');
    };

    const hideSuggestions = (panel) => {
        if (!panel) return;
        panel.hidden = true;
        panel.innerHTML = '';
        (panel._portalField || panel.closest('.booking-field-dest'))?.classList.remove('is-open');
        if (isMobile()) window.BookingMobile?.restore(panel);

        const guestsOpen = document.querySelector('.booking-guests-panel.is-open');
        const datesOpen = document.querySelector('.booking-datepicker-panel.is-open');
        if (!guestsOpen && !datesOpen) hideBackdrop();
    };

    const showSuggestions = (panel, input) => {
        if (!panel) return;
        window.BookingDatepicker?.closeAll?.();
        panel.hidden = false;
        (panel._portalField || panel.closest('.booking-field-dest'))?.classList.add('is-open');
        if (isMobile()) window.BookingMobile?.attachToBody(panel);
        positionSuggestionsPanel(input, panel);
        if (isMobile()) showBackdrop();
    };

    const renderPlaceGroup = (title, places) => {
        if (!places.length) return '';
        let html = `<div class="search-suggestion-group"><span class="search-suggestion-label">${title}</span>`;
        places.forEach((place) => {
            const label = place.label || `${place.name}, ${place.city}`;
            const kind = place.kind || (place.name === place.city ? 'city' : 'district');
            html += `
                <button type="button" class="search-suggestion-item search-suggestion-place"
                        data-city="${escapeHtml(place.city)}"
                        data-district="${escapeHtml(kind === 'city' ? '' : place.name)}"
                        data-kind="${escapeHtml(kind)}"
                        data-query="${escapeHtml(label)}">
                    <span class="search-suggestion-icon"><i class="bi bi-${kind === 'city' ? 'building' : 'geo-alt-fill'}"></i></span>
                    <div class="search-suggestion-text">
                        <strong>${escapeHtml(place.name)}</strong>
                        <small>${escapeHtml(place.subtitle || place.city + ', Casamance')}</small>
                    </div>
                </button>`;
        });
        html += '</div>';
        return html;
    };

    const renderSuggestions = (panel, data, input, type) => {
        if (!panel) return;

        const { results = [], cities = [], districts = [], places = [] } = data;
        const cityItems = cities.length
            ? cities
            : places.filter((place) => (place.kind || '') === 'city');
        const districtItems = districts.length
            ? districts
            : places.filter((place) => (place.kind || 'district') === 'district' && place.name !== place.city);

        if (!results.length && !cityItems.length && !districtItems.length) {
            panel.innerHTML = '<div class="search-suggestion-empty">Aucune correspondance pour cette destination</div>';
            showSuggestions(panel, input);
            return;
        }

        let html = renderPlaceGroup('Villes', cityItems);
        html += renderPlaceGroup('Quartiers', districtItems);

        if (results.length) {
            const label = type === 'lands' ? 'Terrains' : 'Logements';
            html += `<div class="search-suggestion-group"><span class="search-suggestion-label">${label}</span>`;
            results.forEach((item) => {
                html += `
                    <a href="${escapeHtml(item.url)}" class="search-suggestion-item">
                        <img src="${escapeHtml(item.image)}" alt="" loading="lazy">
                        <div class="search-suggestion-text">
                            <strong>${escapeHtml(item.title)}</strong>
                            <small>${escapeHtml(item.subtitle)}</small>
                        </div>
                        <span class="search-suggestion-badge">${escapeHtml(item.type_label)}</span>
                    </a>`;
            });
            html += '</div>';
        }

        panel.innerHTML = html;
        showSuggestions(panel, input);

        panel.querySelectorAll('.search-suggestion-place').forEach((btn) => {
            btn.addEventListener('click', () => {
                const city = btn.dataset.city || '';
                const district = btn.dataset.district || '';
                const query = btn.dataset.query || city;
                setFormCity(input, city);
                setFormDistrict(input, district);
                input.value = query;
                hideSuggestions(panel);
            });
        });
    };

    const fetchSuggestions = async (input, panel) => {
        const query = input.value.trim();
        if (query.length < 2) {
            hideSuggestions(panel);
            return;
        }
        const suggestUrl = input.dataset.suggestUrl || '';
        const url = suggestUrl.includes('://')
            ? new URL(suggestUrl)
            : new URL(suggestUrl, window.location.origin);
        url.searchParams.set('q', query);
        url.searchParams.set('type', input.dataset.suggestType || 'properties');
        const city = getFormCity(input);
        if (city) url.searchParams.set('city', city);

        try {
            const res = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            if (json.success && json.data) {
                renderSuggestions(panel, json.data, input, input.dataset.suggestType);
            }
        } catch {
            hideSuggestions(panel);
        }
    };

    const initAutocomplete = (input) => {
        if (input.dataset.suggestBound === '1') return;
        input.dataset.suggestBound = '1';

        const panelId = input.id === 'searchInputProperties'
            ? 'suggestionsProperties'
            : 'suggestionsLands';
        const panel = document.getElementById(panelId);
        if (!panel) return;

        const field = input.closest('.booking-field-dest');
        if (field) panel._portalField = field;
        const debouncedFetch = debounce(() => fetchSuggestions(input, panel), DEBOUNCE_MS);

        input.addEventListener('input', () => {
            if (input.value.trim().length < 2) {
                setFormCity(input, '');
                setFormDistrict(input, '');
            }
            debouncedFetch();
        });
        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2) debouncedFetch();
        });

        field?.addEventListener('click', (e) => {
            if (panel.contains(e.target)) return;
            if (e.target === input) return;
            input.focus();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hideSuggestions(panel);
        });

        document.addEventListener('click', (e) => {
            const inside = field?.contains(e.target) || panel.contains(e.target);
            if (!inside) hideSuggestions(panel);
        });

        window.addEventListener('resize', () => {
            if (!panel.hidden) positionSuggestionsPanel(input, panel);
        });
    };

    const closeGuestsPanel = (panel, hideOverlay = true) => {
        if (!panel) return;
        panel.classList.remove('is-open');
        (panel._portalField || panel.closest('.booking-field-guests'))?.classList.remove('is-open');
        if (isMobile()) window.BookingMobile?.restore(panel);
        setTimeout(() => {
            if (!panel.classList.contains('is-open')) {
                panel.hidden = true;
            }
        }, 280);

        if (hideOverlay && isMobile()) {
            const suggestionsOpen = document.querySelector('.search-suggestions:not([hidden])');
            const datesOpen = document.querySelector('.booking-datepicker-panel.is-open');
            if (!suggestionsOpen && !datesOpen) hideBackdrop();
        }
    };

    const openGuestsPanel = (panel) => {
        window.BookingDatepicker?.closeAll?.();
        document.querySelectorAll('.booking-guests-panel').forEach((p) => {
            if (p !== panel) closeGuestsPanel(p, false);
        });
        document.querySelectorAll('.search-suggestions').forEach(hideSuggestions);

        panel.hidden = false;
        const guestField = panel._portalField || panel.closest('.booking-field-guests');
        guestField?.classList.add('is-open');
        if (isMobile()) window.BookingMobile?.attachToBody(panel);
        requestAnimationFrame(() => panel.classList.add('is-open'));
        if (isMobile()) showBackdrop();
    };

    const getGuestField = (field, name) => field.querySelector(`#${name}InputProperties`) || field.querySelector(`input[name="${name}"]`);

    const readGuestCounts = (field) => {
        const adults = Math.max(1, parseInt(getGuestField(field, 'adults')?.value || '1', 10));
        const children = Math.max(0, parseInt(getGuestField(field, 'children')?.value || '0', 10));
        const rooms = Math.max(1, parseInt(getGuestField(field, 'rooms')?.value || '1', 10));
        const pets = field.querySelector('#petsInputProperties')?.checked || false;
        const business = field.querySelector('#businessInputProperties')?.checked || false;
        return { adults, children, rooms, pets, business };
    };

    const writeGuestCounts = (field, counts) => {
        const adultsInput = getGuestField(field, 'adults');
        const childrenInput = getGuestField(field, 'children');
        const roomsInput = getGuestField(field, 'rooms');
        const guestsInput = getGuestField(field, 'guests');
        if (adultsInput) adultsInput.value = String(counts.adults);
        if (childrenInput) childrenInput.value = String(counts.children);
        if (roomsInput) roomsInput.value = String(counts.rooms);
        if (guestsInput) guestsInput.value = String(counts.adults + counts.children);
        field.querySelectorAll('.stepper-count[data-field="adults"]').forEach((el) => {
            el.textContent = String(counts.adults);
        });
        field.querySelectorAll('.stepper-count[data-field="children"]').forEach((el) => {
            el.textContent = String(counts.children);
        });
        field.querySelectorAll('.stepper-count[data-field="rooms"]').forEach((el) => {
            el.textContent = String(counts.rooms);
        });
        const petsCheckbox = field.querySelector('#petsInputProperties');
        if (petsCheckbox) petsCheckbox.checked = counts.pets;
        const businessCheckbox = field.querySelector('#businessInputProperties');
        if (businessCheckbox) businessCheckbox.checked = counts.business;
    };

    const formatGuestLabel = ({ adults, children, rooms, pets }) => {
        const parts = [
            `${adults} adulte${adults > 1 ? 's' : ''}`,
            `${children} enfant${children > 1 ? 's' : ''}`,
            `${rooms} chambre${rooms > 1 ? 's' : ''}`,
        ];
        if (pets) parts.push('animaux');
        return parts.join(' · ');
    };

    const updateGuestsUI = (field) => {
        const counts = readGuestCounts(field);
        writeGuestCounts(field, counts);
        const trigger = field.querySelector('.booking-guests-trigger');
        if (trigger) trigger.textContent = formatGuestLabel(counts);

        const adultsMinus = field.querySelector('.booking-stepper[data-field="adults"] .stepper-minus');
        const childrenMinus = field.querySelector('.booking-stepper[data-field="children"] .stepper-minus');
        const roomsMinus = field.querySelector('.booking-stepper[data-field="rooms"] .stepper-minus');
        if (adultsMinus) adultsMinus.disabled = counts.adults <= 1;
        if (childrenMinus) childrenMinus.disabled = counts.children <= 0;
        if (roomsMinus) roomsMinus.disabled = counts.rooms <= 1;
    };

    const initGuestsPanels = () => {
        document.querySelectorAll('.booking-field-guests').forEach((field) => {
            const panel = field.querySelector('.booking-guests-panel');
            const trigger = field.querySelector('.booking-guests-trigger');
            if (!panel || !trigger) return;

            panel._portalField = field;

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                if (panel.classList.contains('is-open')) {
                    closeGuestsPanel(panel);
                } else {
                    openGuestsPanel(panel);
                }
            });

            field.querySelectorAll('.booking-stepper').forEach((stepper) => {
                const type = stepper.dataset.field;
                stepper.querySelector('.stepper-minus')?.addEventListener('click', () => {
                    const counts = readGuestCounts(field);
                    if (type === 'adults') counts.adults = Math.max(1, counts.adults - 1);
                    if (type === 'children') counts.children = Math.max(0, counts.children - 1);
                    if (type === 'rooms') counts.rooms = Math.max(1, counts.rooms - 1);
                    writeGuestCounts(field, counts);
                    updateGuestsUI(field);
                });
                stepper.querySelector('.stepper-plus')?.addEventListener('click', () => {
                    const counts = readGuestCounts(field);
                    if (type === 'adults') counts.adults = Math.min(16, counts.adults + 1);
                    if (type === 'children') counts.children = Math.min(10, counts.children + 1);
                    if (type === 'rooms') counts.rooms = Math.min(8, counts.rooms + 1);
                    writeGuestCounts(field, counts);
                    updateGuestsUI(field);
                });
            });

            field.querySelector('#petsInputProperties')?.addEventListener('change', () => {
                updateGuestsUI(field);
            });

            field.querySelector('#businessInputProperties')?.addEventListener('change', () => {
                updateGuestsUI(field);
            });

            field.addEventListener('click', (e) => {
                if (panel.contains(e.target)) return;
                e.stopPropagation();
                if (!panel.classList.contains('is-open')) openGuestsPanel(panel);
            });

            field.querySelector('.booking-guests-done')?.addEventListener('click', () => {
                closeGuestsPanel(panel);
            });

            updateGuestsUI(field);
        });

        document.addEventListener('click', (e) => {
            document.querySelectorAll('.booking-guests-panel.is-open').forEach((guestPanel) => {
                const guestField = guestPanel._portalField || guestPanel.closest('.booking-field-guests');
                const inside = guestField?.contains(e.target) || guestPanel.contains(e.target);
                if (!inside && isMobile()) closeGuestsPanel(guestPanel);
            });
        });

        backdrop?.addEventListener('click', () => {
            window.BookingDatepicker?.closeAll?.();
            document.querySelectorAll('.booking-guests-panel.is-open').forEach((p) => {
                closeGuestsPanel(p);
            });
            document.querySelectorAll('.search-suggestions:not([hidden])').forEach((p) => {
                hideSuggestions(p);
            });
        });
    };

    const boot = () => {
        document.querySelectorAll('.search-input-dynamic').forEach(initAutocomplete);
        initGuestsPanels();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
