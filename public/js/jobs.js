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
    const typeSelect = explorer.querySelector('#job-mode-select, #job-type-select');
    const sortSelect = explorer.querySelector('#job-sort-select');
    const searchButton = explorer.querySelector('#job-search-button');
    const viewButtons = Array.from(explorer.querySelectorAll('[data-job-view]'));
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
        companyVerified: explorer.querySelector('#detail-company-verified'),
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
        sortSelect,
        detailSaveButton,
        detailApplyButton,
        detailPageLink,
        jobsSection,
        ...Object.values(detailFields),
    ];

    // Guests see a link to the apply gate where members see this dialog, so
    // every part of it is optional from here on.
    const hasApplyDialog = Boolean(applyDialog && applyForm && applySuccess
        && applyJobTitle && closeDialogButton);

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

    const urlFilterControls = () => [
        ['q', titleInput],
        ['type', typeSelect],
        ['location', locationInput],
        ['department', categorySelect],
    ];

    const setSelectValue = (select, value) => {
        if (!select || !value) {
            return;
        }

        // Ignore hand-edited URLs so a stray value cannot blank the select.
        if (Array.from(select.options).some((option) => option.value === value)) {
            select.value = value;
        }
    };

    const applyStateFromUrl = () => {
        const params = new URLSearchParams(window.location.search);
        const query = params.get('q');

        if (query && titleInput) {
            titleInput.value = query;
        }

        setSelectValue(typeSelect, params.get('type'));
        setSelectValue(locationInput, params.get('location'));
        setSelectValue(categorySelect, params.get('department'));
    };

    const syncUrl = () => {
        const params = new URLSearchParams();

        urlFilterControls().forEach(([key, control]) => {
            const value = control?.value.trim();

            if (value && value !== 'all') {
                params.set(key, value);
            }
        });

        const query = params.toString();
        window.history.replaceState(null, '', query
            ? `${window.location.pathname}?${query}`
            : window.location.pathname);
    };

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
        // Falls back rather than throwing: a panel key that no longer exists
        // used to leave the previous tab's content on screen.
        const panel = job.tabs[activeTab] || job.tabs.description;

        if (!panel) {
            return;
        }

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

        // Verification belongs to the employer, so it follows the selected job.
        if (detailFields.companyVerified) {
            detailFields.companyVerified.hidden = !job.company_verified;
        }
        detailFields.location.textContent = job.location;
        detailFields.salary.textContent = job.salary;
        detailFields.posted.textContent = job.posted;
        detailFields.applicants.textContent = job.applicants;
        detailFields.quickTitle.textContent = job.quick_apply.title;
        detailFields.quickText.textContent = job.quick_apply.text;
        detailApplyButton.dataset.jobId = jobId;
        detailPageLink.href = detailPageLink.dataset.urlTemplate.replace('__JOB__', encodeURIComponent(jobId));

        // Only the guest apply link carries a template; the member button posts
        // nowhere and opens the dialog instead.
        if (detailApplyButton.dataset.urlTemplate) {
            detailApplyButton.href = detailApplyButton.dataset.urlTemplate
                .replace('__JOB__', encodeURIComponent(jobId));
        }

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
        const titleQuery = normalize(titleInput?.value);
        const locationQuery = normalize(locationInput?.value || 'all');
        const categoryQuery = normalize(categorySelect?.value || 'all');
        const typeQuery = normalize(typeSelect?.value || 'all');
        const quickFilters = activeQuickFilters();

        cards.forEach((card) => {
            const matchesTitle = !titleQuery || normalize(card.dataset.search).includes(titleQuery);
            const matchesLocation = locationQuery === 'all' || !locationQuery
                || normalize(card.dataset.location).includes(locationQuery);
            const matchesCategory = categoryQuery === 'all' || normalize(card.dataset.department).includes(categoryQuery);
            const matchesType = typeQuery === 'all' || normalize(card.dataset.type).includes(typeQuery);
            const matchesQuickFilters = quickFilters.every((filter) => filterRules[filter]?.(card) ?? true);

            card.hidden = !(matchesTitle && matchesLocation && matchesCategory && matchesType && matchesQuickFilters);
        });

        updateResultState();
        animateVisibleCards();
        syncUrl();

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
        if (titleInput) {
            titleInput.value = '';
        }
        if (locationInput) {
            locationInput.value = 'all';
        }
        if (categorySelect) {
            categorySelect.value = 'all';
        }
        if (typeSelect) {
            typeSelect.value = 'all';
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
        syncUrl();
    };

    const openApplyDialog = (jobId) => {
        if (!hasApplyDialog) {
            return;
        }

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

    [titleInput].filter(Boolean).forEach((input) => {
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

    categorySelect?.addEventListener('change', () => filterJobs());
    locationInput?.addEventListener('change', () => filterJobs());
    typeSelect?.addEventListener('change', () => filterJobs());
    sortSelect.addEventListener('change', () => sortJobs());
    searchButton?.addEventListener('click', () => filterJobs({ scroll: true }));
    viewButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const isListView = button.dataset.jobView === 'list';
            explorer.classList.toggle('is-list-view', isListView);
            viewButtons.forEach((control) => {
                const isActive = control === button;
                control.classList.toggle('is-primary', isActive);
                control.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        });
    });
    explorer.querySelectorAll('[data-reset-search]').forEach((button) => {
        button.addEventListener('click', resetSearch);
    });

    document.querySelectorAll('[data-view-job]').forEach((link) => {
        link.addEventListener('click', () => renderJob(link.dataset.viewJob));
    });

    explorer.querySelectorAll('.js-apply-job').forEach((button) => {
        button.addEventListener('click', () => openApplyDialog(button.dataset.jobId));
    });

    if (hasApplyDialog) {
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
    }

    let resizeFrame;
    window.addEventListener('resize', () => {
        window.cancelAnimationFrame(resizeFrame);
        resizeFrame = window.requestAnimationFrame(() => positionDetail(activeJobId));
    });

    syncSavedControls();
    renderJob(activeJobId, { animate: false });
    sortJobs(false);
    applyStateFromUrl();
    filterJobs();
});

