'use strict';

(function () {
    const gallery = document.getElementById('hostListingGallery');
    const main = document.getElementById('hostGalleryMain');
    if (!gallery || !main) {
        return;
    }

    gallery.querySelectorAll('.host-listing-gallery-thumb').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const src = thumb.getAttribute('data-gallery-src');
            if (!src) {
                return;
            }

            main.src = src;
            gallery.querySelectorAll('.host-listing-gallery-thumb').forEach((el) => {
                const active = el === thumb;
                el.classList.toggle('is-active', active);
                el.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        });
    });
})();
