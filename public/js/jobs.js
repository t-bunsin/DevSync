document.addEventListener('DOMContentLoaded', () => {
    const dataElement = document.getElementById('jobs-data');

    if (!dataElement) {
        return;
    }

    const jobs = JSON.parse(dataElement.textContent);
    const jobsById = Object.fromEntries(jobs.map((job) => [job.id, job]));
    const list = document.getElementById('job-card-list');
    const cards = Array.from(list.querySelectorAll('.jf-job-card'));
    const detail = document.querySelector('.jf-detail');
    const detailPanel = document.getElementById('detail-panel-content');
    const tabs = Array.from(document.querySelectorAll('.jf-tab'));
    const filterChips = Array.from(document.querySelectorAll('.jf-search-chip'));
    const jobCount = document.getElementById('job-count');
    const noResults = document.getElementById('jf-no-results');
    const titleInput = document.getElementById('job-search-input');
    const locationInput = document.getElementById('job-location-input');
    const categorySelect = document.getElementById('job-category-select');
    const sortSelect = document.getElementById('job-sort-select');
    const searchButton = document.getElementById('job-search-button');
    const detailSaveButton = document.getElementById('detail-save-button');
    const detailApplyButton = document.getElementById('detail-apply-button');
    const applyDialog = document.getElementById('apply-dialog');
    const applyForm = document.getElementById('apply-form');
    const applySuccess = document.getElementById('apply-success');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const animationTimers = new WeakMap();

    const detailFields = {
        badge: document.getElementById('detail-badge'),
        title: document.getElementById('detail-title'),
        company: document.getElementById('detail-company'),
        location: document.getElementById('detail-location'),
        salary: document.getElementById('detail-salary'),
        posted: document.getElementById('detail-posted'),
        applicants: document.getElementById('detail-applicants'),
        sectionTitle: document.getElementById('detail-section-title'),
        sectionBody: document.getElementById('detail-section-body'),
        listTitle: document.getElementById('detail-list-title'),
        list: document.getElementById('detail-list'),
        facts: document.getElementById('detail-facts'),
        quickApply: document.getElementById('detail-quick-apply'),
        quickTitle: document.getElementById('detail-quick-title'),
        quickText: document.getElementById('detail-quick-text'),
    };

    let activeJobId = detailApplyButton.dataset.jobId;
    let activeTab = 'description';
    let filterTimer;
    let savedJobs = new Set();

    try {
        savedJobs = new Set(JSON.parse(localStorage.getItem('khworks:saved-jobs') || '[]'));
    } catch (error) {
        savedJobs = new Set();
    }

    const normalize = (value) => String(value || '')
        .toLowerCase()
        .replace(/[^a-z0-9\s+-]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    const replayAnimation = (element, className = 'is-refreshing', duration = 460) => {
        if (!element || prefersReducedMotion) {
            return;
        }

        window.clearTimeout(animationTimers.get(element));
        element.classList.remove(className);
        void element.offsetWidth;
        element.classList.add(className);
        animationTimers.set(element, window.setTimeout(() => {
            element.classList.remove(className);
            animationTimers.delete(element);
        }, duration));
    };

    const persistSavedJobs = () => {
        try {
            localStorage.setItem('khworks:saved-jobs', JSON.stringify([...savedJobs]));
        } catch (error) {
            // Saving jobs still works for this page if browser storage is unavailable.
        }
    };

    const updateSaveControl = (button, jobId) => {
        const isSaved = savedJobs.has(jobId);
        const jobTitle = jobsById[jobId]?.title || 'job';
        const icon = button.querySelector('i');

        button.classList.toggle('is-saved', isSaved);
        button.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
        button.setAttribute('aria-label', isSaved ? `Remove ${jobTitle} from saved jobs` : `Save ${jobTitle}`);
        icon?.classList.toggle('far', !isSaved);
        icon?.classList.toggle('fas', isSaved);
    };

    const syncSavedControls = () => {
        document.querySelectorAll('.jf-bookmark[data-job-id]').forEach((button) => {
            updateSaveControl(button, button.dataset.jobId);
        });
        updateSaveControl(detailSaveButton, activeJobId);
    };

    const toggleSavedJob = (jobId) => {
        if (savedJobs.has(jobId)) {
            savedJobs.delete(jobId);
        } else {
            savedJobs.add(jobId);
        }

        persistSavedJobs();
        syncSavedControls();
    };

    const renderFacts = (job) => {
        detailFields.facts.innerHTML = job.detail_items.map((item) => `
            <div class="jf-fact">
                <span>${item.label}</span>
                <strong>${item.value}</strong>
            </div>
        `).join('');
    };

    const renderTab = (job, animate = true) => {
        const panel = job.tabs[activeTab];

        detailFields.sectionTitle.textContent = panel.title;
        detailFields.sectionBody.textContent = panel.body;
        detailFields.listTitle.textContent = panel.list_title;
        detailFields.list.innerHTML = panel.list.map((item) => `<li>${item}</li>`).join('');
        detailFields.quickApply.hidden = activeTab !== 'description';

        if (animate) {
            replayAnimation(detailPanel);
        }
    };

    const renderJob = (jobId, options = {}) => {
        const job = jobsById[jobId];

        if (!job) {
            return;
        }

        activeJobId = jobId;
        detail.hidden = false;

        cards.forEach((card) => {
            const isActive = card.dataset.jobId === jobId;
            card.classList.toggle('is-active', isActive);
            card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        detailFields.badge.textContent = job.featured ? 'Featured role' : 'Open role';
        detailFields.title.textContent = job.title;
        detailFields.company.textContent = job.company;
        detailFields.location.textContent = job.location;
        detailFields.salary.textContent = job.salary;
        detailFields.posted.textContent = job.posted;
        detailFields.applicants.textContent = job.applicants;
        detailFields.quickTitle.textContent = job.quick_apply.title;
        detailFields.quickText.textContent = job.quick_apply.text;
        detailApplyButton.dataset.jobId = jobId;

        renderFacts(job);
        renderTab(job, false);
        syncSavedControls();

        if (options.animate !== false) {
            replayAnimation(detail);
        }

        if (options.scroll && window.innerWidth <= 960) {
            detail.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
        }
    };

    const activeQuickFilters = () => filterChips
        .filter((chip) => chip.classList.contains('is-active'))
        .map((chip) => chip.dataset.filter);

    const filterRules = {
        remote: (card) => card.dataset.mode.includes('remote') || card.dataset.location.includes('remote'),
        'full-time': (card) => card.dataset.type.includes('full-time'),
        'entry-level': (card) => card.dataset.experience.includes('1+') || card.dataset.experience.includes('entry'),
        design: (card) => card.dataset.department.includes('design') || card.dataset.title.includes('designer'),
    };

    const updateResultState = () => {
        const visibleCards = cards.filter((card) => !card.hidden);
        jobCount.textContent = visibleCards.length.toLocaleString();
        noResults.hidden = visibleCards.length !== 0;
        detail.hidden = visibleCards.length === 0;

        if (visibleCards.length && !visibleCards.some((card) => card.dataset.jobId === activeJobId)) {
            renderJob(visibleCards[0].dataset.jobId);
        }
    };

    const animateVisibleCards = () => {
        cards.filter((card) => !card.hidden).forEach((card, index) => {
            window.setTimeout(() => replayAnimation(card, 'is-entering', 400), index * 45);
        });
    };

    const filterJobs = ({ scroll = false } = {}) => {
        const titleQuery = normalize(titleInput.value);
        const locationQuery = normalize(locationInput.value);
        const categoryQuery = normalize(categorySelect.value);
        const quickFilters = activeQuickFilters();

        cards.forEach((card) => {
            const matchesTitle = !titleQuery
                || normalize(card.dataset.title).includes(titleQuery)
                || normalize(card.dataset.company).includes(titleQuery);
            const matchesLocation = !locationQuery || normalize(card.dataset.location).includes(locationQuery);
            const matchesCategory = categoryQuery === 'all' || normalize(card.dataset.department).includes(categoryQuery);
            const matchesQuickFilters = quickFilters.every((filter) => filterRules[filter]?.(card) ?? true);

            card.hidden = !(matchesTitle && matchesLocation && matchesCategory && matchesQuickFilters);
        });

        updateResultState();
        animateVisibleCards();

        if (scroll) {
            document.getElementById('jobs').scrollIntoView({
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
                block: 'start',
            });
        }
    };

    const sortJobs = (animate = true) => {
        const sortedCards = [...cards].sort((left, right) => {
            if (sortSelect.value === 'salary') {
                return Number(right.dataset.salaryRank) - Number(left.dataset.salaryRank);
            }
            if (sortSelect.value === 'featured') {
                return Number(right.dataset.featured) - Number(left.dataset.featured);
            }
            return Number(left.dataset.postedDays) - Number(right.dataset.postedDays);
        });

        sortedCards.forEach((card) => list.appendChild(card));
        if (animate) {
            animateVisibleCards();
        }
    };

    const resetSearch = () => {
        titleInput.value = '';
        locationInput.value = '';
        categorySelect.value = 'all';
        filterChips.forEach((chip) => {
            chip.classList.remove('is-active');
            chip.setAttribute('aria-pressed', 'false');
        });
        cards.forEach((card) => { card.hidden = false; });
        sortSelect.value = 'recent';
        sortJobs(false);
        updateResultState();
        animateVisibleCards();
    };

    const openApplyDialog = (jobId) => {
        const job = jobsById[jobId] || jobsById[activeJobId];

        document.getElementById('apply-job-title').textContent = job.title;
        applyForm.hidden = false;
        applySuccess.hidden = true;
        applyForm.reset();
        applyDialog.showModal();
    };

    cards.forEach((card) => {
        card.addEventListener('click', (event) => {
            if (!event.target.closest('button, a')) {
                renderJob(card.dataset.jobId);
            }
        });
        card.addEventListener('keydown', (event) => {
            if (event.target === card && (event.key === 'Enter' || event.key === ' ')) {
                event.preventDefault();
                renderJob(card.dataset.jobId, { scroll: window.innerWidth <= 960 });
            }
        });
    });

    document.querySelectorAll('.jf-bookmark[data-job-id]').forEach((button) => {
        button.addEventListener('keydown', (event) => event.stopPropagation());
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleSavedJob(button.dataset.jobId);
        });
    });

    detailSaveButton.addEventListener('click', () => toggleSavedJob(activeJobId));

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activeTab = tab.dataset.tab;
            tabs.forEach((button) => {
                const isActive = button === tab;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            renderTab(jobsById[activeJobId]);
        });
    });

    filterChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const isActive = chip.classList.toggle('is-active');
            chip.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            filterJobs();
        });
    });

    [titleInput, locationInput].forEach((input) => {
        input.addEventListener('input', () => {
            window.clearTimeout(filterTimer);
            filterTimer = window.setTimeout(() => filterJobs(), 180);
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                filterJobs({ scroll: true });
            }
        });
    });

    categorySelect.addEventListener('change', () => filterJobs());
    sortSelect.addEventListener('change', () => sortJobs());
    searchButton.addEventListener('click', () => filterJobs({ scroll: true }));
    document.querySelectorAll('#jf-reset-search, [data-reset-search]').forEach((button) => {
        button.addEventListener('click', resetSearch);
    });

    document.querySelectorAll('[data-view-job]').forEach((link) => {
        link.addEventListener('click', () => renderJob(link.dataset.viewJob));
    });

    document.querySelectorAll('.js-apply-job').forEach((button) => {
        button.addEventListener('click', () => openApplyDialog(button.dataset.jobId));
    });

    document.querySelector('[data-close-dialog]').addEventListener('click', () => applyDialog.close());
    applyDialog.addEventListener('click', (event) => {
        const bounds = applyDialog.getBoundingClientRect();
        const isOutside = event.clientX < bounds.left || event.clientX > bounds.right
            || event.clientY < bounds.top || event.clientY > bounds.bottom;
        if (isOutside) {
            applyDialog.close();
        }
    });

    applyForm.addEventListener('submit', (event) => {
        event.preventDefault();
        applyForm.hidden = true;
        applySuccess.hidden = false;
    });

    syncSavedControls();
    renderJob(activeJobId, { animate: false });
    sortJobs(false);
    updateResultState();
});
