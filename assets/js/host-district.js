'use strict';

(function () {
    const DEBOUNCE_MS = 200;
    const input = document.getElementById('hostDistrict');
    const panel = document.getElementById('hostDistrictSuggestions');
    const citySelect = document.getElementById('host-city');

    if (!input || !panel) {
        return;
    }

    const debounce = (fn, ms) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), ms);
        };
    };

    const escapeHtml = (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    const getCity = () => citySelect?.value?.trim() || '';

    const hideSuggestions = () => {
        panel.hidden = true;
        panel.innerHTML = '';
        input.closest('.host-autocomplete')?.classList.remove('is-open');
    };

    const showSuggestions = () => {
        panel.hidden = false;
        input.closest('.host-autocomplete')?.classList.add('is-open');
    };

    const renderDistricts = (districts) => {
        if (!districts.length) {
            panel.innerHTML = '<div class="search-suggestion-empty">Aucun quartier trouvé en Casamance</div>';
            showSuggestions();
            return;
        }

        let html = '<div class="search-suggestion-group"><span class="search-suggestion-label">Quartiers</span>';
        districts.forEach((place) => {
            html += `
                <button type="button" class="search-suggestion-item search-suggestion-place"
                        data-district="${escapeHtml(place.name)}">
                    <span class="search-suggestion-icon"><i class="bi bi-geo-alt-fill"></i></span>
                    <div class="search-suggestion-text">
                        <strong>${escapeHtml(place.name)}</strong>
                        <small>${escapeHtml(place.subtitle || place.city + ', Casamance')}</small>
                    </div>
                </button>`;
        });
        html += '</div>';

        panel.innerHTML = html;
        showSuggestions();

        panel.querySelectorAll('.search-suggestion-place').forEach((btn) => {
            btn.addEventListener('click', () => {
                input.value = btn.dataset.district || '';
                hideSuggestions();
            });
        });
    };

    const fetchDistricts = async () => {
        const query = input.value.trim();
        const city = getCity();

        if (query === '' && city === '') {
            hideSuggestions();
            return;
        }

        const suggestUrl = input.dataset.suggestUrl || '/api/search/suggest';
        const url = suggestUrl.includes('://')
            ? new URL(suggestUrl)
            : new URL(suggestUrl, window.location.origin);
        url.searchParams.set('scope', 'districts');
        url.searchParams.set('q', query);
        if (city) {
            url.searchParams.set('city', city);
        }

        try {
            const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (json.success && json.data?.districts) {
                renderDistricts(json.data.districts);
            } else {
                hideSuggestions();
            }
        } catch {
            hideSuggestions();
        }
    };

    const debouncedFetch = debounce(fetchDistricts, DEBOUNCE_MS);

    input.addEventListener('input', debouncedFetch);
    input.addEventListener('focus', fetchDistricts);

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    citySelect?.addEventListener('change', () => {
        if (document.activeElement === input || !panel.hidden) {
            fetchDistricts();
        }
    });

    document.addEventListener('click', (e) => {
        const wrap = input.closest('.host-autocomplete');
        if (!wrap?.contains(e.target)) {
            hideSuggestions();
        }
    });
})();
