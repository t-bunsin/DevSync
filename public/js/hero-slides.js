/* Hero slides: switches the landing hero between its job-seeker and employer
   slides. The tabs follow the ARIA tabs pattern (arrow keys, Home/End); the
   slideshow advances when the progress bar on the current tab finishes its
   CSS animation, so pausing is just pausing that animation. It pauses while
   the pointer or keyboard focus is in the slides, while the hero is off
   screen, and for good when the visitor presses pause. Under
   prefers-reduced-motion it never advances on its own. */
document.addEventListener('DOMContentLoaded', () => {
    const hero = document.querySelector('[data-hero-slides]');
    const track = hero?.querySelector('[data-slides]');
    const bar = hero?.querySelector('[data-slides-bar]');

    if (!hero || !track || !bar) {
        return;
    }

    const slides = Array.from(track.querySelectorAll('[data-slide]'));
    const tabs = Array.from(bar.querySelectorAll('[data-slide-tab]'));
    const toggle = bar.querySelector('[data-slide-toggle]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    if (slides.length < 2 || tabs.length !== slides.length) {
        return;
    }

    const SETTLE_MS = 800; // a little longer than jf-slide-in in hero-slides.css

    let current = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
    let userPaused = false;
    let settleTimer = null;
    const holds = new Set();

    bar.hidden = false;

    const render = () => {
        const autoplay = !reducedMotion.matches && !userPaused;

        hero.classList.toggle('is-autoplay', autoplay);
        hero.classList.toggle('is-paused', autoplay && holds.size > 0);

        if (toggle) {
            toggle.hidden = reducedMotion.matches;
            toggle.setAttribute('aria-label', userPaused ? 'Play slideshow' : 'Pause slideshow');
            toggle.querySelector('i')?.classList.replace(userPaused ? 'fa-pause' : 'fa-play', userPaused ? 'fa-play' : 'fa-pause');
        }
    };

    /* Same count-up as the first showing (jobs.js), replayed whenever a slide
       comes back so its numbers land again with the rest of its entrance. */
    const countUp = (element) => {
        const label = element.dataset.countLabel ?? element.textContent.trim();
        const match = label.match(/^([\d,.]+)(.*)$/);
        element.dataset.countLabel = label;

        if (!match) {
            return;
        }

        const target = Number(match[1].replace(/,/g, ''));
        const suffix = match[2];
        const started = performance.now();

        const step = (now) => {
            const progress = Math.min((now - started) / 1400, 1);
            const value = Math.round(target * (1 - Math.pow(1 - progress, 3)));

            element.textContent = progress < 1 ? value.toLocaleString('en-US') + suffix : label;

            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };

        window.requestAnimationFrame(step);
    };

    const show = (index, direction) => {
        const next = (index + slides.length) % slides.length;

        if (next === current) {
            return;
        }

        const from = slides[current];
        const to = slides[next];
        const animate = !reducedMotion.matches;

        // Finish any change still in flight before starting this one.
        window.clearTimeout(settleTimer);
        slides.forEach((slide) => slide.classList.remove('is-leaving', 'is-entering'));

        track.style.setProperty('--jf-slide-dir', String(direction));

        from.classList.remove('is-active');
        from.inert = true;

        to.inert = false;
        to.classList.add('is-active');

        if (animate) {
            from.classList.add('is-leaving');
            to.classList.add('is-entering');

            settleTimer = window.setTimeout(() => {
                from.classList.remove('is-leaving');
                to.classList.remove('is-entering');
            }, SETTLE_MS);

            to.querySelectorAll('[data-count-up]').forEach(countUp);
        }

        tabs.forEach((tab, i) => {
            const selected = i === next;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
        });

        hero.dataset.activeSlide = to.dataset.slide;
        current = next;

        to.dispatchEvent(new CustomEvent('jf:slide-shown', { detail: { direction } }));
    };

    const step = (by) => show(current + by, by);

    /* ---------- Controls ---------- */
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => show(i, i > current ? 1 : -1));
    });

    bar.querySelector('[role="tablist"]')?.addEventListener('keydown', (event) => {
        const keys = {
            ArrowRight: current + 1,
            ArrowLeft: current - 1,
            Home: 0,
            End: slides.length - 1,
        };

        if (!(event.key in keys)) {
            return;
        }

        event.preventDefault();
        const target = (keys[event.key] + slides.length) % slides.length;
        show(target, event.key === 'ArrowLeft' || event.key === 'Home' ? -1 : 1);
        tabs[target].focus();
    });

    bar.querySelectorAll('[data-slide-step]').forEach((button) => {
        button.addEventListener('click', () => step(Number(button.dataset.slideStep) || 1));
    });

    toggle?.addEventListener('click', () => {
        userPaused = !userPaused;
        render();
    });

    // The current tab's progress bar running out is the autoplay tick.
    bar.addEventListener('animationend', (event) => {
        if (event.animationName === 'jf-slide-progress' && hero.classList.contains('is-autoplay')) {
            step(1);
        }
    });

    /* ---------- Pause while someone is looking closely ---------- */
    const hold = (reason, on) => {
        if (on) {
            holds.add(reason);
        } else {
            holds.delete(reason);
        }

        render();
    };

    track.addEventListener('pointerenter', (event) => {
        if (event.pointerType === 'mouse') {
            hold('hover', true);
        }
    });

    track.addEventListener('pointerleave', () => hold('hover', false));

    // Keyboard focus only: a mouse click on a tab should not stop the show.
    hero.addEventListener('focusin', (event) => {
        if (event.target.matches(':focus-visible') && !event.target.closest('[data-slides-bar]')) {
            hold('focus', true);
        }
    });

    hero.addEventListener('focusout', (event) => {
        if (!hero.contains(event.relatedTarget)) {
            hold('focus', false);
        }
    });

    if (typeof IntersectionObserver !== 'undefined') {
        new IntersectionObserver(([entry]) => hold('offscreen', !entry.isIntersecting), { threshold: 0.35 }).observe(hero);
    }

    document.addEventListener('visibilitychange', () => hold('hidden', document.hidden));

    /* ---------- Swipe on touch screens ---------- */
    let touch = null;

    track.addEventListener('touchstart', (event) => {
        // The rack scrolls sideways on its own; leave those swipes to it.
        touch = event.target.closest('.jf-rack__list') ? null : event.touches[0];
        touch = touch && { x: touch.clientX, y: touch.clientY };
    }, { passive: true });

    track.addEventListener('touchend', (event) => {
        if (!touch) {
            return;
        }

        const end = event.changedTouches[0];
        const dx = end.clientX - touch.x;
        const dy = end.clientY - touch.y;
        touch = null;

        if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy) * 1.5) {
            step(dx < 0 ? 1 : -1);
        }
    }, { passive: true });

    reducedMotion.addEventListener?.('change', render);

    render();
});
