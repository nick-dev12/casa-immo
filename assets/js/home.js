'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const siteHeader = document.getElementById('bookingSiteHeader');
    const syncHeaderScroll = () => {
        if (!siteHeader) {
            return;
        }
        siteHeader.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    syncHeaderScroll();
    window.addEventListener('scroll', syncHeaderScroll, { passive: true });

    const tabs = document.querySelectorAll('.search-tab');
    const formLogements = document.querySelector('.search-form-logements');
    const formTerrains = document.querySelector('.search-form-terrains');

    const updateCityCardLinks = (mode) => {
        document.querySelectorAll('.casamance-city-card[data-href-logements][data-href-terrains]').forEach((card) => {
            const href = mode === 'terrains' ? card.dataset.hrefTerrains : card.dataset.hrefLogements;
            if (href) {
                card.setAttribute('href', href);
            }
        });
    };

    const syncSearchFields = (mode) => {
        const propQ = document.getElementById('searchInputProperties');
        const landQ = document.getElementById('searchInputLands');
        const propCity = document.getElementById('searchCityProperties');
        const landCity = document.getElementById('searchCityLands');
        const propDistrict = document.getElementById('searchDistrictProperties');
        const landDistrict = document.getElementById('searchDistrictLands');

        if (mode === 'terrains' && landQ && propQ && landQ.value.trim() === '') {
            landQ.value = propQ.value;
        }
        if (mode === 'logements' && propQ && landQ && propQ.value.trim() === '') {
            propQ.value = landQ.value;
        }
        if (mode === 'terrains' && landCity && propCity && landCity.value === '') {
            landCity.value = propCity?.value ?? '';
        }
        if (mode === 'logements' && propCity && landCity && propCity.value === '') {
            propCity.value = landCity?.value ?? '';
        }
        if (mode === 'terrains' && landDistrict && propDistrict && landDistrict.value === '') {
            landDistrict.value = propDistrict?.value ?? '';
        }
        if (mode === 'logements' && propDistrict && landDistrict && propDistrict.value === '') {
            propDistrict.value = landDistrict?.value ?? '';
        }
    };

    const switchSearchTab = (target) => {
        const mode = target === 'terrains' ? 'terrains' : 'logements';

        tabs.forEach((t) => {
            t.classList.toggle('active', t.dataset.target === mode);
        });

        syncSearchFields(mode);
        updateCityCardLinks(mode);

        if (mode === 'terrains') {
            formLogements?.classList.remove('is-active');
            formLogements?.setAttribute('hidden', '');
            formTerrains?.classList.add('is-active');
            formTerrains?.removeAttribute('hidden');
        } else {
            formTerrains?.classList.remove('is-active');
            formTerrains?.setAttribute('hidden', '');
            formLogements?.classList.add('is-active');
            formLogements?.removeAttribute('hidden');
        }

        document.querySelectorAll('[data-search-panel]').forEach((panel) => {
            if (panel.hasAttribute('data-home-cities')) {
                return;
            }
            const show = panel.dataset.searchPanel === mode;
            panel.classList.toggle('is-active', show);
            if (show) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', '');
            }
        });

        document.body.classList.toggle('home-tab-terrains', mode === 'terrains');
        document.body.classList.toggle('home-tab-logements', mode === 'logements');
        document.querySelector('[data-home-search-stack]')?.setAttribute('data-active-mode', mode);

        try {
            sessionStorage.setItem('homeSearchTab', mode);
        } catch {
            /* ignore */
        }
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            switchSearchTab(tab.dataset.target);
        });
    });

    let initialTab = 'logements';
    const hero = document.querySelector('.booking-hero');
    const isSearching = hero?.dataset.isSearching === '1';

    if (!isSearching) {
        try {
            const saved = sessionStorage.getItem('homeSearchTab');
            if (saved === 'terrains' || saved === 'logements') {
                initialTab = saved;
            }
        } catch {
            /* ignore */
        }
    }

    if (formLogements && formTerrains) {
        switchSearchTab(initialTab);
    }

    const menuBtn = document.getElementById('bookingMobileMenuBtn');
    const menuClose = document.getElementById('bookingMobileMenuClose');
    const menu = document.getElementById('bookingMobileMenu');
    const overlay = document.getElementById('bookingMobileOverlay');

    if (window.BookingMobile?.isMobile?.()) {
        window.BookingMobile.attachToBody(overlay);
        window.BookingMobile.attachToBody(menu);
    }

    const openMobileMenu = () => {
        if (!menu || !overlay || !menuBtn) {
            return;
        }
        menu.hidden = false;
        overlay.hidden = false;
        requestAnimationFrame(() => {
            menu.classList.add('is-open');
            overlay.classList.add('is-visible');
        });
        menuBtn.classList.add('is-open');
        menuBtn.setAttribute('aria-expanded', 'true');
        menuBtn.setAttribute('aria-label', 'Fermer le menu');
        menu.setAttribute('aria-hidden', 'false');
        document.body.classList.add('booking-menu-open');
    };

    const closeMobileMenu = () => {
        if (!menu || !overlay || !menuBtn) {
            return;
        }
        menu.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        menuBtn.classList.remove('is-open');
        menuBtn.setAttribute('aria-expanded', 'false');
        menuBtn.setAttribute('aria-label', 'Ouvrir le menu');
        menu.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('booking-menu-open');
        setTimeout(() => {
            if (!menu.classList.contains('is-open')) {
                menu.hidden = true;
                overlay.hidden = true;
            }
        }, 280);
    };

    menuBtn?.addEventListener('click', () => {
        if (menuBtn.classList.contains('is-open')) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    });

    menuClose?.addEventListener('click', closeMobileMenu);
    overlay?.addEventListener('click', closeMobileMenu);

    menu?.querySelectorAll('.search-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            switchSearchTab(tab.dataset.target);
            closeMobileMenu();
        });
    });

    menu?.querySelectorAll('a.booking-discovery-card').forEach((link) => {
        link.addEventListener('click', closeMobileMenu);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menuBtn?.classList.contains('is-open')) {
            closeMobileMenu();
        }
    });

    const initCitiesAutoScroll = () => {
        const mobileQuery = window.matchMedia('(max-width: 991px)');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const cleanups = [];

        const stopAll = () => {
            cleanups.splice(0).forEach((stop) => stop());
        };

        const startGrid = (grid) => {
            if (!mobileQuery.matches || reducedMotion.matches) {
                return;
            }
            if (grid.closest('.d-none') || grid.offsetParent === null) {
                return;
            }

            const maxScroll = () => grid.scrollWidth - grid.clientWidth;
            if (maxScroll() <= 0) {
                return;
            }

            let paused = false;
            let frameId = 0;

            const pause = () => {
                paused = true;
            };
            const resume = () => {
                paused = false;
            };

            grid.addEventListener('touchstart', pause, { passive: true });
            grid.addEventListener('touchend', resume, { passive: true });
            grid.addEventListener('mouseenter', pause);
            grid.addEventListener('mouseleave', resume);

            const tick = () => {
                if (!mobileQuery.matches || reducedMotion.matches) {
                    return;
                }
                if (!paused && grid.offsetParent !== null) {
                    const end = maxScroll();
                    if (end > 0) {
                        grid.scrollLeft += 0.35;
                        if (grid.scrollLeft >= end - 1) {
                            grid.scrollLeft = 0;
                        }
                    }
                }
                frameId = window.requestAnimationFrame(tick);
            };

            frameId = window.requestAnimationFrame(tick);

            cleanups.push(() => {
                window.cancelAnimationFrame(frameId);
                grid.removeEventListener('touchstart', pause);
                grid.removeEventListener('touchend', resume);
                grid.removeEventListener('mouseenter', pause);
                grid.removeEventListener('mouseleave', resume);
            });
        };

        const restart = () => {
            stopAll();
            if (!mobileQuery.matches || reducedMotion.matches) {
                return;
            }
            document.querySelectorAll('.casamance-cities-grid').forEach(startGrid);
        };

        restart();
        mobileQuery.addEventListener('change', restart);
        reducedMotion.addEventListener('change', restart);

        document.querySelectorAll('.search-tab').forEach((tab) => {
            tab.addEventListener('click', () => {
                window.setTimeout(restart, 320);
            });
        });
    };

    initCitiesAutoScroll();

    document.querySelectorAll('[data-cat-dropdown]').forEach((dropdown) => {
        const trigger = dropdown.querySelector('.booking-cat-trigger');
        const menu = dropdown.querySelector('.booking-cat-menu');
        const label = dropdown.querySelector('[data-cat-label]');
        const input = dropdown.querySelector('input[type="hidden"][name="type"], input[type="hidden"][data-cat-input]');
        const backdrop = document.getElementById('bookingSearchBackdrop');
        const mobileMenuQuery = window.matchMedia('(max-width: 991px)');
        if (!trigger || !menu || !label) {
            return;
        }

        const syncCatBackdrop = () => {
            const anyOpen = document.querySelector('[data-cat-dropdown].is-open');
            const heroPanel = document.querySelector('.booking-hero-panel');

            if (anyOpen) {
                document.body.classList.add('booking-cat-menu-open');
                heroPanel?.classList.add('is-dropdown-open');
            } else {
                document.body.classList.remove('booking-cat-menu-open');
                heroPanel?.classList.remove('is-dropdown-open');
            }

            if (!backdrop || !mobileMenuQuery.matches) {
                return;
            }
            if (anyOpen) {
                backdrop.hidden = false;
                backdrop.setAttribute('aria-hidden', 'false');
                requestAnimationFrame(() => backdrop.classList.add('is-visible'));
                document.body.classList.add('booking-search-overlay-open');
            } else {
                backdrop.classList.remove('is-visible');
                backdrop.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('booking-search-overlay-open');
                window.setTimeout(() => {
                    if (!backdrop.classList.contains('is-visible')) {
                        backdrop.hidden = true;
                    }
                }, 260);
            }
        };

        const setOpen = (open) => {
            menu.hidden = !open;
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            dropdown.classList.toggle('is-open', open);
            if (open) {
                trigger.querySelector('.booking-field-caret')?.classList.add('is-up');
            } else {
                trigger.querySelector('.booking-field-caret')?.classList.remove('is-up');
            }
            syncCatBackdrop();
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const willOpen = menu.hidden;
            document.querySelectorAll('[data-cat-dropdown].is-open').forEach((other) => {
                if (other !== dropdown) {
                    other.classList.remove('is-open');
                    other.querySelector('.booking-cat-menu')?.setAttribute('hidden', '');
                    other.querySelector('.booking-cat-trigger')?.setAttribute('aria-expanded', 'false');
                    other.querySelector('.booking-field-caret')?.classList.remove('is-up');
                }
            });
            setOpen(willOpen);
        });

        menu.querySelectorAll('.booking-cat-option').forEach((option) => {
            option.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const value = option.dataset.value ?? '';
                if (input) {
                    input.value = value;
                }
                label.textContent = option.textContent.trim();
                menu.querySelectorAll('.booking-cat-option').forEach((item) => {
                    const selected = item === option;
                    item.classList.toggle('is-selected', selected);
                    item.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                setOpen(false);
            });
        });

        backdrop?.addEventListener('click', () => {
            if (dropdown.classList.contains('is-open')) {
                setOpen(false);
            }
        });

        document.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && dropdown.classList.contains('is-open')) {
                setOpen(false);
                trigger.focus();
            }
        });
    });
});