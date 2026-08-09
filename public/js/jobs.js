document.addEventListener('DOMContentLoaded', () => {
    const explorer = document.querySelector('[data-jobs-explorer]');
    const dataElement = explorer?.querySelector('#jobs-data');

    if (!explorer || !dataElement) {
        return;
    }

    let jobs = [];

    try {
        jobs = JSON.parse(dataElement.textContent);
    } catch (error) {
        return;
    }

    if (!Array.isArray(jobs) || jobs.length === 0) {
        return;
    }

    const jobsById = Object.fromEntries(jobs.map((job) => [job.id, job]));
    const list = explorer.querySelector('#job-card-list');
    const cards = Array.from(explorer.querySelectorAll('.jf-job-card'));
    const previewButtons = Array.from(explorer.querySelectorAll('[data-preview-job]'));
    const boardGrid = explorer.querySelector('.jf-board__grid');
    const detail = explorer.querySelector('.jf-detail');
    const detailPanel = explorer.querySelector('#detail-panel-content');
    const tabs = Array.from(explorer.querySelectorAll('.jf-tab'));
    const filterChips = Array.from(explorer.querySelectorAll('.jf-search-chip'));
    const jobCount = explorer.querySelector('#job-count');
    const noResults = explorer.querySelector('#jf-no-results');
    const titleInput = explorer.querySelector('#job-search-input');
    const locationInput = explorer.querySelector('#job-location-input');
    const categorySelect = explorer.querySelector('#job-category-select');
    const modeSelect = explorer.querySelector('#job-mode-select');
    const sortSelect = explorer.querySelector('#job-sort-select');
    const searchButton = explorer.querySelector('#job-search-button');
    const detailSaveButton = explorer.querySelector('#detail-save-button');
    const detailApplyButton = explorer.querySelector('#detail-apply-button');
    const detailPageLink = explorer.querySelector('#detail-page-link');
    const applyDialog = explorer.querySelector('#apply-dialog');
    const applyForm = explorer.querySelector('#apply-form');
    const applySuccess = explorer.querySelector('#apply-success');
    const applyJobTitle = explorer.querySelector('#apply-job-title');
    const closeDialogButton = explorer.querySelector('[data-close-dialog]');
    const jobsSection = explorer.querySelector('#jobs');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const animationTimers = new WeakMap();

    const detailFields = {
        badge: explorer.querySelector('#detail-badge'),
        title: explorer.querySelector('#detail-title'),
        company: explorer.querySelector('#detail-company'),
        location: explorer.querySelector('#detail-location'),
        salary: explorer.querySelector('#detail-salary'),
        posted: explorer.querySelector('#detail-posted'),
        applicants: explorer.querySelector('#detail-applicants'),
        sectionTitle: explorer.querySelector('#detail-section-title'),
        sectionBody: explorer.querySelector('#detail-section-body'),
        listTitle: explorer.querySelector('#detail-list-title'),
        list: explorer.querySelector('#detail-list'),
        facts: explorer.querySelector('#detail-facts'),
        quickApply: explorer.querySelector('#detail-quick-apply'),
        quickTitle: explorer.querySelector('#detail-quick-title'),
        quickText: explorer.querySelector('#detail-quick-text'),
    };

    const requiredElements = [
        list,
        boardGrid,
        detail,
        detailPanel,
        jobCount,
        noResults,
        titleInput,
        locationInput,
        categorySelect,
        sortSelect,
        searchButton,
        detailSaveButton,
        detailApplyButton,
        detailPageLink,
        applyDialog,
        applyForm,
        applySuccess,
        applyJobTitle,
        closeDialogButton,
        jobsSection,
        ...Object.values(detailFields),
    ];

    if (cards.length === 0 || previewButtons.length !== cards.length || tabs.length === 0
        || requiredElements.some((element) => !element)) {
        return;
    }

    let activeJobId = jobsById[detailApplyButton.dataset.jobId]
        ? detailApplyButton.dataset.jobId
        : jobs[0].id;
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
        explorer.querySelectorAll('.jf-bookmark[data-job-id]').forEach((button) => {
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

    const positionDetail = (jobId) => {
        const selectedCard = cards.find((card) => card.dataset.jobId === jobId);

        if (window.innerWidth <= 960 && selectedCard && !selectedCard.hidden) {
            selectedCard.insertAdjacentElement('afterend', detail);
        } else if (detail.parentElement !== boardGrid) {
            boardGrid.appendChild(detail);
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
            card.querySelector('[data-preview-job]')?.setAttribute('aria-pressed', isActive ? 'true' : 'false');
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
        detailPageLink.href = detailPageLink.dataset.urlTemplate.replace('__JOB__', encodeURIComponent(jobId));
        positionDetail(jobId);

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
        const modeQuery = normalize(modeSelect?.value || 'all');
        const quickFilters = activeQuickFilters();

        cards.forEach((card) => {
            const matchesTitle = !titleQuery
                || normalize(card.dataset.title).includes(titleQuery)
                || normalize(card.dataset.company).includes(titleQuery);
            const matchesLocation = !locationQuery || normalize(card.dataset.location).includes(locationQuery);
            const matchesCategory = categoryQuery === 'all' || normalize(card.dataset.department).includes(categoryQuery);
            const matchesMode = modeQuery === 'all' || normalize(card.dataset.mode).includes(modeQuery);
            const matchesQuickFilters = quickFilters.every((filter) => filterRules[filter]?.(card) ?? true);

            card.hidden = !(matchesTitle && matchesLocation && matchesCategory && matchesMode && matchesQuickFilters);
        });

        updateResultState();
        animateVisibleCards();

        if (scroll) {
            jobsSection.scrollIntoView({
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
        positionDetail(activeJobId);
        if (animate) {
            animateVisibleCards();
        }
    };

    const resetSearch = () => {
        titleInput.value = '';
        locationInput.value = '';
        categorySelect.value = 'all';
        if (modeSelect) {
            modeSelect.value = 'all';
        }
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

        applyJobTitle.textContent = job.title;
        applyForm.hidden = false;
        applySuccess.hidden = true;
        applyForm.reset();
        applyDialog.showModal();
    };

    cards.forEach((card) => {
        card.addEventListener('click', (event) => {
            if (!event.target.closest('button, a')) {
                renderJob(card.dataset.jobId, { scroll: window.innerWidth <= 960 });
            }
        });
    });

    previewButtons.forEach((button) => {
        button.addEventListener('click', () => {
            renderJob(button.dataset.previewJob, { scroll: window.innerWidth <= 960 });
        });
    });

    explorer.querySelectorAll('.jf-bookmark[data-job-id]').forEach((button) => {
        button.addEventListener('keydown', (event) => event.stopPropagation());
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleSavedJob(button.dataset.jobId);
        });
    });

    detailSaveButton.addEventListener('click', () => toggleSavedJob(activeJobId));

    const activateTab = (tab, { focus = false } = {}) => {
        activeTab = tab.dataset.tab;
        tabs.forEach((button) => {
            const isActive = button === tab;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            button.setAttribute('tabindex', isActive ? '0' : '-1');
        });
        detailPanel.setAttribute('aria-labelledby', tab.id);
        renderTab(jobsById[activeJobId]);

        if (focus) {
            tab.focus();
        }
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateTab(tab));
        tab.addEventListener('keydown', (event) => {
            let nextIndex = null;

            if (event.key === 'ArrowRight') {
                nextIndex = (index + 1) % tabs.length;
            } else if (event.key === 'ArrowLeft') {
                nextIndex = (index - 1 + tabs.length) % tabs.length;
            } else if (event.key === 'Home') {
                nextIndex = 0;
            } else if (event.key === 'End') {
                nextIndex = tabs.length - 1;
            }

            if (nextIndex !== null) {
                event.preventDefault();
                activateTab(tabs[nextIndex], { focus: true });
            }
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
    modeSelect?.addEventListener('change', () => filterJobs());
    sortSelect.addEventListener('change', () => sortJobs());
    searchButton.addEventListener('click', () => filterJobs({ scroll: true }));
    explorer.querySelectorAll('#jf-reset-search, [data-reset-search]').forEach((button) => {
        button.addEventListener('click', resetSearch);
    });

    document.querySelectorAll('[data-view-job]').forEach((link) => {
        link.addEventListener('click', () => renderJob(link.dataset.viewJob));
    });

    explorer.querySelectorAll('.js-apply-job').forEach((button) => {
        button.addEventListener('click', () => openApplyDialog(button.dataset.jobId));
    });

    closeDialogButton.addEventListener('click', () => applyDialog.close());
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

    let resizeFrame;
    window.addEventListener('resize', () => {
        window.cancelAnimationFrame(resizeFrame);
        resizeFrame = window.requestAnimationFrame(() => positionDetail(activeJobId));
    });

    syncSavedControls();
    renderJob(activeJobId, { animate: false });
    sortJobs(false);
    updateResultState();
});
