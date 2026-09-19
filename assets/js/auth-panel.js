'use strict';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-auth-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const field = btn.closest('.auth-field');
            const input = field?.querySelector('[data-auth-password]');
            const icon = btn.querySelector('i');
            if (!input || !icon) {
                return;
            }

            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !reveal);
            icon.classList.toggle('bi-eye-slash', reveal);
        });
    });

    document.querySelectorAll('[data-auth-social]').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.disabled = true;
            window.setTimeout(() => {
                btn.disabled = false;
            }, 1200);
        });
    });

    const remember = document.getElementById('authRemember');
    const loginForm = document.getElementById('authLoginForm');
    const emailInput = loginForm?.querySelector('input[name="email"]');

    if (remember && emailInput) {
        try {
            const saved = localStorage.getItem('zig_auth_remember_email');
            if (saved && !emailInput.value) {
                emailInput.value = saved;
                remember.checked = true;
            }
        } catch {
            /* ignore */
        }

        loginForm?.addEventListener('submit', () => {
            try {
                if (remember.checked) {
                    localStorage.setItem('zig_auth_remember_email', emailInput.value);
                } else {
                    localStorage.removeItem('zig_auth_remember_email');
                }
            } catch {
                /* ignore */
            }
        });
    }

    const registerRoot = document.querySelector('[data-auth-panel-root][data-initial-mode="register"]');
    if (registerRoot) {
        registerRoot.dispatchEvent(new CustomEvent('auth:register-view'));
        registerRoot.dispatchEvent(new CustomEvent('auth:account-type-change'));
    }
});
