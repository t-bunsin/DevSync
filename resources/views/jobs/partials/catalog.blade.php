@php
    // Must use the block form here. This file has another block further down,
    // and Blade's block matcher swallows the single-line form into it.
    $showHeading = $showHeading ?? true;

    // Interactive is the explorer: a column of cards driving the detail panel
    // beside them. Off, the board is a plain grid of cards that each link
    // straight to their job page, with no controls that need scripting.
    $interactive = $interactive ?? true;
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
                @if ($interactive)
                    <label class="jf-sort">
                        <span class="visually-hidden">Sort jobs</span>
                        <select id="job-sort-select">
                            <option value="recent">Most recent</option>
                            <option value="salary">Highest salary</option>
                            <option value="featured">Featured first</option>
                        </select>
                    </label>
                @endif
            </div>
        </div>

        <div @class(['jf-board__grid', 'jf-board__grid--cards' => ! $interactive])>
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

                            [$salaryAmount, $salaryUnit] = array_pad(
                                array_map('trim', explode('/', $job['short_salary'], 2)), 2, null
                            );
                            $salaryUnit = $salaryUnit ? '/' . $salaryUnit : null;

                            // Badges always arrive as type, then work mode, with the
                            // experience appended last, so the icons follow position.
                            $chipIcons = ['fa-briefcase', 'fa-building', 'fa-calendar-days'];
                            $chips = array_merge($job['badges'], [$job['experience']]);

                            $rateLabel = match (true) {
                                str_contains((string) $salaryUnit, 'hour') => 'Hourly rate',
                                str_contains((string) $salaryUnit, 'year') => 'Annual salary',
                                str_contains((string) $salaryUnit, 'month') => 'Monthly salary',
                                default => 'Compensation',
                            };
                        @endphp
                        <article
                            class="jf-job-card{{ $job['featured'] ? ' is-featured' : '' }}{{ $interactive && $job['highlighted'] ? ' is-active' : '' }}"
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
                                <div @class([
                                    'jf-logo',
                                    'jf-logo--' . $job['logo'],
                                    'jf-logo--photo' => ! empty($job['company_logo_url']),
                                ])>
                                    @if (! empty($job['company_logo_url']))
                                        <img src="{{ $job['company_logo_url'] }}" alt="">
                                    @elseif ($job['logo'] === 'aba')
                                        <span>ABA</span><small>BANK</small>
                                    @elseif ($job['logo'] === 'tech')
                                        <i class="fas fa-wifi" aria-hidden="true"></i>
                                    @else
                                        <span>D</span>
                                    @endif
                                </div>

                                <div class="jf-job-card__identity">
                                    <p class="jf-job-card__company">
                                        {{ $job['company'] }}
                                        @if (! empty($job['company_verified']))
                                            <x-verified-badge :show-label="false" :size="14" label="Verified employer" />
                                        @endif
                                    </p>
                                    <p class="jf-job-card__location">
                                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                                        {{ $job['location'] }}
                                    </p>
                                </div>

                                @if ($interactive)
                                    <div class="jf-job-card__status">
                                        <button class="jf-bookmark" type="button" data-job-id="{{ $job['id'] }}"
                                            aria-label="Save {{ $job['title'] }}" aria-pressed="false">
                                            <i class="far fa-bookmark" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <div class="jf-job-card__chips">
                                @foreach ($chips as $index => $chip)
                                    <span>
                                        <i class="fas {{ $chipIcons[$index % count($chipIcons)] }}" aria-hidden="true"></i>
                                        {{ $chip }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="jf-job-card__content">
                                <h3>
                                    @if ($interactive)
                                        <button class="jf-job-card__preview" type="button"
                                            data-preview-job="{{ $job['id'] }}"
                                            aria-controls="detail-panel-content"
                                            aria-pressed="{{ $job['highlighted'] ? 'true' : 'false' }}">
                                            {{ $job['title'] }}
                                        </button>
                                    @else
                                        {{-- No panel to preview into, so the title is the job page. --}}
                                        <a class="jf-job-card__preview" href="{{ route('jobs.show', $job['id']) }}">
                                            {{ $job['title'] }}
                                        </a>
                                    @endif
                                </h3>
                                <p class="jf-job-card__summary">{{ $job['summary'] }}</p>
                            </div>

                            <div class="jf-job-card__footer">
                                <div class="jf-job-card__rate">
                                    <span class="jf-job-card__rate-icon" aria-hidden="true">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                    <div>
                                        <small>{{ $rateLabel }}</small>
                                        <p class="jf-job-card__salary">
                                            <strong>{{ $salaryAmount }}</strong>@if ($salaryUnit)<span>{{ $salaryUnit }}</span>@endif
                                        </p>
                                    </div>
                                </div>

                                <div class="jf-job-card__actions">
                                    @if ($job['featured'])
                                        <span class="jf-badge">
                                            <i class="fas fa-star" aria-hidden="true"></i>
                                            Featured
                                        </span>
                                    @endif
                                    {{-- A plain link, not a scripted button: the gate route sends
                                         guests to register and members straight to the form, so the
                                         card still applies even where the explorer script is absent. --}}
                                    <a class="jf-job-card__apply" href="{{ route('jobs.apply', $job['id']) }}"
                                        aria-label="Apply for {{ $job['title'] }} at {{ $job['company'] }}">
                                        <i class="fas fa-briefcase" aria-hidden="true"></i>
                                        <span>Apply now</span>
                                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                    </a>
                                </div>
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

            @if ($interactive)
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
                            <p class="jf-detail__company">
                                <span id="detail-company">{{ $selectedJob['company'] }}</span>
                                <span id="detail-company-verified"
                                    @unless (! empty($selectedJob['company_verified'])) hidden @endunless>
                                    <x-verified-badge :show-label="false" :size="15" label="Verified employer" />
                                </span>
                            </p>
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

                        @auth
                            @if (! empty($selectedJob['already_applied']))
                                <button class="jf-btn jf-btn--apply is-applied" id="detail-apply-button" type="button"
                                    data-job-id="{{ $selectedJob['id'] }}" disabled>
                                    <i class="fas fa-check" aria-hidden="true"></i> Already applied
                                </button>
                            @else
                                <button class="jf-btn jf-btn--apply js-apply-job" id="detail-apply-button" type="button"
                                    data-job-id="{{ $selectedJob['id'] }}">
                                    Apply for this role <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </button>
                            @endif
                        @else
                            {{-- Applying needs an account; the gate route sorts that out. --}}
                            <a class="jf-btn jf-btn--apply" id="detail-apply-button"
                                href="{{ route('jobs.apply', $selectedJob['id']) }}"
                                data-job-id="{{ $selectedJob['id'] }}"
                                data-url-template="{{ route('jobs.apply', '__JOB__') }}">
                                Register to apply <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        @endauth
                        <a class="jf-detail__page-link" id="detail-page-link"
                            href="{{ route('jobs.show', $selectedJob['id']) }}"
                            data-url-template="{{ route('jobs.show', '__JOB__') }}">
                            Open full job page <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </a>
                    </div>

                    <div class="jf-tabs" role="tablist" aria-label="Job information">
                        <button class="jf-tab is-active" id="job-tab-description" type="button" data-tab="description"
                            role="tab" aria-selected="true" aria-controls="detail-panel-content" tabindex="0">Overview</button>
                        <button class="jf-tab" id="job-tab-job-details" type="button" data-tab="job_details"
                            role="tab" aria-selected="false" aria-controls="detail-panel-content" tabindex="-1">Job details</button>
                        <button class="jf-tab" id="job-tab-company" type="button" data-tab="company"
                            role="tab" aria-selected="false" aria-controls="detail-panel-content" tabindex="-1">Company</button>
                    </div>

                    <div class="jf-detail__body">
                        <div class="jf-detail__article" id="detail-panel-content" role="tabpanel"
                            aria-labelledby="job-tab-description" aria-live="polite" tabindex="0">
                            <div id="detail-text-view">
                                <h3 id="detail-section-title">{{ $selectedJob['tabs']['description']['title'] }}</h3>
                                <p id="detail-section-body">{{ $selectedJob['tabs']['description']['body'] }}</p>
                                <h4 id="detail-list-title">{{ $selectedJob['tabs']['description']['list_title'] }}</h4>
                                <ul id="detail-list" class="jf-detail__list">
                                    @foreach ($selectedJob['tabs']['description']['list'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>

                                {{-- Filled in only for the combined Job details tab, which
                                     stacks Requirements and Job description together. --}}
                                <div class="jf-detail__secondary" id="detail-secondary-group" hidden>
                                    <h4 id="detail-secondary-title"></h4>
                                    <p id="detail-secondary-body"></p>
                                    <h4 id="detail-secondary-list-title"></h4>
                                    <ul id="detail-secondary-list" class="jf-detail__list"></ul>
                                </div>
                            </div>

                            {{-- Company tab content — built client-side by jobs.js from
                                 the same employer fields the full job page's Company tab
                                 uses (logo, details, address, profile sections). --}}
                            <div id="detail-company-view" hidden></div>
                        </div>

                        <div class="jf-detail__sidebar">
                            <div class="jf-side-card" id="detail-role-card">
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
            @endif
        </div>
    </div>
</section>

@if ($interactive)
@auth
<dialog class="jf-apply-dialog" id="apply-dialog" aria-labelledby="apply-dialog-title">
    <button class="jf-dialog__close" type="button" data-close-dialog aria-label="Close application form">
        <i class="fas fa-xmark" aria-hidden="true"></i>
    </button>
    <span class="jf-dialog__icon"><i class="fas fa-paper-plane" aria-hidden="true"></i></span>
    <p class="jf-kicker">Quick application</p>
    <h2 id="apply-dialog-title">Apply for <span id="apply-job-title">this role</span></h2>
    <p>
        Leave your details and the hiring team receives your application.
        @if ($ownResume)
            Your resume on file is attached automatically.
        @else
            A CV is required — attach one below.
        @endif
    </p>
    {{-- The explorer applies to whichever role is selected, so the action is
         filled in from the template when the dialog opens. --}}
    <form id="apply-form" method="POST" data-apply-template="{{ url('/jobs') }}/:job/apply">
        @csrf
        <label>Full name<input name="name" type="text" autocomplete="name"
                value="{{ auth()->user()->displayName() }}" required></label>
        <label>Email address<input name="email" type="email" autocomplete="email"
                value="{{ auth()->user()->email }}" required></label>
        <label><span>Phone <span class="jf-hint">(optional)</span></span><input name="phone" type="tel" autocomplete="tel"
                value="{{ auth()->user()->phone }}"></label>
        <label><span>Message <span class="jf-hint">(optional)</span></span><textarea name="message" rows="3"
                placeholder="Why you are a good fit for this role."></textarea></label>
        {{-- A CV is required to apply. Candidates who already built a resume
             in the dashboard have one attached for them; everyone else
             uploads a file here, or follows the link and builds one. --}}
        @if ($ownResume)
            <p class="jf-dialog__note">
                <i class="fas fa-file-lines" aria-hidden="true"></i>
                Your resume <strong>{{ $ownResume->full_name }}</strong> is attached automatically.
            </p>
            <label><span>Attach a different CV <span class="jf-hint">(optional)</span></span><input name="cv" type="file"
                    accept=".pdf,.doc,.docx"></label>
        @else
            <label><span>Your CV <span class="jf-hint">(PDF or Word, up to 5 MB)</span></span><input name="cv" type="file"
                    accept=".pdf,.doc,.docx" required></label>
            <p class="jf-dialog__note">
                <i class="fas fa-circle-info" aria-hidden="true"></i>
                No CV to hand? <a href="{{ route('resumes.create') }}">Build one in your dashboard</a>
                and it is attached to every application you send.
            </p>
        @endif
        <button class="jf-btn jf-btn--apply" type="submit">
            Send application <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </button>
    </form>
    <p class="jf-dialog__error" id="apply-error" role="alert" hidden></p>
    <p class="jf-dialog__success" id="apply-success" role="status" hidden>
        <i class="fas fa-circle-check" aria-hidden="true"></i> Your application was sent.
    </p>
</dialog>
@endauth

<script type="application/json" id="jobs-data">@json($jobs)</script>
@endif
