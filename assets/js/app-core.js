'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const initFavorites = () => {
        const appBase = document.body.dataset.appUrl || '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const isAuthed = document.body.dataset.auth === '1';
        const favButtons = document.querySelectorAll('.fav-btn[data-favorite-id]');
        if (!favButtons.length) {
            return;
        }

        const setFavState = (btn, active) => {
            const icon = btn.querySelector('i');
            if (!icon) {
                return;
            }
            icon.classList.toggle('bi-heart-fill', active);
            icon.classList.toggle('bi-heart', !active);
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            const addLabel = btn.dataset.favoriteLabelAdd || '';
            const removeLabel = btn.dataset.favoriteLabelRemove || '';
            if (addLabel !== '' && removeLabel !== '') {
                btn.setAttribute('aria-label', active ? removeLabel : addLabel);
            }
        };

        const loginRedirect = () => {
            const redirect = `${window.location.pathname}${window.location.search}`;
            window.location.href = `${appBase}/login?redirect=${encodeURIComponent(redirect)}`;
        };

        const loadIds = async () => {
            if (!isAuthed) {
                return { properties: new Set(), lands: new Set() };
            }

            try {
                const res = await fetch(`${appBase}/api/favorites/ids`, {
                    headers: { Accept: 'application/json' },
                });
                const json = await res.json();
                return {
                    properties: new Set((json.property_ids || []).map(Number)),
                    lands: new Set((json.land_ids || []).map(Number)),
                };
            } catch {
                return { properties: new Set(), lands: new Set() };
            }
        };

        const toggleFavorite = async (btn) => {
            const type = btn.dataset.favoriteType || 'property';
            const id = parseInt(btn.dataset.favoriteId || '0', 10);
            if (!id) {
                return;
            }

            btn.classList.add('is-loading');
            btn.disabled = true;

            const body = new FormData();
            body.append('_token', csrfToken);
            body.append('type', type);
            body.append('id', String(id));

            try {
                const res = await fetch(`${appBase}/api/favorites/toggle`, {
                    method: 'POST',
                    body,
                    headers: { Accept: 'application/json' },
                });

                if (res.status === 401) {
                    loginRedirect();
                    return;
                }

                const json = await res.json();
                if (json.success) {
                    setFavState(btn, Boolean(json.favorited));
                }
            } catch {
                /* ignore */
            } finally {
                btn.classList.remove('is-loading');
                btn.disabled = false;
            }
        };

        loadIds().then(({ properties, lands }) => {
            favButtons.forEach((btn) => {
                const type = btn.dataset.favoriteType || 'property';
                const id = parseInt(btn.dataset.favoriteId || '0', 10);
                const active = type === 'land' ? lands.has(id) : properties.has(id);
                if (active) {
                    setFavState(btn, true);
                }
            });
        });

        favButtons.forEach((btn) => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                if (!isAuthed) {
                    loginRedirect();
                    return;
                }

                try {
                    await toggleFavorite(btn);
                } catch {
                    /* ignore */
                }
            });
        });
    };

    const initAuthMenus = () => {
        document.querySelectorAll('[data-auth-menu]').forEach((menu) => {
            const trigger = menu.querySelector('.auth-menu-trigger');
            const dropdown = menu.querySelector('.auth-menu-dropdown');
            if (!trigger || !dropdown) {
                return;
            }

            const closeMenu = () => {
                dropdown.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            };

            const openMenu = () => {
                document.querySelectorAll('[data-auth-menu] .auth-menu-dropdown').forEach((other) => {
                    if (other !== dropdown) {
                        other.hidden = true;
                        other.closest('[data-auth-menu]')
                            ?.querySelector('.auth-menu-trigger')
                            ?.setAttribute('aria-expanded', 'false');
                    }
                });
                dropdown.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
            };

            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (dropdown.hidden) {
                    openMenu();
                } else {
                    closeMenu();
                }
            });

            dropdown.querySelectorAll('.auth-menu-option, .auth-menu-option-logout').forEach((link) => {
                link.addEventListener('click', closeMenu);
            });
        });

        const closeAll = () => {
            document.querySelectorAll('[data-auth-menu] .auth-menu-dropdown').forEach((dropdown) => {
                dropdown.hidden = true;
            });
            document.querySelectorAll('.auth-menu-trigger').forEach((trigger) => {
                trigger.setAttribute('aria-expanded', 'false');
            });
        };

        document.addEventListener('click', (e) => {
            if (!e.target.closest('[data-auth-menu]')) {
                closeAll();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAll();
            }
        });
    };

    initFavorites();
    initAuthMenus();
});