/* Hero spotlight slideshow (top 3 roles) */
document.addEventListener('DOMContentLoaded', () => {
    const spotlight = document.querySelector('[data-spotlight]');

    if (!spotlight) {
        return;
    }

    const slides = Array.from(spotlight.querySelectorAll('[data-spotlight-slide]'));
    const dots = Array.from(spotlight.querySelectorAll('[data-spotlight-dot]'));
    const controls = spotlight.querySelector('.jf-spotlight__controls');

    if (slides.length < 2) {
        controls?.remove();
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const interval = 6000;
    let current = slides.findIndex((slide) => slide.classList.contains('is-active'));
    let timer;

    if (current < 0) {
        current = 0;
    }

    const showSlide = (index) => {
        current = (index + slides.length) % slides.length;

        slides.forEach((slide, position) => {
            const isActive = position === current;
            slide.classList.toggle('is-active', isActive);
            slide.toggleAttribute('aria-hidden', !isActive);
        });

        dots.forEach((dot, position) => {
            const isActive = position === current;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    };

    const stopAutoplay = () => window.clearInterval(timer);

    const startAutoplay = () => {
        stopAutoplay();

        if (prefersReducedMotion) {
            return;
        }

        timer = window.setInterval(() => showSlide(current + 1), interval);
    };

    const goTo = (index) => {
        showSlide(index);
        startAutoplay();
    };

    spotlight.querySelector('[data-spotlight-prev]')?.addEventListener('click', () => goTo(current - 1));
    spotlight.querySelector('[data-spotlight-next]')?.addEventListener('click', () => goTo(current + 1));
    dots.forEach((dot, position) => dot.addEventListener('click', () => goTo(position)));

    spotlight.addEventListener('mouseenter', stopAutoplay);
    spotlight.addEventListener('mouseleave', startAutoplay);
    spotlight.addEventListener('focusin', stopAutoplay);
    spotlight.addEventListener('focusout', (event) => {
        if (!spotlight.contains(event.relatedTarget)) {
            startAutoplay();
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoplay();
        } else {
            startAutoplay();
        }
    });

    showSlide(current);
    startAutoplay();
});
