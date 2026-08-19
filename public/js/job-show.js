(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        const dataElement = document.getElementById('job-page-data');

        if (!dataElement) {
            return;
        }

        const job = JSON.parse(dataElement.textContent);
        const tabs = Array.from(document.querySelectorAll('[data-job-page-tab]'));
        const panel = document.getElementById('job-page-panel');
        const sectionTitle = document.getElementById('job-page-section-title');
        const sectionBody = document.getElementById('job-page-section-body');
        const listTitle = document.getElementById('job-page-list-title');
        const list = document.getElementById('job-page-list');
        const companyHeader = document.getElementById('job-page-company-header');
        const companyProfile = document.getElementById('job-page-company-profile');
        const mainBlock = document.getElementById('job-page-main-block');
        const offerBlock = document.getElementById('job-page-offer');
        const kicker = document.getElementById('job-page-kicker');
        const kickerLabels = {
            description: 'Role overview',
            job_description: 'Job description',
            requirements: 'What we look for',
        };
        const saveButton = document.getElementById('job-page-save-button');
        const applyButton = document.getElementById('job-page-apply-button');
        const applyDialog = document.getElementById('job-page-apply-dialog');
        const applyForm = document.getElementById('job-page-apply-form');
        const applySuccess = document.getElementById('job-page-apply-success');
        const shareButton = document.getElementById('job-page-share-button');
        const shareStatus = document.getElementById('job-page-share-status');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let animationTimer;
        let savedJobs = new Set();

        try {
            savedJobs = new Set(JSON.parse(localStorage.getItem('khworks:saved-jobs') || '[]'));
        } catch (error) {
            savedJobs = new Set();
        }

        const persistSavedJobs = () => {
            try {
                localStorage.setItem('khworks:saved-jobs', JSON.stringify([...savedJobs]));
            } catch (error) {
                // Saving remains available for the current page if storage is blocked.
            }
        };

        const updateSaveButton = () => {
            const isSaved = savedJobs.has(job.id);
            const icon = saveButton?.querySelector('i');
            const label = saveButton?.querySelector('span');

            saveButton?.classList.toggle('is-saved', isSaved);
            saveButton?.setAttribute('aria-pressed', String(isSaved));
            saveButton?.setAttribute('aria-label', isSaved ? `Remove ${job.title} from saved jobs` : `Save ${job.title}`);
            icon?.classList.toggle('far', !isSaved);
            icon?.classList.toggle('fas', isSaved);

            if (label) {
                label.textContent = isSaved ? 'Job saved' : 'Save job';
            }
        };

        const renderTab = (tab) => {
            const tabName = tab.dataset.jobPageTab;
            const tabContent = job.tabs[tabName];

            // The Company tab has no authored panel of its own — it shows the
            // employer profile — so it must not bail out here.
            const isCompanyTab = tabName === 'company';

            if (!tabContent && !isCompanyTab) {
                return;
            }

            tabs.forEach((item) => {
                const isActive = item === tab;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-selected', String(isActive));
                item.tabIndex = isActive ? 0 : -1;
            });

            // The banner and employer profile belong to the Company tab only.
            if (companyHeader) {
                companyHeader.hidden = !isCompanyTab;
            }

            if (companyProfile) {
                companyProfile.hidden = !isCompanyTab;
            }

            if (mainBlock) {
                mainBlock.hidden = isCompanyTab;
            }

            if (offerBlock) {
                offerBlock.hidden = tabName !== 'requirements';
            }

            if (!tabContent) {
                return;
            }

            if (kicker && kickerLabels[tabName]) {
                kicker.textContent = kickerLabels[tabName];
            }

            sectionTitle.textContent = tabContent.title;
            sectionBody.textContent = tabContent.body;
            listTitle.textContent = tabContent.list_title;
            list.replaceChildren(...tabContent.list.map((text) => {
                const item = document.createElement('li');
                item.textContent = text;
                return item;
            }));
            panel.setAttribute('aria-labelledby', tab.id);

            if (!prefersReducedMotion) {
                window.clearTimeout(animationTimer);
                panel.classList.remove('is-refreshing');
                void panel.offsetWidth;
                panel.classList.add('is-refreshing');
                animationTimer = window.setTimeout(() => panel.classList.remove('is-refreshing'), 320);
            }
        };

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => renderTab(tab));
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
                    tabs[nextIndex].focus();
                    renderTab(tabs[nextIndex]);
                }
            });
        });

        saveButton?.addEventListener('click', () => {
            if (savedJobs.has(job.id)) {
                savedJobs.delete(job.id);
            } else {
                savedJobs.add(job.id);
            }

            persistSavedJobs();
            updateSaveButton();
        });

        // Guests get a link to the apply gate instead of this dialog, so none
        // of the form markup is on the page for them.
        const openApplyDialog = () => {
            if (!applyDialog || !applyForm) {
                return;
            }

            applyForm.hidden = false;
            applySuccess.hidden = true;
            // Restores the account name and email the fields ship with.
            applyForm.reset();
            applyDialog.showModal();
        };

        applyButton?.addEventListener('click', openApplyDialog);

        // Coming back from registration with ?apply=1 opens the form straight away.
        if (applyButton?.hasAttribute('data-job-page-auto-open')) {
            openApplyDialog();
        }
        document.querySelector('[data-job-page-close]')?.addEventListener('click', () => applyDialog.close());
        applyDialog?.addEventListener('click', (event) => {
            const bounds = applyDialog.getBoundingClientRect();
            const isOutside = event.clientX < bounds.left || event.clientX > bounds.right
                || event.clientY < bounds.top || event.clientY > bounds.bottom;

            if (isOutside) {
                applyDialog.close();
            }
        });

        applyForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            applyForm.hidden = true;
            applySuccess.hidden = false;
        });

        const copyPageUrl = async () => {
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(window.location.href);
                } else {
                    const input = document.createElement('input');
                    input.value = window.location.href;
                    input.setAttribute('readonly', '');
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    input.remove();
                }

                shareStatus.textContent = 'Job link copied.';
                shareButton.querySelector('span').textContent = 'Link copied';
                window.setTimeout(() => {
                    shareStatus.textContent = '';
                    shareButton.querySelector('span').textContent = 'Copy job link';
                }, 2200);
            } catch (error) {
                shareStatus.textContent = 'Copy failed. Use your browser address bar.';
            }
        };

        shareButton?.addEventListener('click', copyPageUrl);
        updateSaveButton();
    });
})();
