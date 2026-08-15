/*
 * Pricing billing-period switch.
 *
 * Every figure that changes between monthly and annual billing carries both
 * values as data-monthly / data-annual, so this only has to swap text — it
 * never does the arithmetic. The amounts come from the Blade view, which
 * derives the annual column from the monthly one.
 */
(function () {
    'use strict';

    const toggle = document.querySelector('[data-billing-toggle]');
    const section = toggle ? toggle.closest('.jf-pricing') : null;

    if (!toggle || !section) {
        return;
    }

    const buttons = Array.from(toggle.querySelectorAll('[data-period]'));
    const amounts = Array.from(section.querySelectorAll('[data-amount]'));

    if (!buttons.length || !amounts.length) {
        return;
    }

    function apply(period) {
        amounts.forEach(function (element) {
            const value = element.dataset[period];

            if (typeof value === 'string') {
                element.textContent = value;
            }
        });

        buttons.forEach(function (button) {
            button.setAttribute('aria-pressed', String(button.dataset.period === period));
        });

        section.dataset.period = period;
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            apply(button.dataset.period);
        });
    });

    // The control works from here on, so it can be shown.
    toggle.hidden = false;
    apply('monthly');
})();
