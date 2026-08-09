(function () {
    'use strict';

    const chartRanges = {
        '7d': {
            total: '842',
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            line: 'M0,214 C55,210 72,178 126,182 C180,186 192,145 252,151 C312,158 326,111 382,119 C438,127 456,82 508,91 C568,102 586,56 638,70 C692,84 717,37 760,43',
            response: 'M0,228 C70,218 84,211 126,213 C180,216 204,192 252,199 C312,207 334,175 382,181 C438,189 463,155 508,164 C568,174 591,137 638,149 C690,161 724,116 760,124',
        },
        '30d': {
            total: '3,486',
            labels: ['1', '5', '10', '15', '20', '25', '30'],
            line: 'M0,220 C70,206 82,198 126,202 C184,207 202,169 252,177 C306,186 330,142 382,148 C434,155 462,112 508,119 C562,128 594,86 638,94 C690,102 718,58 760,66',
            response: 'M0,232 C60,228 88,216 126,220 C180,225 208,198 252,203 C310,211 338,178 382,185 C440,193 467,157 508,165 C570,176 597,134 638,144 C694,154 724,118 760,126',
        },
    };

    function renderIcons() {
        if (window.feather && typeof window.feather.replace === 'function') {
            window.feather.replace({
                width: 18,
                height: 18,
                'stroke-width': 2,
            });
        }
    }

    function initFlashMessages() {
        document.querySelectorAll('[data-dismiss-flash]').forEach((button) => {
            button.addEventListener('click', () => {
                const message = button.closest('.kh-flash');

                if (!message) {
                    return;
                }

                message.classList.add('is-leaving');
                window.setTimeout(() => message.remove(), 180);
            });
        });
    }

    function initChartRanges() {
        const buttons = document.querySelectorAll('[data-chart-range]');
        const area = document.getElementById('chart-area');
        const line = document.getElementById('chart-line');
        const response = document.getElementById('chart-response');
        const total = document.getElementById('chart-total');
        const labels = document.querySelectorAll('.kh-chart__labels span');

        if (!buttons.length || !area || !line || !response || !total) {
            return;
        }

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const range = chartRanges[button.dataset.chartRange];

                if (!range || button.classList.contains('is-active')) {
                    return;
                }

                buttons.forEach((item) => {
                    const selected = item === button;
                    item.classList.toggle('is-active', selected);
                    item.setAttribute('aria-pressed', String(selected));
                });

                line.setAttribute('d', range.line);
                area.setAttribute('d', `${range.line} L760,240 L0,240 Z`);
                response.setAttribute('d', range.response);
                total.textContent = range.total;

                labels.forEach((label, index) => {
                    label.textContent = range.labels[index] || '';
                });

                const chart = document.querySelector('.kh-chart');
                chart?.classList.remove('is-updated');
                window.requestAnimationFrame(() => chart?.classList.add('is-updated'));
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderIcons();
        initFlashMessages();
        initChartRanges();
    });
})();
