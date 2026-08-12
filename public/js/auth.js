document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-auth-form]');

    if (form) {
        const roleInputs = Array.from(form.querySelectorAll('input[name="account_type"]'));
        const companyField = form.querySelector('[data-company-field]');
        const companyInput = companyField?.querySelector('input');
        const submitLabel = form.querySelector('[data-submit-label]');
        const panels = Array.from(document.querySelectorAll('[data-panel-for]'));

        const syncRole = () => {
            const selected = roleInputs.find((input) => input.checked)?.value || 'employee';
            const isEmployer = selected === 'employer';

            form.classList.toggle('is-employer', isEmployer);

            if (companyInput) {
                // Only required when the employer path is active, matching the
                // required_if rule on the server.
                companyInput.required = isEmployer;
                companyInput.disabled = !isEmployer;
            }

            if (submitLabel) {
                submitLabel.textContent = isEmployer ? 'Create employer account' : 'Create account';
            }

            panels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.dataset.panelFor === selected);
            });
        };

        roleInputs.forEach((input) => input.addEventListener('change', syncRole));
        syncRole();
    }

    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        const input = document.getElementById(button.dataset.togglePassword);

        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const revealed = input.type === 'text';

            input.type = revealed ? 'password' : 'text';
            button.setAttribute('aria-label', revealed ? 'Show password' : 'Hide password');
            button.querySelector('i')?.classList.toggle('fa-eye', revealed);
            button.querySelector('i')?.classList.toggle('fa-eye-slash', !revealed);
        });
    });
});
