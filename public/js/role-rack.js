/* Role rack: each hanging card is a damped pendulum around its resting tilt.
   The cards drop onto the rail when the section first scrolls into view, sway
   in a faint breeze, and get knocked by a cursor swiping across them (or by the
   rack being scrolled on touch screens). Skipped under prefers-reduced-motion,
   where the cards just hang still. */
document.addEventListener('DOMContentLoaded', () => {
    const rack = document.querySelector('[data-rack]');

    if (!rack || window.matchMedia('(prefers-reduced-motion: reduce)').matches || typeof IntersectionObserver === 'undefined') {
        return;
    }

    const rail = rack.querySelector('.jf-rack__rail');
    const list = rack.querySelector('.jf-rack__list');

    const STIFFNESS = 38;    // pull back toward the resting tilt
    const DAMPING = 1.9;     // low, so a knock rings for a few swings
    const MAX_SPEED = 160;   // deg/s, keeps a frantic swipe from spinning a card
    const SWIPE_FORCE = 0.4; // deg/s added per pixel the cursor travels over a card

    const cards = Array.from(rack.querySelectorAll('[data-rack-swing]')).map((element, index) => {
        const rest = Number(element.dataset.tilt) || 0;

        return {
            element,
            rest,
            angle: rest,
            speed: 0,
            // Offsets so the breeze never moves the cards in lockstep.
            phase: index * 1.7,
            gust: 0.8 + (index % 3) * 0.22,
        };
    });

    const clampSpeed = (value) => Math.max(-MAX_SPEED, Math.min(MAX_SPEED, value));
    let railJiggle = 0;

    const knock = (card, impulse) => {
        card.speed = clampSpeed(card.speed + impulse);
        railJiggle = Math.min(3, railJiggle + Math.abs(impulse) * 0.02);
    };

    let running = false;
    let last = 0;

    const frame = (now) => {
        if (!running) {
            return;
        }

        // Clamp the step so a backgrounded tab does not return to a wild swing.
        const dt = Math.min((now - last) / 1000, 1 / 30);
        const t = now / 1000;
        last = now;

        cards.forEach((card) => {
            const breeze = Math.sin(t * card.gust + card.phase) * 9 + Math.sin(t * 2.3 + card.phase * 0.5) * 3;
            const accel = -STIFFNESS * (card.angle - card.rest) - DAMPING * card.speed + breeze;

            card.speed = clampSpeed(card.speed + accel * dt);
            card.angle += card.speed * dt;
            card.element.style.setProperty('--jf-rack-angle', `${card.angle.toFixed(3)}deg`);
        });

        if (rail) {
            railJiggle *= 0.9;
            rail.style.setProperty('--jf-rack-jiggle', (Math.sin(t * 40) * railJiggle).toFixed(2));
        }

        window.requestAnimationFrame(frame);
    };

    const start = () => {
        if (!running) {
            running = true;
            last = performance.now();
            window.requestAnimationFrame(frame);
        }
    };

    const stop = () => {
        running = false;
    };

    /* Opening sequence: arm the hidden state, let the browser paint it, then
       release it so the CSS transitions actually run (adding both classes in
       one frame would skip straight to the end). Each card gets a hard
       sideways kick as it hooks onto its string. The timings mirror the
       transition delays in role-rack.css. */
    const INTRO_CARD_START = 950;
    const INTRO_STAGGER = 140;
    const INTRO_LENGTH = 2700;

    rack.classList.add('is-armed');
    rack.dataset.rackReady = '1';
    let introPlayed = false;

    const playIntro = () => {
        introPlayed = true;
        rack.classList.add('is-in');

        cards.forEach((card, index) => {
            window.setTimeout(() => {
                const side = index % 2 === 0 ? -1 : 1;
                card.angle = card.rest + side * (16 + Math.random() * 6);
                card.speed = side * -40;
                railJiggle = 2.5;
            }, INTRO_CARD_START + index * INTRO_STAGGER);
        });

        window.setTimeout(() => rack.classList.add('is-settled'), INTRO_LENGTH);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                stop();
                return;
            }

            start();

            if (!introPlayed) {
                // Two frames: one to paint the armed state, one to release it.
                window.requestAnimationFrame(() => window.requestAnimationFrame(playIntro));
            }
        });
    }, { threshold: 0.25 });

    observer.observe(rack);

    /* In the hero slideshow (hero-slides.js), the rack slides back in when its
       slide is shown again; the cards trail behind that sideways move. */
    rack.closest('[data-slide]')?.addEventListener('jf:slide-shown', (event) => {
        if (!introPlayed) {
            return;
        }

        const direction = event.detail?.direction ?? 1;

        cards.forEach((card, index) => {
            window.setTimeout(() => knock(card, -direction * (70 + index * 12)), 120 + index * 60);
        });
    });

    /* A cursor sweeping across the cards knocks each one it crosses, in the
       direction it was travelling and harder the faster it went. */
    let pointerX = null;

    // Listen on the whole hero so a swipe that enters the rack at speed still
    // registers the stretch it covered before reaching the first card.
    const swipeArea = rack.closest('.jf-hero') ?? rack;

    swipeArea.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse' && event.pointerType !== 'pen') {
            return;
        }

        if (pointerX !== null) {
            const dx = event.clientX - pointerX;

            cards.forEach((card) => {
                const bounds = card.element.getBoundingClientRect();
                const crossed = Math.min(pointerX, event.clientX) <= bounds.right
                    && Math.max(pointerX, event.clientX) >= bounds.left
                    && event.clientY >= bounds.top
                    && event.clientY <= bounds.bottom;

                if (crossed) {
                    // Pushing a hanging card sideways swings its bottom that way,
                    // which is a rotation the opposite way round its hook.
                    knock(card, -dx * SWIPE_FORCE);
                }
            });
        }

        pointerX = event.clientX;
    }, { passive: true });

    swipeArea.addEventListener('pointerleave', () => {
        pointerX = null;
    });

    /* On touch screens the rack scrolls sideways; every card lags behind the
       scroll like clothes on a moving rail. */
    if (list) {
        let scrollX = list.scrollLeft;

        list.addEventListener('scroll', () => {
            const dx = list.scrollLeft - scrollX;
            scrollX = list.scrollLeft;
            // Content sliding left leaves each card's bottom trailing right.
            cards.forEach((card) => knock(card, -dx * 0.5));
        }, { passive: true });
    }
});
