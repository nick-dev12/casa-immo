'use strict';

(function () {
    const btn = document.getElementById('hostLocateBtn');
    const addressInput = document.getElementById('hostAddress');
    const latInput = document.getElementById('hostLatitude');
    const lngInput = document.getElementById('hostLongitude');
    const statusEl = document.getElementById('hostLocateStatus');
    const citySelect = document.getElementById('host-city');
    const districtInput = document.getElementById('hostDistrict');

    if (!btn || !addressInput || !latInput || !lngInput) {
        return;
    }

    const messages = {
        permissionPrompt: btn.dataset.msgPermissionPrompt
            || 'Acceptez l\'accès à votre position dans la fenêtre de votre navigateur.',
        loadingGps: btn.dataset.msgLoadingGps || 'Activation du GPS…',
        loadingAddress: btn.dataset.msgLoadingAddress || 'Récupération de l\'adresse…',
        success: btn.dataset.msgSuccess || 'Position détectée.',
        denied: btn.dataset.msgDenied || 'Autorisez la géolocalisation dans votre navigateur.',
        unavailable: btn.dataset.msgUnavailable || 'Position indisponible. Réessayez ou saisissez l\'adresse.',
        timeout: btn.dataset.msgTimeout || 'GPS trop lent. Réessayez près d\'une fenêtre ou saisissez l\'adresse.',
        failed: btn.dataset.msgFailed || 'Impossible de récupérer l\'adresse.',
        insecure: btn.dataset.msgInsecure || 'La géolocalisation nécessite une connexion sécurisée (HTTPS).',
        auto: btn.dataset.msgAuto || 'Détection automatique de la position…',
    };

    const primaryGeoOptions = {
        enableHighAccuracy: true,
        timeout: 30000,
        maximumAge: 0,
    };

    let captureInProgress = false;
    let captureSucceeded = latInput.value.trim() !== '' && lngInput.value.trim() !== '';

    const setStatus = (text, type) => {
        if (!statusEl) {
            return;
        }
        if (!text) {
            statusEl.hidden = true;
            statusEl.textContent = '';
            statusEl.className = 'host-locate-status';
            return;
        }
        statusEl.hidden = false;
        statusEl.textContent = text;
        statusEl.className = 'host-locate-status host-locate-status-' + (type || 'info');
    };

    const setLoading = (loading) => {
        btn.disabled = loading;
        btn.classList.toggle('is-loading', loading);
        btn.setAttribute('aria-busy', loading ? 'true' : 'false');
    };

    const selectCityOption = (city) => {
        if (!citySelect || !city) {
            return;
        }
        const options = Array.from(citySelect.options);
        const match = options.find((opt) => opt.value.toLowerCase() === city.toLowerCase());
        if (match) {
            citySelect.value = match.value;
            citySelect.dispatchEvent(new Event('change', { bubbles: true }));
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
        const geocodeUrl = btn.dataset.geocodeUrl || '/api/geocode/reverse';
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

    const applyLocation = async (latitude, longitude) => {
        latInput.value = String(latitude);
        lngInput.value = String(longitude);
        captureSucceeded = true;

        try {
            setStatus(messages.loadingAddress, 'info');
            const data = await reverseGeocode(latitude, longitude);
            const addressParts = [data.street, data.district, data.city, data.country]
                .map((value) => (value || '').trim())
                .filter(Boolean);
            addressInput.value = addressParts.length > 0
                ? addressParts.join(', ')
                : (data.full_address || data.address || addressInput.value);

            if (data.latitude != null) {
                latInput.value = String(data.latitude);
            }
            if (data.longitude != null) {
                lngInput.value = String(data.longitude);
            }

            if (data.city) {
                selectCityOption(data.city);
            }
            if (data.district && districtInput && districtInput.value.trim() === '') {
                districtInput.value = data.district;
            }

            setStatus(messages.success, 'success');
        } catch (err) {
            if (!addressInput.value.trim()) {
                addressInput.value = latitude.toFixed(6) + ', ' + longitude.toFixed(6);
            }
            setStatus(
                latitude && longitude
                    ? (messages.success + ' ' + (err.message || messages.failed))
                    : (err.message || messages.failed),
                latitude && longitude ? 'success' : 'error'
            );
        }
    };

    const onPositionSuccess = (position) => {
        applyLocation(position.coords.latitude, position.coords.longitude)
            .finally(() => {
                setLoading(false);
                captureInProgress = false;
            });
    };

    const onPositionError = (error) => {
        if (error.code === 1) {
            setStatus(geolocationError(error), 'error');
            setLoading(false);
            captureInProgress = false;
            return;
        }

        setStatus(messages.loadingGps, 'info');
        getPositionWithWatch({ enableHighAccuracy: true, maximumAge: 0 }, 45000)
            .then(onPositionSuccess)
            .catch((watchError) => {
                setStatus(geolocationError(watchError.code === 1 ? watchError : error), 'error');
                setLoading(false);
                captureInProgress = false;
            });
    };

    const capturePosition = (options = {}) => {
        const { force = false, silent = false } = options;

        if (captureInProgress) {
            return;
        }

        if (captureSucceeded && !force) {
            return;
        }

        if (!navigator.geolocation) {
            if (!silent) {
                setStatus(messages.unavailable, 'error');
            }
            return;
        }

        if (!window.isSecureContext) {
            if (!silent) {
                setStatus(messages.insecure, 'error');
            }
            return;
        }

        captureInProgress = true;
        setLoading(true);

        if (silent) {
            setStatus('');
        } else if (force) {
            setStatus(messages.permissionPrompt, 'info');
        } else {
            setStatus(messages.auto, 'info');
        }

        navigator.geolocation.getCurrentPosition(
            onPositionSuccess,
            onPositionError,
            primaryGeoOptions
        );
    };

    btn.addEventListener('click', () => {
        capturePosition({ force: true });
    });

    if (!captureSucceeded) {
        capturePosition({ silent: true });
    }
})();
