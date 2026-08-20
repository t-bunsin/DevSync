/*
 * Account billing — package picker + mock processing step.
 *
 * Billing-period toggle: same data-amount/data-period pattern as pricing.js,
 * scoped to .kh-bill instead of .jf-pricing. Also keeps each plan's "Select"
 * link pointed at whichever period is currently shown, so checkout opens on
 * the period the user was actually looking at.
 */
(function () {
    'use strict';

    const toggle = document.querySelector('[data-billing-toggle]');
    const section = toggle ? toggle.closest('.kh-bill') : null;

    if (toggle && section) {
        const buttons = Array.from(toggle.querySelectorAll('[data-period]'));
        const amounts = Array.from(section.querySelectorAll('[data-amount]'));
        const selectLinks = Array.from(section.querySelectorAll('[data-plan-select]'));

        const apply = function (period) {
            amounts.forEach(function (element) {
                const value = element.dataset[period];

                if (typeof value === 'string') {
                    element.textContent = value;
                }
            });

            selectLinks.forEach(function (link) {
                const url = new URL(link.href);
                url.searchParams.set('billing_period', period);
                link.href = url.toString();
            });

            buttons.forEach(function (button) {
                button.setAttribute('aria-pressed', String(button.dataset.period === period));
            });
        };

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                apply(button.dataset.period);
            });
        });

        apply('monthly');
    }

    // Checkout confirm: show a brief "processing" state before the form
    // actually submits, since there is no real payment gateway to wait on.
    const checkoutForm = document.querySelector('[data-checkout-form]');

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (event) {
            if (checkoutForm.dataset.processing) {
                return;
            }

            event.preventDefault();
            checkoutForm.dataset.processing = 'true';

            const button = checkoutForm.querySelector('[data-checkout-submit]');
            const status = checkoutForm.querySelector('[data-checkout-status]');

            if (button) {
                button.disabled = true;
            }

            if (status) {
                status.classList.add('is-active');
            }

            window.setTimeout(function () {
                checkoutForm.submit();
            }, 1200);
        });
    }
})();
