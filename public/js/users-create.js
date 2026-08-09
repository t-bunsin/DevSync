(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            const input = document.getElementById(button.dataset.passwordToggle);

            if (!input) {
                return;
            }

            button.addEventListener('click', () => {
                const shouldShow = input.type === 'password';
                const icon = button.querySelector('i');
                const labelTarget = input.id === 'inputPasswordConfirm' ? 'password confirmation' : 'password';

                input.type = shouldShow ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(shouldShow));
                button.setAttribute('aria-label', `${shouldShow ? 'Hide' : 'Show'} ${labelTarget}`);

                icon?.classList.toggle('fa-eye', !shouldShow);
                icon?.classList.toggle('fa-eye-slash', shouldShow);
            });
        });

        const errorSummary = document.querySelector('.kh-user-create__error-summary');

        if (errorSummary) {
            errorSummary.focus();
        }
    });
})();
