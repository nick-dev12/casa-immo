'use strict';

window.BookingMobile = (() => {
    const MOBILE_BP = 767;

    const isMobile = () => window.innerWidth <= MOBILE_BP;

    const attachToBody = (el) => {
        if (!el || el.dataset.ported === '1') return;

        const placeholder = document.createComment('booking-mobile-portal');
        el.parentNode?.insertBefore(placeholder, el);
        el._portalPlaceholder = placeholder;
        document.body.appendChild(el);
        el.dataset.ported = '1';
        el.classList.add('is-ported');
    };

    const restore = (el) => {
        if (!el || el.dataset.ported !== '1' || !el._portalPlaceholder) return;

        el._portalPlaceholder.parentNode?.insertBefore(el, el._portalPlaceholder);
        el._portalPlaceholder.remove();
        delete el._portalPlaceholder;
        delete el.dataset.ported;
        el.classList.remove('is-ported');
    };

    const restoreAll = () => {
        document.querySelectorAll('[data-ported="1"]').forEach((el) => restore(el));
    };

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (!isMobile()) restoreAll();
        }, 120);
    });

    return { isMobile, attachToBody, restore, restoreAll, MOBILE_BP };
})();
