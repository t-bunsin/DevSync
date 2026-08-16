/*
 * Tabbed back-office forms.
 *
 * Panels stay in the DOM and are toggled with [hidden], so hidden fields still
 * post. Two cases have to reveal a panel the user cannot see:
 *
 *   - the browser refusing to submit because a required field in a hidden
 *     panel is empty (it fires `invalid` and cannot focus a hidden control), and
 *   - a server-side validation error rendering .is-invalid inside a hidden panel.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-bo-tabs]').forEach(setupTabs);
    });

    function setupTabs(root) {
        const tabs = Array.from(root.querySelectorAll('[data-bo-tab]'));
        const panels = Array.from(root.querySelectorAll('[data-bo-panel]'));

        if (!tabs.length || !panels.length) {
            return;
        }

        function activate(name, { focusTab = false } = {}) {
            tabs.forEach((tab) => {
                const isActive = tab.dataset.boTab === name;
                tab.setAttribute('aria-selected', String(isActive));
                tab.tabIndex = isActive ? 0 : -1;

                if (isActive && focusTab) {
                    tab.focus();
                }
            });

            panels.forEach((panel) => {
                panel.hidden = panel.dataset.boPanel !== name;
            });
        }

        function panelNameOf(element) {
            const panel = element.closest('[data-bo-panel]');
            return panel ? panel.dataset.boPanel : null;
        }

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => activate(tab.dataset.boTab));

            tab.addEventListener('keydown', (event) => {
                const keys = { ArrowRight: 1, ArrowLeft: -1 };

                if (!(event.key in keys)) {
                    return;
                }

                event.preventDefault();
                const next = (index + keys[event.key] + tabs.length) % tabs.length;
                activate(tabs[next].dataset.boTab, { focusTab: true });
            });
        });

        const form = root.closest('form') || root.querySelector('form');

        if (form) {
            // Capture phase: `invalid` does not bubble.
            form.addEventListener('invalid', (event) => {
                const name = panelNameOf(event.target);

                if (name) {
                    activate(name);
                }
            }, true);
        }

        // Server-side errors: open the panel holding the first one.
        const firstError = root.querySelector('.is-invalid');
        const errorPanel = firstError ? panelNameOf(firstError) : null;

        activate(errorPanel || tabs[0].dataset.boTab);

        // Badge each tab with how many of its fields failed validation.
        panels.forEach((panel) => {
            const count = panel.querySelectorAll('.is-invalid').length;
            const tab = tabs.find((item) => item.dataset.boTab === panel.dataset.boPanel);

            if (!count || !tab) {
                return;
            }

            const badge = document.createElement('span');
            badge.className = 'kh-bo__tab-count';
            badge.textContent = String(count);
            tab.appendChild(badge);
        });
    }
})();
