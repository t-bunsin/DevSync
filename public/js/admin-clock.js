/*
 * Topnav clock — "Tue 18 Aug  10:56:36 PM".
 *
 * Reads the viewer's own clock rather than the server's: APP_TIMEZONE is UTC,
 * so a server-rendered time would be wrong for anyone not sitting in UTC.
 *
 * The element ships with [hidden] and is revealed on the first draw, so a
 * browser with JS off shows no empty pill.
 *
 * Ticks are scheduled to the next whole second instead of a flat 1000ms
 * interval: setInterval drifts, which makes the seconds visibly skip a number
 * or repeat one every few minutes.
 */
(function () {
    'use strict';

    var DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var clock = document.getElementById('khAdminClock');

    if (!clock) {
        return;
    }

    var dateSlot = clock.querySelector('[data-clock-date]');
    var timeSlot = clock.querySelector('[data-clock-time]');

    function pad(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function draw() {
        var now = new Date();
        var hours24 = now.getHours();
        var hours12 = hours24 % 12 || 12;

        dateSlot.textContent = DAYS[now.getDay()] + ' ' + pad(now.getDate()) + ' ' + MONTHS[now.getMonth()];
        timeSlot.textContent = hours12 + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds())
            + ' ' + (hours24 < 12 ? 'AM' : 'PM');

        // Local time, so no trailing Z — that would claim UTC.
        clock.setAttribute('datetime',
            now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate())
            + 'T' + pad(hours24) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds()));

        if (clock.hidden) {
            clock.hidden = false;
        }

        return now;
    }

    function tick() {
        var now = draw();

        setTimeout(tick, 1000 - now.getMilliseconds());
    }

    tick();

    // Background tabs get throttled, so catch up the moment one is looked at.
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            draw();
        }
    });
})();
