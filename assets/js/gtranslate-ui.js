'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const DEFAULT_LANG = 'fr';
    const switchers = Array.from(document.querySelectorAll('[data-gt-lang-switcher]'));
    if (switchers.length === 0) {
        return;
    }

    const flagUrl = (code) => `https://cdn.gtranslate.net/flags/svg/${code}.svg`;

    const getStoredLang = () => {
        try {
            const stored = JSON.parse(localStorage.getItem('__GT_TRANSLATE_LANGS') || 'null');
            if (stored && typeof stored.tgtLang === 'string') {
                return stored.tgtLang;
            }
        } catch {
            /* ignore */
        }
        return DEFAULT_LANG;
    };

    const loadTranslator = (callback) => {
        if (typeof window.doGTranslate === 'function') {
            callback();
            return;
        }
        const existing = document.getElementById('gt-translate-lib');
        if (existing) {
            existing.addEventListener('load', () => callback(), { once: true });
            return;
        }
        const script = document.createElement('script');
        script.id = 'gt-translate-lib';
        script.src = 'https://cdn.gtranslate.net/widgets/latest/lib.min.js';
        script.addEventListener('load', () => callback(), { once: true });
        document.body.appendChild(script);
    };

    const applyLanguage = (lang) => {
        loadTranslator(() => {
            if (typeof window.doGTranslate === 'function') {
                window.doGTranslate(`${DEFAULT_LANG}|${lang}`);
            } else if (window.__GT?.translator?.translate) {
                window.__GT.translator.translate(DEFAULT_LANG, lang);
            }
        });
    };

    const syncUi = (lang) => {
        switchers.forEach((root) => {
            const options = root.querySelectorAll('[data-gt-lang]');
            let active = null;
            options.forEach((option) => {
                const selected = option.getAttribute('data-gt-lang') === lang;
                option.classList.toggle('is-active', selected);
                option.setAttribute('aria-selected', selected ? 'true' : 'false');
                if (selected) {
                    active = option;
                }
            });

            if (!active) {
                return;
            }

            const flag = root.querySelector('[data-gt-flag]');
            const code = root.querySelector('[data-gt-code]');
            const flagCode = active.getAttribute('data-gt-flag-code') || lang;
            const shortCode = active.getAttribute('data-gt-code') || lang.toUpperCase();

            if (flag instanceof HTMLImageElement) {
                flag.src = flagUrl(flagCode);
            }
            if (code) {
                code.textContent = shortCode;
            }
        });

        document.documentElement.setAttribute('lang', lang === 'zh-TW' ? 'zh-Hant' : lang);
        document.documentElement.classList.toggle('translated-rtl', lang === 'ar');
    };

    const closeAll = () => {
        switchers.forEach((root) => {
            const menu = root.querySelector('[data-gt-menu]');
            const trigger = root.querySelector('[data-gt-trigger]');
            if (menu) {
                menu.hidden = true;
            }
            root.classList.remove('is-open');
            trigger?.setAttribute('aria-expanded', 'false');
        });
    };

    switchers.forEach((root) => {
        const trigger = root.querySelector('[data-gt-trigger]');
        const menu = root.querySelector('[data-gt-menu]');
        if (!trigger || !menu) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const willOpen = menu.hidden;
            closeAll();
            if (willOpen) {
                menu.hidden = false;
                root.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        menu.querySelectorAll('[data-gt-lang]').forEach((option) => {
            option.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const lang = option.getAttribute('data-gt-lang') || DEFAULT_LANG;
                syncUi(lang);
                applyLanguage(lang);
                closeAll();
            });
        });
    });

    document.addEventListener('click', () => closeAll());
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    const initial = getStoredLang();
    syncUi(initial);
    if (initial !== DEFAULT_LANG) {
        applyLanguage(initial);
    }
});
