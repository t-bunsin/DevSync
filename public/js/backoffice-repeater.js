/*
 * Repeating form sections (resume work history, education, and so on).
 *
 * Each section posts as section[index][field]. The index only has to be unique
 * and ordered — Laravel reindexes on the way in and the controller reindexes on
 * the way out — so adding a row clones the first one and renumbers every row
 * from the top, which keeps the numbering right after a removal too.
 *
 * The last row is never removed: an empty row is the section's "add something
 * here" affordance, and a section with no rows at all cannot be filled back in
 * without a page reload.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-bo-repeater]').forEach(setupRepeater);
    });

    function setupRepeater(root) {
        const list = root.querySelector('[data-bo-rows]');
        const addButton = root.querySelector('[data-bo-add]');

        if (!list || !addButton) {
            return;
        }

        addButton.addEventListener('click', () => {
            const rows = list.querySelectorAll('[data-bo-row]');
            const clone = rows[0].cloneNode(true);

            clearRow(clone);
            list.appendChild(clone);
            renumber(list);

            const firstField = clone.querySelector('input, select, textarea');
            if (firstField) {
                firstField.focus();
            }
        });

        // Delegated, so rows added after load are covered without rebinding.
        list.addEventListener('click', (event) => {
            const button = event.target.closest('[data-bo-remove]');

            if (!button || !list.contains(button)) {
                return;
            }

            const row = button.closest('[data-bo-row]');
            const rows = list.querySelectorAll('[data-bo-row]');

            if (!row) {
                return;
            }

            if (rows.length === 1) {
                clearRow(row);
            } else {
                row.remove();
            }

            renumber(list);
        });

        renumber(list);
    }

    function clearRow(row) {
        row.querySelectorAll('input, textarea').forEach((field) => {
            field.value = '';
            field.classList.remove('is-invalid');
        });

        row.querySelectorAll('select').forEach((field) => {
            field.selectedIndex = 0;
            field.classList.remove('is-invalid');
        });
    }

    /*
     * Rewrites the [n] segment of every field name to match the row's position,
     * and updates the visible row number. Only the first bracketed group is
     * touched, so section[0][bullets] keeps its field name.
     */
    function renumber(list) {
        list.querySelectorAll('[data-bo-row]').forEach((row, index) => {
            row.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace(/\[\d+\]/, '[' + index + ']');
            });

            const number = row.querySelector('[data-bo-number]');
            if (number) {
                number.textContent = String(index + 1);
            }
        });
    }
})();
