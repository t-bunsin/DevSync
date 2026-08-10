@php
    // Must use the block form here. This file has another block further down,
    // and Blade's block matcher swallows the single-line form into it.
    $showHeading = $showHeading ?? true;
@endphp

<section class="jf-board" id="jobs" aria-labelledby="jobs-title">
    <div class="jf-shell">
        <div class="jf-board__heading">
            <div>
                {{-- When the page already carries an <h1>, the id moves here so
                     the section keeps an accessible name. --}}
                <span class="jf-kicker" @unless ($showHeading) id="jobs-title" @endunless>Fresh opportunities</span>
                @if ($showHeading)
                    <h2 id="jobs-title">Jobs picked for you</h2>
                @endif
                <p>Review the latest openings, preview each role, or open its full job page.</p>
            </div>

            <div class="jf-results__tools">
                <span class="jf-results__count" aria-live="polite"><strong id="job-count">{{ count($jobs) }}</strong> roles found</span>
                <label class="jf-sort">
                    <span class="visually-hidden">Sort jobs</span>
                    <select id="job-sort-select">
                        <option value="recent">Most recent</option>
                        <option value="salary">Highest salary</option>
                        <option value="featured">Featured first</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="jf-board__grid">
            <div class="jf-results">
                <div id="job-card-list" class="jf-results__list">
                    @foreach ($jobs as $job)
                        @php
                            $searchIndex = array_merge([
                                $job['title'], $job['company'], $job['location'], $job['department'],
                                $job['type'], $job['mode'], $job['experience'], $job['summary'],
                            ], $job['badges']);

                            foreach ($job['tabs'] as $tab) {
                                $searchIndex[] = $tab['body'];
                                $searchIndex = array_merge($searchIndex, $tab['list']);
                            }
                        @endphp
                        <article
                            class="jf-job-card{{ $job['featured'] ? ' is-featured' : '' }}{{ $job['highlighted'] ? ' is-active' : '' }}"
                            data-job-id="{{ $job['id'] }}"
                            data-title="{{ strtolower($job['title']) }}"
                            data-company="{{ strtolower($job['company']) }}"
                            data-location="{{ strtolower($job['location']) }}"
                            data-type="{{ strtolower($job['type']) }}"
                            data-department="{{ strtolower($job['department']) }}"
                            data-mode="{{ strtolower($job['mode']) }}"
                            data-experience="{{ strtolower($job['experience']) }}"
                            data-posted-days="{{ $job['posted_days'] }}"
                            data-featured="{{ $job['featured'] ? 1 : 0 }}"
                            data-salary-rank="{{ preg_replace('/[^0-9]/', '', $job['salary']) }}"
                            data-search="{{ strtolower(implode(' ', $searchIndex)) }}"
                        >
                            <div class="jf-job-card__topline">
                                <div class="jf-logo jf-logo--{{ $job['logo'] }}">
                                    @if ($job['logo'] === 'aba')
                                        <span>ABA</span><small>BANK</small>
                                    @elseif ($job['logo'] === 'tech')
                                        <i class="fas fa-wifi" aria-hidden="true"></i>
                                    @else
                                        <span>D</span>
                                    @endif
                                </div>

                                <div class="jf-job-card__status">
                                    @if ($job['featured'])
                                        <span class="jf-badge">Featured</span>
                                    @endif
                                    <button class="jf-bookmark" type="button" data-job-id="{{ $job['id'] }}"
                                        aria-label="Save {{ $job['title'] }}" aria-pressed="false">
                                        <i class="far fa-bookmark" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="jf-job-card__content">
                                <p class="jf-job-card__company">{{ $job['company'] }}</p>
                                <h3>
                                    <button class="jf-job-card__preview" type="button"
                                        data-preview-job="{{ $job['id'] }}"
                                        aria-controls="detail-panel-content"
                                        aria-pressed="{{ $job['highlighted'] ? 'true' : 'false' }}">
                                        {{ $job['title'] }}
                                    </button>
                                </h3>
                                <p class="jf-job-card__location"><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $job['location'] }}</p>
                                <p class="jf-job-card__summary">{{ $job['summary'] }}</p>
                            </div>

                            <div class="jf-job-card__chips">
                                @foreach ($job['badges'] as $badge)
                                    <span>{{ $badge }}</span>
                                @endforeach
                                <span>{{ $job['experience'] }}</span>
                            </div>

                            <div class="jf-job-card__footer">
                                <div><small>Salary</small><strong>{{ $job['short_salary'] }}</strong></div>
                                <a class="jf-job-card__page-link" href="{{ route('jobs.show', $job['id']) }}"
                                    aria-label="Open the full job page for {{ $job['title'] }}">
                                    Full job page <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="jf-no-results" id="jf-no-results" hidden>
                    <span class="jf-no-results__icon"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></span>
                    <h3>No matching jobs yet</h3>
                    <p>Try a broader title, another location, or remove one of your quick filters.</p>
                    <button type="button" data-reset-search>Reset search</button>
                </div>
            </div>

            <aside class="jf-detail" aria-label="Selected job details">
                <div class="jf-detail__hero">
                    <div class="jf-detail__topline">
                        <span class="jf-badge jf-badge--light" id="detail-badge">{{ $selectedJob['featured'] ? 'Featured role' : 'Open role' }}</span>
                        <button class="jf-detail__save" id="detail-save-button" type="button"
                            aria-label="Save selected job" aria-pressed="false">
                            <i class="far fa-bookmark" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="jf-detail__content">
                        <p id="detail-company">{{ $selectedJob['company'] }}</p>
                        <h2 id="detail-title">{{ $selectedJob['title'] }}</h2>
                        <div class="jf-detail__meta">
                            <span><i class="fas fa-location-dot" aria-hidden="true"></i> <span id="detail-location">{{ $selectedJob['location'] }}</span></span>
                            <span><i class="fas fa-clock" aria-hidden="true"></i> <span id="detail-posted">{{ $selectedJob['posted'] }}</span></span>
                        </div>
                    </div>

                    <div class="jf-detail__offer">
                        <div><small>Compensation</small><strong id="detail-salary">{{ $selectedJob['salary'] }}</strong></div>
                        <span id="detail-applicants">{{ $selectedJob['applicants'] }}</span>
                    </div>

                    <button class="jf-btn jf-btn--apply js-apply-job" id="detail-apply-button" type="button"
                        data-job-id="{{ $selectedJob['id'] }}">
                        Apply for this role <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                    <a class="jf-detail__page-link" id="detail-page-link"
                        href="{{ route('jobs.show', $selectedJob['id']) }}"
                        data-url-template="{{ route('jobs.show', '__JOB__') }}">
                        Open full job page <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="jf-tabs" role="tablist" aria-label="Job information">
                    <button class="jf-tab is-active" id="job-tab-description" type="button" data-tab="description"
                        role="tab" aria-selected="true" aria-controls="detail-panel-content" tabindex="0">Overview</button>
                    <button class="jf-tab" id="job-tab-requirements" type="button" data-tab="requirements"
                        role="tab" aria-selected="false" aria-controls="detail-panel-content" tabindex="-1">Requirements</button>
                    <button class="jf-tab" id="job-tab-company" type="button" data-tab="company"
                        role="tab" aria-selected="false" aria-controls="detail-panel-content" tabindex="-1">Company</button>
                </div>

                <div class="jf-detail__body">
                    <div class="jf-detail__article" id="detail-panel-content" role="tabpanel"
                        aria-labelledby="job-tab-description" aria-live="polite" tabindex="0">
                        <h3 id="detail-section-title">{{ $selectedJob['tabs']['description']['title'] }}</h3>
                        <p id="detail-section-body">{{ $selectedJob['tabs']['description']['body'] }}</p>
                        <h4 id="detail-list-title">{{ $selectedJob['tabs']['description']['list_title'] }}</h4>
                        <ul id="detail-list" class="jf-detail__list">
                            @foreach ($selectedJob['tabs']['description']['list'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="jf-detail__sidebar">
                        <div class="jf-side-card">
                            <span>Role details</span>
                            <div id="detail-facts" class="jf-facts">
                                @foreach ($selectedJob['detail_items'] as $item)
                                    <div class="jf-fact"><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></div>
                                @endforeach
                            </div>
                        </div>

                        <div id="detail-quick-apply" class="jf-quick-apply">
                            <i class="fas fa-bolt" aria-hidden="true"></i>
                            <div>
                                <strong id="detail-quick-title">{{ $selectedJob['quick_apply']['title'] }}</strong>
                                <p id="detail-quick-text">{{ $selectedJob['quick_apply']['text'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<dialog class="jf-apply-dialog" id="apply-dialog" aria-labelledby="apply-dialog-title">
    <button class="jf-dialog__close" type="button" data-close-dialog aria-label="Close application form">
        <i class="fas fa-xmark" aria-hidden="true"></i>
    </button>
    <span class="jf-dialog__icon"><i class="fas fa-paper-plane" aria-hidden="true"></i></span>
    <p class="jf-kicker">Quick application</p>
    <h2 id="apply-dialog-title">Apply for <span id="apply-job-title">this role</span></h2>
    <p>Leave your details and the hiring team can follow up. This demo does not send data to an employer.</p>
    <form id="apply-form">
        <label>Full name<input name="name" type="text" autocomplete="name" required></label>
        <label>Email address<input name="email" type="email" autocomplete="email" required></label>
        <button class="jf-btn jf-btn--apply" type="submit">
            Create application draft <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </button>
    </form>
    <p class="jf-dialog__success" id="apply-success" role="status" hidden>
        <i class="fas fa-circle-check" aria-hidden="true"></i> Your application draft is ready.
    </p>
</dialog>

<script type="application/json" id="jobs-data">@json($jobs)</script>
