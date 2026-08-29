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

            const companyReq = form.querySelector('[data-company-req]');

            if (companyReq) {
                companyReq.hidden = !isEmployer;
            }

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

    /*
     * Live password feedback. Deliberately a mirror of
     * RegisterController::passwordRules() and nothing more: the server is still
     * the gate, this just saves a round trip to find out what is missing.
     */
    const password = document.querySelector('[data-password]');
    const rules = document.querySelector('[data-password-rules]');

    if (password && rules) {
        const tests = {
            length: (v) => v.length >= 8,
            mixed: (v) => /[a-z]/.test(v) && /[A-Z]/.test(v),
            number: (v) => /\d/.test(v),
            symbol: (v) => /[^\p{L}\p{N}]/u.test(v),
        };

        const rows = Array.from(rules.querySelectorAll('[data-rule]'));

        const check = () => {
            const value = password.value;
            rules.classList.toggle('is-active', value.length > 0);

            rows.forEach((row) => {
                const passed = tests[row.dataset.rule](value);
                row.classList.toggle('is-met', passed);
                const icon = row.querySelector('i');
                icon?.classList.toggle('fa-circle-check', passed);
                icon?.classList.toggle('fa-circle', !passed);
            });
        };

        password.addEventListener('input', check);
        check();
    }

    const confirmation = document.querySelector('[data-password-confirm]');
    const match = document.querySelector('[data-password-match]');

    if (password && confirmation && match) {
        const compare = () => {
            if (!confirmation.value) {
                match.hidden = true;
                return;
            }

            const same = confirmation.value === password.value;
            match.hidden = false;
            match.textContent = same ? 'Passwords match.' : 'Passwords do not match yet.';
            match.classList.toggle('is-ok', same);
        };

        confirmation.addEventListener('input', compare);
        password.addEventListener('input', compare);
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
