/*
 * The Activity center bell.
 *
 * Keeps the dropdown current without a page reload: the feed is asked for the
 * unread count and the rendered list every POLL_MS, and opening the dropdown
 * marks everything read. The server renders the same partial the layout does,
 * so this only ever swaps markup in — it never builds any.
 *
 * Polling, not a socket: this ships with no broker and no daemon to keep
 * running. Swapping in a broadcast later means calling render() from the event
 * instead of from the timer.
 */
(function () {
    'use strict';

    const POLL_MS = 15000;

    document.addEventListener('DOMContentLoaded', () => {
        const root = document.querySelector('[data-kh-notifications]');

        if (!root) {
            return;
        }

        const badge = root.querySelector('[data-kh-badge]');
        const list = root.querySelector('[data-kh-notification-items]');
        const toggle = root.querySelector('.dropdown-toggle');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        const setBadge = (count) => {
            if (!badge) {
                return;
            }

            badge.textContent = count > 9 ? '9+' : String(count);
            badge.hidden = count < 1;
        };

        const render = async () => {
            // A background tab is not worth a request; the next poll after it
            // is focused again catches up.
            if (document.hidden) {
                return;
            }

            try {
                const response = await fetch(root.dataset.feedUrl, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();

                if (typeof payload.html === 'string' && list && !root.querySelector('.show')) {
                    // Left alone while the dropdown is open, so the list cannot
                    // shift under the pointer mid-click.
                    list.innerHTML = payload.html;
                    window.feather?.replace();
                }

                setBadge(Number(payload.unread) || 0);
            } catch (error) {
                // Offline or a hiccup: the next tick tries again.
            }
        };

        const markRead = async () => {
            if (!csrf || badge?.hidden) {
                return;
            }

            setBadge(0);

            try {
                await fetch(root.dataset.readUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            } catch (error) {
                // The badge is already cleared for this page; the next poll
                // restores the true count if the request never landed.
            }
        };

        toggle?.addEventListener('click', () => {
            if (toggle.getAttribute('aria-expanded') !== 'true') {
                markRead();
            }
        });

        window.setInterval(render, POLL_MS);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                render();
            }
        });
    });
}());
