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

        // Company name only applies to employers. Kept in sync with the role
        // select so the field is not submitted as dead weight for other roles;
        // the server still enforces required_if independently.
        const roleSelect = document.querySelector('[data-role-select]');
        const employerOnly = document.querySelectorAll('[data-employer-only]');

        if (roleSelect && employerOnly.length) {
            const syncEmployerFields = () => {
                const isEmployer = roleSelect.value === 'employer';

                employerOnly.forEach((field) => {
                    field.hidden = !isEmployer;
                    field.querySelectorAll('input, select, textarea').forEach((input) => {
                        input.required = isEmployer;
                    });
                });
            };

            roleSelect.addEventListener('change', syncEmployerFields);
            syncEmployerFields();
        }

        const errorSummary = document.querySelector('.kh-bo__errors');

        if (errorSummary) {
            errorSummary.focus();
        }
    });
})();
