@php
    $jobs = config('jobs_demo');
    $selectedJobId = 'software-engineer';
    $selectedJob = collect($jobs)->firstWhere('id', $selectedJobId) ?? $jobs[0];
@endphp

@extends('layouts.master')

@section('title', 'KH-WORKS | Find Your Next Opportunity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
@endpush

@section('content')
    <section class="jf-hero" aria-labelledby="hero-title">
        <div class="jf-hero__glow jf-hero__glow--one" aria-hidden="true"></div>
        <div class="jf-hero__glow jf-hero__glow--two" aria-hidden="true"></div>

        <div class="jf-shell jf-hero__inner">
            <div class="jf-hero__copy">
                <span class="jf-hero__eyebrow">
                    <i class="fas fa-sparkles" aria-hidden="true"></i>
                    Cambodia’s career marketplace
                </span>
                <h1 id="hero-title">Find work that moves <span>you forward.</span></h1>
                <p>Explore verified opportunities from trusted teams in Cambodia and remote-first companies across Southeast Asia.</p>

                <div class="jf-hero__actions">
                    <a class="jf-btn jf-btn--accent" href="#job-search">
                        Explore jobs
                        <i class="fas fa-arrow-down" aria-hidden="true"></i>
                    </a>
                    <a class="jf-btn jf-btn--hero-ghost" href="{{ route('register') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Post a job
                    </a>
                </div>

                <dl class="jf-hero__metrics" aria-label="Platform statistics">
                    <div><dt>1,200+</dt><dd>Active roles</dd></div>
                    <div><dt>320+</dt><dd>Hiring teams</dd></div>
                    <div><dt>3 days</dt><dd>Avg. response</dd></div>
                </dl>
            </div>

            <aside class="jf-spotlight" aria-label="Featured opportunity">
                <div class="jf-spotlight__topline">
                    <span><i class="fas fa-bolt" aria-hidden="true"></i> Featured this week</span>
                    <span class="jf-spotlight__status">Actively hiring</span>
                </div>

                <div class="jf-spotlight__company">
                    <div class="jf-logo jf-logo--tech"><i class="fas fa-wifi" aria-hidden="true"></i></div>
                    <div>
                        <span>Tech Corp</span>
                        <strong>Software Engineer</strong>
                    </div>
                </div>

                <div class="jf-spotlight__meta">
                    <span><i class="fas fa-location-dot" aria-hidden="true"></i> Remote</span>
                    <span><i class="fas fa-clock" aria-hidden="true"></i> Full-time</span>
                    <span><i class="fas fa-code" aria-hidden="true"></i> Engineering</span>
                </div>

                <div class="jf-spotlight__footer">
                    <div><small>Salary range</small><strong>$80k–$120k</strong></div>
                    <a href="#jobs" data-view-job="software-engineer">View role <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </aside>
        </div>
    </section>

    <section class="jf-job-ticker" aria-label="Popular job categories">
        <p class="visually-hidden">Popular job categories include software engineering, product design, retail banking, data and analytics, customer support, marketing, finance, and remote work.</p>
        <div class="jf-job-ticker__viewport" aria-hidden="true">
            <div class="jf-job-ticker__track">
                @for ($tickerCopy = 0; $tickerCopy < 2; $tickerCopy++)
                    <div class="jf-job-ticker__group">
                        <span>Software Engineering</span>
                        <span>Product Design</span>
                        <span>Retail Banking</span>
                        <span>Data &amp; Analytics</span>
                        <span>Customer Support</span>
                        <span>Digital Marketing</span>
                        <span>Finance</span>
                        <span>Remote Roles</span>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <section class="jf-search jf-shell" id="job-search" aria-labelledby="search-title">
        <div class="jf-search__panel">
            <div class="jf-search__header">
                <div>
                    <span class="jf-kicker">Find your fit</span>
                    <h2 id="search-title">Search opportunities</h2>
                </div>
                <p>Use a few details to narrow the list. You can combine multiple filters.</p>
            </div>

            <div class="jf-search__grid">
                <label class="jf-search__field jf-search__field--wide">
                    <span class="jf-search__label">Role or company</span>
                    <span class="jf-search__control">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input id="job-search-input" type="search" placeholder="e.g. Software engineer" autocomplete="off">
                    </span>
                </label>

                <label class="jf-search__field">
                    <span class="jf-search__label">Location</span>
                    <span class="jf-search__control">
                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                        <input id="job-location-input" type="search" placeholder="City or remote" autocomplete="off">
                    </span>
                </label>

                <label class="jf-search__field">
                    <span class="jf-search__label">Department</span>
                    <span class="jf-search__control jf-search__control--select">
                        <i class="fas fa-layer-group" aria-hidden="true"></i>
                        <select id="job-category-select" aria-label="Department">
                            <option value="all">All departments</option>
                            <option value="engineering">Engineering</option>
                            <option value="product design">Product Design</option>
                            <option value="retail banking">Retail Banking</option>
                        </select>
                        <i class="fas fa-chevron-down jf-search__chevron" aria-hidden="true"></i>
                    </span>
                </label>

                <button id="job-search-button" class="jf-btn jf-btn--search" type="button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    Search jobs
                </button>
            </div>

            <div class="jf-search__footer">
                <div class="jf-search__filters" aria-label="Quick filters">
                    <span>Popular:</span>
                    <button class="jf-search-chip" type="button" data-filter="remote" aria-pressed="false">Remote</button>
                    <button class="jf-search-chip" type="button" data-filter="full-time" aria-pressed="false">Full-time</button>
                    <button class="jf-search-chip" type="button" data-filter="entry-level" aria-pressed="false">Entry level</button>
                    <button class="jf-search-chip" type="button" data-filter="design" aria-pressed="false">Design</button>
                </div>
                <button class="jf-search__clear" id="jf-reset-search" type="button">
                    <i class="fas fa-rotate-left" aria-hidden="true"></i> Clear filters
                </button>
            </div>
        </div>
    </section>

    <section class="jf-trusted" id="companies" aria-labelledby="companies-title">
        <div class="jf-shell jf-trusted__inner">
            <p id="companies-title">Opportunities from teams shaping Cambodia</p>
            <div class="jf-trusted__logos" aria-label="Featured employers">
                <span><b>ABA</b> Bank</span>
                <span><i class="fas fa-signal" aria-hidden="true"></i> Smart</span>
                <span><i class="fas fa-circle-nodes" aria-hidden="true"></i> Wing</span>
                <span><i class="fas fa-building" aria-hidden="true"></i> Chip Mong</span>
                <span><i class="fas fa-shield-heart" aria-hidden="true"></i> AIA</span>
            </div>
        </div>
    </section>

    <section class="jf-board" id="jobs" aria-labelledby="jobs-title">
        <div class="jf-shell">
            <div class="jf-board__heading">
                <div>
                    <span class="jf-kicker">Fresh opportunities</span>
                    <h2 id="jobs-title">Jobs picked for you</h2>
                    <p>Review the latest openings and select a role to see the full details.</p>
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
                                tabindex="0"
                                role="button"
                                aria-pressed="{{ $job['highlighted'] ? 'true' : 'false' }}"
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
                                        <button class="jf-bookmark" type="button" data-job-id="{{ $job['id'] }}" aria-label="Save {{ $job['title'] }}" aria-pressed="false">
                                            <i class="far fa-bookmark" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="jf-job-card__content">
                                    <p class="jf-job-card__company">{{ $job['company'] }}</p>
                                    <h3>{{ $job['title'] }}</h3>
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
                                    <span>View details <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
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
                            <button class="jf-detail__save" id="detail-save-button" type="button" aria-label="Save selected job" aria-pressed="false">
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

                        <button class="jf-btn jf-btn--apply js-apply-job" id="detail-apply-button" type="button" data-job-id="{{ $selectedJob['id'] }}">
                            Apply for this role <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="jf-tabs" role="tablist" aria-label="Job information">
                        <button class="jf-tab is-active" type="button" data-tab="description" role="tab" aria-selected="true">Overview</button>
                        <button class="jf-tab" type="button" data-tab="requirements" role="tab" aria-selected="false">Requirements</button>
                        <button class="jf-tab" type="button" data-tab="company" role="tab" aria-selected="false">Company</button>
                    </div>

                    <div class="jf-detail__body">
                        <div class="jf-detail__article" id="detail-panel-content" role="tabpanel" aria-live="polite">
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
                                <div><strong id="detail-quick-title">{{ $selectedJob['quick_apply']['title'] }}</strong><p id="detail-quick-text">{{ $selectedJob['quick_apply']['text'] }}</p></div>
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
            <button class="jf-btn jf-btn--apply" type="submit">Create application draft <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
        </form>
        <p class="jf-dialog__success" id="apply-success" role="status" hidden><i class="fas fa-circle-check" aria-hidden="true"></i> Your application draft is ready.</p>
    </dialog>

    <script type="application/json" id="jobs-data">@json($jobs)</script>
@endsection

@push('scripts')
    <script src="{{ asset('js/jobs.js') }}"></script>
@endpush
