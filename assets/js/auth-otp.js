'use strict';

(function () {
    const form = document.querySelector('[data-auth-otp-form]');
    if (!form) return;

    const inputs = Array.from(form.querySelectorAll('[data-auth-otp] .auth-otp-input'));
    const hidden = form.querySelector('#authOtpCode');
    const actionBtn = document.getElementById('authOtpActionBtn');
    if (!inputs.length || !actionBtn) return;

    const labelVerify = actionBtn.dataset.labelVerify || 'Confirmer le code';
    const labelResend = actionBtn.dataset.labelResend || 'Renvoyer le code';
    const verifyAction = actionBtn.dataset.verifyAction || form.action;
    const resendAction = actionBtn.dataset.resendAction || form.action;

    const currentCode = () => inputs.map((input) => input.value.replace(/\D/g, '').slice(0, 1)).join('');

    const isComplete = () => currentCode().length === 6;

    const syncAction = () => {
        if (hidden) {
            hidden.value = currentCode();
        }

        if (isComplete()) {
            actionBtn.textContent = labelVerify;
            form.action = verifyAction;
            actionBtn.classList.remove('auth-submit-secondary');
        } else {
            actionBtn.textContent = labelResend;
            form.action = resendAction;
            actionBtn.classList.add('auth-submit-secondary');
        }
    };

    const focusAt = (index) => {
        const target = inputs[index];
        if (!target) return;
        target.focus();
        target.select();
    };

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            const digit = input.value.replace(/\D/g, '').slice(-1);
            input.value = digit;
            syncAction();
            if (digit && index < inputs.length - 1) {
                focusAt(index + 1);
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && input.value === '' && index > 0) {
                focusAt(index - 1);
            }
            if (event.key === 'ArrowLeft' && index > 0) {
                event.preventDefault();
                focusAt(index - 1);
            }
            if (event.key === 'ArrowRight' && index < inputs.length - 1) {
                event.preventDefault();
                focusAt(index + 1);
            }
        });

        input.addEventListener('paste', (event) => {
            const text = (event.clipboardData || window.clipboardData)?.getData('text') || '';
            const digits = text.replace(/\D/g, '').slice(0, inputs.length);
            if (!digits) return;
            event.preventDefault();
            digits.split('').forEach((digit, offset) => {
                if (inputs[index + offset]) {
                    inputs[index + offset].value = digit;
                }
            });
            syncAction();
            focusAt(Math.min(index + digits.length, inputs.length - 1));
        });
    });

    form.addEventListener('submit', syncAction);
    syncAction();
    focusAt(0);
})();
