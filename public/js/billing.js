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

    // Checkout confirm: PayWay's purchase call happens server-side during
    // this submit, so guard against a second click while it is in flight.
    const checkoutForm = document.querySelector('[data-checkout-form]');

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (event) {
            if (checkoutForm.dataset.processing) {
                return;
            }

            checkoutForm.dataset.processing = 'true';

            const button = checkoutForm.querySelector('[data-checkout-submit]');

            if (button) {
                button.disabled = true;
            }
        });
    }
})();
