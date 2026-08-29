(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const root = document.documentElement;
        const toggle = document.querySelector('.kh-admin-theme-toggle');
        const themeColor = document.getElementById('admin-theme-color');

        const setTheme = (theme, persist = false) => {
            const isDark = theme === 'dark';
            // Labels come from the button's data-* attributes (ui.admin.a11y.*)
            // so the toggle follows the chosen language; English is the fallback.
            const toDark = toggle?.dataset.themeToDark || 'Switch to dark theme';
            const toLight = toggle?.dataset.themeToLight || 'Switch to light theme';
            const nextThemeLabel = isDark ? toLight : toDark;

            root.dataset.adminTheme = isDark ? 'dark' : 'light';
            root.style.colorScheme = isDark ? 'dark' : 'light';
            themeColor?.setAttribute('content', isDark ? '#071015' : '#ffffff');

            if (toggle) {
                toggle.setAttribute('aria-pressed', String(isDark));
                toggle.setAttribute('aria-label', nextThemeLabel);
                toggle.setAttribute('title', nextThemeLabel);
            }

            if (persist) {
                try {
                    localStorage.setItem('khworks:theme', isDark ? 'dark' : 'light');
                } catch (error) {
                    // Theme switching remains available if storage is blocked.
                }
            }
        };

        setTheme(root.dataset.adminTheme === 'dark' ? 'dark' : 'light');

        toggle?.addEventListener('click', () => {
            setTheme(root.dataset.adminTheme === 'dark' ? 'light' : 'dark', true);
        });

    });
})();
