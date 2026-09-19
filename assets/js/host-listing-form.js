'use strict';

(function () {
    const form = document.querySelector('.host-form-simple');
    if (!form) return;

    const listingKindInputs = form.querySelectorAll('input[name="listing_kind"]');
    const rentalPeriodInputs = form.querySelectorAll('input[name="rental_period"]');
    const propertySections = form.querySelectorAll('[data-listing-section="logement"]');
    const landSections = form.querySelectorAll('[data-listing-section="terrain"]');
    const priceLabel = document.getElementById('hostPriceLabel');
    const priceInput = document.getElementById('hostPrice');
    const priceHint = document.getElementById('hostPriceHint');

    const labels = {
        nightly: priceLabel?.dataset.labelNight || 'Prix / nuit',
        monthly: priceLabel?.dataset.labelMonth || 'Prix / mois',
        land: priceLabel?.dataset.labelLand || 'Prix de vente',
    };

    const hints = {
        nightly: priceHint?.dataset.hintNight || '',
        monthly: priceHint?.dataset.hintMonth || '',
        land: priceHint?.dataset.hintLand || '',
    };

    const getListingKind = () => {
        const checked = form.querySelector('input[name="listing_kind"]:checked');
        return checked ? checked.value : 'logement';
    };

    const getRentalPeriod = () => {
        const checked = form.querySelector('input[name="rental_period"]:checked');
        return checked ? checked.value : 'nightly';
    };

    const setSectionVisible = (sections, visible) => {
        sections.forEach((section) => {
            section.hidden = !visible;
            const radioGroups = new Set();

            section.querySelectorAll('input, select, textarea, button').forEach((field) => {
                if (field.id === 'hostLocateBtn') return;
                field.disabled = !visible;

                if (field.type === 'radio' && field.dataset.requiredWhenVisible === '1') {
                    radioGroups.add(field.name);
                    return;
                }

                if (field.type === 'radio' || field.type === 'checkbox') return;

                field.required = visible && field.dataset.requiredWhenVisible === '1';
            });

            radioGroups.forEach((groupName) => {
                const radios = section.querySelectorAll(`input[type="radio"][name="${groupName}"]`);
                radios.forEach((radio, index) => {
                    radio.required = visible && index === 0;
                });
            });
        });
    };

    const updatePriceField = () => {
        if (!priceLabel || !priceInput) return;

        const kind = getListingKind();
        priceInput.disabled = false;
        priceInput.required = true;

        if (kind === 'terrain') {
            priceLabel.textContent = labels.land;
            if (priceHint) priceHint.textContent = hints.land;
            priceInput.placeholder = '5000000';
            priceInput.name = 'land_price';
            return;
        }

        priceInput.name = 'listing_price';
        const period = getRentalPeriod();
        if (period === 'monthly') {
            priceLabel.textContent = labels.monthly;
            if (priceHint) priceHint.textContent = hints.monthly;
            priceInput.placeholder = '350000';
        } else {
            priceLabel.textContent = labels.nightly;
            if (priceHint) priceHint.textContent = hints.nightly;
            priceInput.placeholder = '25000';
        }
    };

    const syncForm = (options = {}) => {
        const kind = getListingKind();
        const isLand = kind === 'terrain';
        const wasLand = form.dataset.listingKind === 'terrain';

        setSectionVisible(propertySections, !isLand);
        setSectionVisible(landSections, isLand);
        updatePriceField();

        form.dataset.listingKind = kind;
        syncLandAreaFromDimensions();

        if (options.scrollToLand && isLand && !wasLand) {
            const terrainBlock = form.querySelector('.host-basics-block[data-listing-section="terrain"]');
            terrainBlock?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    listingKindInputs.forEach((input) => {
        input.addEventListener('change', () => syncForm({ scrollToLand: true }));
    });

    rentalPeriodInputs.forEach((input) => {
        input.addEventListener('change', updatePriceField);
    });

    const landLengthInput = document.getElementById('hostLandLength');
    const landWidthInput = document.getElementById('hostLandWidth');
    const landAreaInput = document.getElementById('hostLandArea');
    const landAreaUnit = document.getElementById('hostLandAreaUnit');
    const landAreaHint = document.getElementById('hostLandAreaHint');
    const landPlanPreview = document.querySelector('.land-plan-preview[data-land-plan="live"]');

    const unitLabels = {
        m2: 'm²',
        ha: 'ha',
        are: 'are',
    };

    const formatArea = (value) => {
        if (!Number.isFinite(value)) {
            return '';
        }
        return String(Math.round(value * 100) / 100).replace('.', ',');
    };

    const convertAreaFromM2 = (squareMeters, unit) => {
        if (unit === 'ha') {
            return squareMeters / 10000;
        }
        if (unit === 'are') {
            return squareMeters / 100;
        }
        return squareMeters;
    };

    const unitLabelForHint = (unit) => unitLabels[unit] || unit;

    const syncLandAreaFromDimensions = () => {
        if (!landLengthInput || !landWidthInput || !landAreaInput || !landAreaUnit) {
            return;
        }

        const length = parseFloat(landLengthInput.value || '0');
        const width = parseFloat(landWidthInput.value || '0');
        const unit = landAreaUnit.value || 'm2';

        if (length <= 0 || width <= 0) {
            landAreaInput.readOnly = false;
            landAreaInput.classList.remove('is-auto');
            if (landAreaHint) {
                landAreaHint.hidden = true;
                landAreaHint.textContent = '';
            }
            if (typeof globalThis.syncLiveLandPlan === 'function' && landPlanPreview) {
                globalThis.syncLiveLandPlan(landPlanPreview);
            } else if (typeof globalThis.renderLandPlan === 'function' && landPlanPreview) {
                globalThis.renderLandPlan(landPlanPreview, { length, width, area: 0, unit });
            }
            return;
        }

        const squareMeters = length * width;
        const computed = convertAreaFromM2(squareMeters, unit);
        const rounded = Math.round(computed * 100) / 100;

        landAreaInput.value = String(rounded);
        landAreaInput.readOnly = true;
        landAreaInput.classList.add('is-auto');

        if (landAreaHint) {
            const template = landAreaHint.dataset.template || 'Surface calculée automatiquement : :area';
            const displayUnit = unitLabelForHint(unit);
            landAreaHint.textContent = `${template.replace(':area', formatArea(rounded))} ${displayUnit}`;
            landAreaHint.hidden = false;
        }

        if (typeof globalThis.syncLiveLandPlan === 'function' && landPlanPreview) {
            globalThis.syncLiveLandPlan(landPlanPreview);
        } else if (typeof globalThis.renderLandPlan === 'function' && landPlanPreview) {
            globalThis.renderLandPlan(landPlanPreview, {
                length,
                width,
                area: rounded,
                unit,
            });
        }
    };

    [landLengthInput, landWidthInput].forEach((field) => {
        if (!field) return;
        field.addEventListener('input', syncLandAreaFromDimensions);
        field.addEventListener('change', syncLandAreaFromDimensions);
    });

    if (landAreaUnit) {
        landAreaUnit.addEventListener('change', syncLandAreaFromDimensions);
    }

    if (landAreaInput) {
        landAreaInput.addEventListener('input', () => {
            if (!landAreaInput.readOnly) {
                if (landAreaHint) {
                    landAreaHint.hidden = true;
                    landAreaHint.textContent = '';
                }
            }
        });
    }

    syncForm();
})();
