/*
 * Back-office dialogs.
 *
 * A button marked [data-bo-modal-open="<id>"] opens the <dialog> with that id;
 * anything inside marked [data-bo-modal-close] closes it, as does a click on
 * the backdrop and the Esc key the element handles itself.
 *
 * The dialog holds a real form that posts and reloads the page, so nothing here
 * has to keep state — closing is only ever "put it away without saving".
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-bo-modal-open]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const dialog = document.getElementById(trigger.dataset.boModalOpen);

                if (!dialog) {
                    return;
                }

                // showModal() gives the focus trap and the Esc key for free;
                // show() would leave the page behind it interactive.
                dialog.showModal();
            });
        });

        document.querySelectorAll('dialog.kh-bo__modal').forEach((dialog) => {
            dialog.querySelectorAll('[data-bo-modal-close]').forEach((button) => {
                button.addEventListener('click', () => dialog.close());
            });

            // A click outside the panel. The dialog element itself fills the
            // viewport, so the backdrop reports as a hit on the dialog.
            dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) {
                    return;
                }

                const bounds = dialog.getBoundingClientRect();
                const isOutside = event.clientX < bounds.left || event.clientX > bounds.right
                    || event.clientY < bounds.top || event.clientY > bounds.bottom;

                if (isOutside) {
                    dialog.close();
                }
            });
        });
    });
}());
