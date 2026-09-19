'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-auth-agency-geolocate]');
    if (!root) {
        return;
    }

    const authPanel = document.querySelector('[data-auth-panel-root]');
    const registerView = authPanel?.querySelector('[data-auth-view="register"]');
    const accountTypeFieldset = authPanel?.querySelector('[data-auth-account-type]');
    const addressInput = root.querySelector('[data-auth-locate-address]');
    const districtInput = root.querySelector('[data-auth-locate-district]');
    const countryInput = root.querySelector('[data-auth-locate-country]');
    const accuracyInput = root.querySelector('[data-auth-locate-accuracy]');
    const latInput = root.querySelector('[data-auth-locate-lat]');
    const lngInput = root.querySelector('[data-auth-locate-lng]');
    const statusEl = root.querySelector('[data-auth-locate-status]');
    const summaryEl = root.querySelector('[data-auth-locate-summary]');
    const citySelect = document.getElementById('auth-agency-city');

    if (!addressInput || !latInput || !lngInput) {
        return;
    }

    const summaryFields = {
        address: root.querySelector('[data-summary-address]'),
        district: root.querySelector('[data-summary-district]'),
        city: root.querySelector('[data-summary-city]'),
        country: root.querySelector('[data-summary-country]'),
        lat: root.querySelector('[data-summary-lat]'),
        lng: root.querySelector('[data-summary-lng]'),
        accuracy: root.querySelector('[data-summary-accuracy]'),
    };

    const messages = {
        permissionPrompt: root.dataset.msgPermissionPrompt
            || 'Acceptez l\'accès à votre position dans la fenêtre de votre navigateur.',
        loadingGps: root.dataset.msgLoadingGps || 'Activation du GPS…',
        loadingAddress: root.dataset.msgLoadingAddress || 'Récupération de l\'adresse…',
        success: root.dataset.msgSuccess || 'Position détectée.',
        denied: root.dataset.msgDenied || 'Autorisez la géolocalisation dans votre navigateur.',
        unavailable: root.dataset.msgUnavailable || 'Position indisponible. Réessayez.',
        timeout: root.dataset.msgTimeout || 'GPS trop lent. Réessayez près d\'une fenêtre.',
        failed: root.dataset.msgFailed || 'Impossible de récupérer l\'adresse.',
        insecure: root.dataset.msgInsecure || 'La géolocalisation nécessite une connexion sécurisée (HTTPS).',
        pending: root.dataset.msgPending || 'Position en cours de détection.',
        auto: root.dataset.msgAuto || 'Détection automatique de votre position…',
        empty: '—',
    };

    const primaryGeoOptions = {
        enableHighAccuracy: true,
        timeout: 30000,
        maximumAge: 0,
    };

    let captureInProgress = false;
    let captureSucceeded = latInput.value.trim() !== '' && lngInput.value.trim() !== '';

    const formatCoord = (value) => {
        const number = Number(value);
        if (!Number.isFinite(number)) {
            return messages.empty;
        }
        return number.toFixed(8);
    };

    const formatAccuracy = (value) => {
        const number = Number(value);
        if (!Number.isFinite(number) || number <= 0) {
            return messages.empty;
        }
        return Math.round(number) + ' m';
    };

    const setSummaryText = (element, value) => {
        if (!element) {
            return;
        }
        const text = value !== null && value !== undefined && String(value).trim() !== ''
            ? String(value).trim()
            : messages.empty;
        element.textContent = text;
    };

    const setStatus = (text, type) => {
        if (!statusEl) {
            return;
        }
        if (!text) {
            statusEl.hidden = true;
            statusEl.textContent = '';
            statusEl.className = 'auth-geolocate-status';
            return;
        }
        statusEl.hidden = false;
        statusEl.textContent = text;
        statusEl.className = 'auth-geolocate-status auth-geolocate-status-' + (type || 'info');
    };

    const setLoading = (loading) => {
        captureInProgress = loading;
        root.classList.toggle('is-loading', loading);
        root.setAttribute('aria-busy', loading ? 'true' : 'false');
    };

    const isAgencyRegistrationActive = () => root !== null;

    const selectCityOption = (city) => {
        if (!citySelect || !city) {
            return city;
        }
        const options = Array.from(citySelect.options);
        const match = options.find((opt) => opt.value.toLowerCase() === city.toLowerCase());
        if (match) {
            citySelect.value = match.value;
            return match.value;
        }
        return city;
    };

    const updateSummary = (data) => {
        const latitude = formatCoord(data.latitude);
        const longitude = formatCoord(data.longitude);
        const city = data.city ? selectCityOption(data.city) : (citySelect?.value || messages.empty);

        setSummaryText(summaryFields.address, data.fullAddress || data.address);
        setSummaryText(summaryFields.district, data.district);
        setSummaryText(summaryFields.city, city);
        setSummaryText(summaryFields.country, data.country);
        setSummaryText(summaryFields.lat, latitude);
        setSummaryText(summaryFields.lng, longitude);
        setSummaryText(summaryFields.accuracy, formatAccuracy(data.accuracy));

        if (summaryEl) {
            summaryEl.hidden = latitude === messages.empty || longitude === messages.empty;
        }
    };

    const geolocationError = (error) => {
        const code = error && typeof error.code === 'number' ? error.code : 0;
        if (code === 1) {
            return messages.denied;
        }
        if (code === 3) {
            return messages.timeout;
        }
        if (code === 2) {
            return messages.unavailable;
        }
        return messages.unavailable;
    };

    const getPositionWithWatch = (options, maxWaitMs) => new Promise((resolve, reject) => {
        let settled = false;
        let watchId = null;

        const finish = (fn, value) => {
            if (settled) {
                return;
            }
            settled = true;
            clearTimeout(timer);
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
            }
            fn(value);
        };

        const timer = setTimeout(() => {
            finish(reject, { code: 3, message: 'timeout' });
        }, maxWaitMs);

        watchId = navigator.geolocation.watchPosition(
            (position) => finish(resolve, position),
            (error) => {
                if (error.code === 1) {
                    finish(reject, error);
                }
            },
            options
        );
    });

    const reverseGeocode = async (latitude, longitude) => {
        const geocodeUrl = root.dataset.geocodeUrl || '/api/geocode/reverse';
        const url = geocodeUrl.includes('://')
            ? new URL(geocodeUrl)
            : new URL(geocodeUrl, window.location.origin);
        url.searchParams.set('lat', String(latitude));
        url.searchParams.set('lng', String(longitude));

        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 20000);

        try {
            const res = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            const json = await res.json();
            if (!json.success || !json.data) {
                throw new Error(json.message || messages.failed);
            }
            return json.data;
        } finally {
            clearTimeout(timer);
        }
    };

    const persistLocation = (data) => {
        const latitude = Number(data.latitude);
        const longitude = Number(data.longitude);
        const shortParts = [data.street, data.district, data.city, data.country]
            .map((value) => (value || '').trim())
            .filter(Boolean);
        const shortAddress = shortParts.length > 0
            ? shortParts.join(', ')
            : (data.full_address || data.address || (formatCoord(latitude) + ', ' + formatCoord(longitude)));
        const fullAddress = data.full_address || data.fullAddress || data.address || shortAddress;

        latInput.value = String(latitude);
        lngInput.value = String(longitude);
        addressInput.value = shortAddress;

        if (districtInput) {
            districtInput.value = data.district || '';
        }
        if (countryInput) {
            countryInput.value = data.country || '';
        }
        if (accuracyInput) {
            accuracyInput.value = data.accuracy != null && data.accuracy !== '' ? String(data.accuracy) : '';
        }

        updateSummary({
            fullAddress,
            address: shortAddress,
            district: data.district || '',
            city: data.city || '',
            country: data.country || '',
            latitude,
            longitude,
            accuracy: data.accuracy,
        });

        root.classList.add('is-captured');
        captureSucceeded = true;
    };

    const applyLocation = async (latitude, longitude, accuracy) => {
        latInput.value = String(latitude);
        lngInput.value = String(longitude);

        try {
            setStatus('');
            const data = await reverseGeocode(latitude, longitude);
            persistLocation({
                fullAddress: data.full_address || data.address,
                address: data.address,
                district: data.district || '',
                city: data.city || '',
                country: data.country || '',
                latitude: data.latitude != null ? data.latitude : latitude,
                longitude: data.longitude != null ? data.longitude : longitude,
                accuracy,
            });
            setStatus('');
        } catch (err) {
            const fallbackAddress = formatCoord(latitude) + ', ' + formatCoord(longitude);
            persistLocation({
                fullAddress: fallbackAddress,
                address: fallbackAddress,
                district: '',
                city: '',
                country: '',
                latitude,
                longitude,
                accuracy,
            });
            setStatus('');
        }
    };

    const onPositionSuccess = (position) => {
        applyLocation(
            position.coords.latitude,
            position.coords.longitude,
            position.coords.accuracy
        ).finally(() => setLoading(false));
    };

    const onPositionError = (error) => {
        if (error.code === 1) {
            setStatus(geolocationError(error), 'error');
            setLoading(false);
            return;
        }

        setStatus('');
        getPositionWithWatch({ enableHighAccuracy: true, maximumAge: 0 }, 45000)
            .then(onPositionSuccess)
            .catch((watchError) => {
                setStatus(geolocationError(watchError.code === 1 ? watchError : error), 'error');
                setLoading(false);
            });
    };

    const capturePosition = (force = false) => {
        if (!isAgencyRegistrationActive()) {
            return;
        }

        if (captureInProgress) {
            return;
        }

        if (captureSucceeded && !force) {
            return;
        }

        if (!navigator.geolocation) {
            setStatus(messages.unavailable, 'error');
            return;
        }

        if (!window.isSecureContext) {
            setStatus(messages.insecure, 'error');
            return;
        }

        setLoading(true);
        setStatus('');

        navigator.geolocation.getCurrentPosition(
            onPositionSuccess,
            onPositionError,
            primaryGeoOptions
        );
    };

    const autoCaptureIfNeeded = () => {
        if (!isAgencyRegistrationActive()) {
            return;
        }

        if (captureSucceeded) {
            return;
        }

        capturePosition();
    };

    if (latInput.value.trim() !== '' && lngInput.value.trim() !== '') {
        updateSummary({
            fullAddress: addressInput.value,
            address: addressInput.value,
            district: districtInput?.value || '',
            city: citySelect?.value || '',
            country: countryInput?.value || '',
            latitude: latInput.value,
            longitude: lngInput.value,
            accuracy: accuracyInput?.value || '',
        });
        root.classList.add('is-captured');
    } else {
        autoCaptureIfNeeded();
    }

    authPanel?.addEventListener('auth:register-view', autoCaptureIfNeeded);
    authPanel?.addEventListener('auth:account-type-change', autoCaptureIfNeeded);

    const registerForm = document.getElementById('authRegisterForm');
    registerForm?.addEventListener('submit', (event) => {
        if (latInput.value.trim() === '' || lngInput.value.trim() === '') {
            event.preventDefault();
            setStatus(messages.pending, 'error');
            capturePosition(true);
        }
    });
});
