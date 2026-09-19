'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const gallery = window.propertyGallery || [];
    const shareData = window.propertyShare || { title: document.title, url: window.location.href, text: '' };
    const modal = document.getElementById('galleryModal');
    const modalImage = document.getElementById('galleryModalImage');
    const counter = document.getElementById('galleryCounter');
    const showAllBtn = document.getElementById('showAllPhotos');
    const mobileMainImage = document.getElementById('mobileMainImage');
    const mobileMainPhoto = document.getElementById('mobileMainPhoto');
    const mobileThumbs = document.getElementById('mobileThumbs');
    const bookingSheet = document.getElementById('bookingSheet');
    const openBookingBtn = document.getElementById('openBookingSheet');
    const closeBookingBtn = document.getElementById('closeBookingSheet');
    const bookingBackdrop = document.getElementById('bookingSheetBackdrop');
    const shareSheet = document.getElementById('shareSheet');
    const shareBackdrop = document.getElementById('shareSheetBackdrop');
    const closeShareBtn = document.getElementById('closeShareSheet');
    const copyLinkBtn = document.getElementById('copyPropertyLink');
    const copyLinkSmBtn = document.getElementById('copyPropertyLinkSm');
    const nativeShareBtn = document.getElementById('nativeShareBtn');
    const shareFeedback = document.getElementById('shareFeedback');
    const shareUrlInput = document.getElementById('shareUrlInput');
    let currentIndex = 0;
    let mobileIndex = 0;

    const setMobilePhoto = (index) => {
        if (!gallery[index]) return;
        mobileIndex = index;
        if (mobileMainImage) {
            mobileMainImage.src = gallery[index];
        }
        if (mobileMainPhoto) {
            mobileMainPhoto.dataset.index = String(index);
            mobileMainPhoto.dataset.src = gallery[index];
        }
        mobileThumbs?.querySelectorAll('.pd-mobile-thumb').forEach((thumb) => {
            const isMore = thumb.classList.contains('pd-mobile-thumb-more');
            const thumbIndex = parseInt(thumb.dataset.index || '0', 10);
            thumb.classList.toggle('active', !isMore && thumbIndex === index);
        });
    };

    /* ── Galerie modal ── */
    const openModal = (index) => {
        if (!modal || !modalImage || gallery.length === 0) return;
        currentIndex = index;
        modalImage.src = gallery[currentIndex];
        if (counter) {
            counter.textContent = `${currentIndex + 1} / ${gallery.length}`;
        }
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        if (!modal) return;
        modal.hidden = true;
        document.body.style.overflow = '';
    };

    const showNext = (dir) => {
        if (gallery.length === 0) return;
        currentIndex = (currentIndex + dir + gallery.length) % gallery.length;
        modalImage.src = gallery[currentIndex];
        if (counter) {
            counter.textContent = `${currentIndex + 1} / ${gallery.length}`;
        }
    };

    document.querySelectorAll('.gallery-item, .gallery-hidden').forEach((item) => {
        item.addEventListener('click', () => {
            const index = parseInt(item.dataset.index || '0', 10);
            if (item.classList.contains('pd-mobile-thumb-more')) {
                openModal(index);
                return;
            }
            if (item.classList.contains('pd-mobile-thumb')) {
                setMobilePhoto(index);
                return;
            }
            openModal(index);
        });
    });

    mobileMainPhoto?.addEventListener('click', () => openModal(mobileIndex));

    showAllBtn?.addEventListener('click', () => openModal(0));

    modal?.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.getElementById('galleryPrev')?.addEventListener('click', () => showNext(-1));
    document.getElementById('galleryNext')?.addEventListener('click', () => showNext(1));

    document.addEventListener('keydown', (e) => {
        if (!modal?.hidden) {
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') showNext(-1);
            if (e.key === 'ArrowRight') showNext(1);
        }
        if (!shareSheet?.hidden && e.key === 'Escape') {
            closeShareSheet();
        }
    });

    /* ── Booking sheet mobile ── */
    const openSheet = () => {
        if (!bookingSheet) return;
        bookingSheet.hidden = false;
        bookingSheet.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeSheet = () => {
        if (!bookingSheet) return;
        bookingSheet.hidden = true;
        bookingSheet.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    openBookingBtn?.addEventListener('click', openSheet);
    closeBookingBtn?.addEventListener('click', closeSheet);
    bookingBackdrop?.addEventListener('click', closeSheet);

    /* ── Dates min ── */
    const today = new Date().toISOString().split('T')[0];
    document.querySelectorAll('input[name="check_in"]').forEach((input) => {
        input.min = today;
        input.addEventListener('change', () => {
            const checkOut = input.closest('form')?.querySelector('input[name="check_out"]');
            if (checkOut && input.value) {
                checkOut.min = input.value;
            }
        });
    });

    /* ── Partager ── */
    const openShareSheet = () => {
        if (!shareSheet) return;
        shareSheet.hidden = false;
        shareSheet.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (shareFeedback) {
            shareFeedback.hidden = true;
        }
    };

    const closeShareSheet = () => {
        if (!shareSheet) return;
        shareSheet.hidden = true;
        shareSheet.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    const showShareFeedback = () => {
        if (!shareFeedback) return;
        shareFeedback.hidden = false;
        setTimeout(() => {
            shareFeedback.hidden = true;
        }, 2500);
    };

    const copyShareLink = async () => {
        const url = shareUrlInput?.value || shareData.url;

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(url);
            } else if (shareUrlInput) {
                shareUrlInput.select();
                document.execCommand('copy');
            }
            showShareFeedback();
        } catch {
            /* silencieux */
        }
    };

    document.querySelectorAll('.js-share-property').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const title = btn.dataset.shareTitle || shareData.title;
            const text = btn.dataset.shareText || shareData.text;
            const url = btn.dataset.shareUrl || shareData.url;

            if (navigator.share) {
                try {
                    await navigator.share({ title, text, url });
                    return;
                } catch (err) {
                    if (err && err.name === 'AbortError') {
                        return;
                    }
                }
            }

            openShareSheet();
        });
    });

    closeShareBtn?.addEventListener('click', closeShareSheet);
    shareBackdrop?.addEventListener('click', closeShareSheet);
    copyLinkBtn?.addEventListener('click', copyShareLink);
    copyLinkSmBtn?.addEventListener('click', copyShareLink);

    nativeShareBtn?.addEventListener('click', async () => {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: shareData.title,
                    text: shareData.text,
                    url: shareData.url,
                });
                closeShareSheet();
            } catch {
                /* annulé */
            }
        } else {
            copyShareLink();
        }
    });

    if (nativeShareBtn && !navigator.share) {
        nativeShareBtn.style.display = 'none';
    }

    /* ── Avis : voir plus ── */
    const reviewsPanel = document.getElementById('reviewsPanel');
    const openReviewsBtn = document.getElementById('openReviewsPanel');
    const closeReviewsBtn = document.getElementById('closeReviewsPanel');
    const reviewsBackdrop = document.getElementById('reviewsPanelBackdrop');

    const openReviewsPanel = () => {
        if (!reviewsPanel) return;
        reviewsPanel.hidden = false;
        reviewsPanel.setAttribute('aria-hidden', 'false');
    };

    const closeReviewsPanel = () => {
        if (!reviewsPanel) return;
        reviewsPanel.hidden = true;
        reviewsPanel.setAttribute('aria-hidden', 'true');
    };

    openReviewsBtn?.addEventListener('click', openReviewsPanel);
    closeReviewsBtn?.addEventListener('click', closeReviewsPanel);
    reviewsBackdrop?.addEventListener('click', closeReviewsPanel);

    /* ── Équipements : voir plus ── */
    const amenitiesPanel = document.getElementById('amenitiesPanel');
    const openAmenitiesBtn = document.getElementById('openAmenitiesPanel');
    const closeAmenitiesBtn = document.getElementById('closeAmenitiesPanel');
    const amenitiesBackdrop = document.getElementById('amenitiesPanelBackdrop');

    const openAmenitiesPanel = () => {
        if (!amenitiesPanel) return;
        amenitiesPanel.hidden = false;
        amenitiesPanel.setAttribute('aria-hidden', 'false');
    };

    const closeAmenitiesPanel = () => {
        if (!amenitiesPanel) return;
        amenitiesPanel.hidden = true;
        amenitiesPanel.setAttribute('aria-hidden', 'true');
    };

    openAmenitiesBtn?.addEventListener('click', openAmenitiesPanel);
    closeAmenitiesBtn?.addEventListener('click', closeAmenitiesPanel);
    amenitiesBackdrop?.addEventListener('click', closeAmenitiesPanel);

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (reviewsPanel && !reviewsPanel.hidden) closeReviewsPanel();
        if (amenitiesPanel && !amenitiesPanel.hidden) closeAmenitiesPanel();
    });
});
