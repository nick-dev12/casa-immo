'use strict';

(function () {
    const root = document.querySelector('[data-home-search-scripts]');
    if (!root) {
        return;
    }

    const urls = (root.dataset.homeSearchScripts || '').split('|').filter(Boolean);
    if (urls.length === 0) {
        return;
    }

    let loading = false;
    let loaded = false;

    const loadScript = (url) => new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${url}"]`)) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = url;
        script.async = false;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load ' + url));
        document.body.appendChild(script);
    });

    const load = async () => {
        if (loaded || loading) {
            return loaded ? Promise.resolve() : new Promise((resolve) => {
                const wait = () => {
                    if (loaded) resolve();
                    else requestAnimationFrame(wait);
                };
                wait();
            });
        }

        loading = true;
        try {
            for (const url of urls) {
                await loadScript(url);
            }
            loaded = true;

            const active = document.activeElement;
            if (active && active.classList.contains('search-input-dynamic')) {
                active.dispatchEvent(new Event('input', { bubbles: true }));
            }
        } finally {
            loading = false;
        }
    };

    const shouldLoad = (target) =>
        target?.closest?.('.booking-search-form, .search-tab, [data-date-trigger], #bookingMobileMenuBtn');

    document.addEventListener('focusin', (event) => {
        if (shouldLoad(event.target)) {
            load();
        }
    }, true);

    document.addEventListener('click', (event) => {
        if (shouldLoad(event.target)) {
            load();
        }
    }, true);

    document.addEventListener('input', (event) => {
        if (event.target?.classList?.contains('search-input-dynamic')) {
            load();
        }
    }, true);

    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => load(), { timeout: 2500 });
    } else {
        window.addEventListener('load', () => setTimeout(load, 400), { once: true });
    }
})();
