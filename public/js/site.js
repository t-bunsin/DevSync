document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const themeToggle = document.querySelector('.jf-theme-toggle');
    const themeColor = document.getElementById('theme-color');

    const setTheme = (theme, persist = false) => {
        const isDark = theme === 'dark';
        const nextThemeLabel = isDark ? 'Switch to light theme' : 'Switch to dark theme';

        root.dataset.theme = isDark ? 'dark' : 'light';
        root.style.colorScheme = isDark ? 'dark' : 'light';
        themeColor?.setAttribute('content', isDark ? '#071015' : '#ffffff');

        if (themeToggle) {
            themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            themeToggle.setAttribute('aria-label', nextThemeLabel);
            themeToggle.setAttribute('title', nextThemeLabel);
        }

        if (persist) {
            try {
                localStorage.setItem('khworks:theme', isDark ? 'dark' : 'light');
            } catch (error) {
                // The switch still works when browser storage is unavailable.
            }
        }
    };

    setTheme(root.dataset.theme === 'dark' ? 'dark' : 'light');

    themeToggle?.addEventListener('click', () => {
        setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark', true);
    });

    const scrollControls = document.querySelector('[data-scroll-controls]');
    const scrollTopButton = scrollControls?.querySelector('[data-scroll-to="top"]');
    const scrollBottomButton = scrollControls?.querySelector('[data-scroll-to="bottom"]');

    if (scrollControls && scrollTopButton && scrollBottomButton) {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let scrollFrame;

        const getMaximumScroll = () => Math.max(
            document.documentElement.scrollHeight,
            document.body.scrollHeight
        ) - window.innerHeight;

        const updateScrollControls = () => {
            const maximumScroll = Math.max(0, getMaximumScroll());
            const currentScroll = Math.max(0, window.scrollY);
            const pageCanScroll = maximumScroll > 8;

            scrollControls.classList.toggle('is-hidden', !pageCanScroll);
            scrollTopButton.disabled = !pageCanScroll || currentScroll <= 8;
            scrollBottomButton.disabled = !pageCanScroll || currentScroll >= maximumScroll - 8;
            scrollFrame = undefined;
        };

        const queueScrollUpdate = () => {
            if (!scrollFrame) {
                scrollFrame = window.requestAnimationFrame(updateScrollControls);
            }
        };

        const scrollToPosition = (top) => {
            window.scrollTo({
                top,
                behavior: prefersReducedMotion.matches ? 'auto' : 'smooth',
            });
        };

        scrollTopButton.addEventListener('click', () => scrollToPosition(0));
        scrollBottomButton.addEventListener('click', () => scrollToPosition(getMaximumScroll()));
        window.addEventListener('scroll', queueScrollUpdate, { passive: true });
        window.addEventListener('resize', queueScrollUpdate);
        window.addEventListener('load', updateScrollControls, { once: true });
        updateScrollControls();
    }

    const header = document.querySelector('.jf-header');
    const toggle = document.querySelector('.jf-menu-toggle');
    const menu = document.getElementById('jf-header-menu');

    if (!header || !toggle || !menu) {
        return;
    }

    const setMenuOpen = (isOpen) => {
        header.classList.toggle('is-menu-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.querySelector('i')?.classList.toggle('fa-bars', !isOpen);
        toggle.querySelector('i')?.classList.toggle('fa-xmark', isOpen);
    };

    toggle.addEventListener('click', () => {
        setMenuOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMenuOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuOpen(false);
            toggle.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 960) {
            setMenuOpen(false);
        }
    });
});
