@extends('layouts.master')

@section('title', $job['title'] . ' at ' . $job['company'] . ' | KH-WORKS')
@section('meta-description', $job['summary'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/job-show.css') }}">
@endpush

@section('content')
    <section class="jf-job-page" aria-labelledby="job-page-title">
        <div class="jf-shell">
            <nav class="jf-job-page__breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                <a href="{{ route('jobs.index') }}">Jobs</a>
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                <span aria-current="page">{{ $job['title'] }}</span>
            </nav>

            <div class="jf-job-page__hero">
                <div class="jf-job-page__hero-copy">
                    <div class="jf-job-page__company-row">
                        {{-- An uploaded company logo wins; otherwise the keyword artwork. --}}
                        <div @class([
                            'jf-logo',
                            'jf-job-page__logo',
                            'jf-logo--' . $job['logo'],
                            'jf-logo--photo' => ! empty($job['company_logo_url']),
                        ]) aria-hidden="true">
                            @if (! empty($job['company_logo_url']))
                                <img src="{{ $job['company_logo_url'] }}" alt="">
                            @elseif ($job['logo'] === 'aba')
                                <span>ABA</span><small>BANK</small>
                            @elseif ($job['logo'] === 'tech')
                                <i class="fas fa-wifi"></i>
                            @else
                                <span>D</span>
                            @endif
                        </div>
                        <div>
                            <span class="jf-job-page__eyebrow">{{ $job['department'] }}</span>
                            <strong class="jf-job-page__company">
                                {{ $job['company'] }}
                                @if (! empty($job['company_verified']))
                                    <x-verified-badge :show-label="false" :size="17"
                                        label="Verified employer" title="Verified employer" />
                                @endif
                            </strong>
                        </div>
                    </div>

                    <div class="jf-job-page__badges">
                        @if ($job['featured'])
                            <span><i class="fas fa-bolt" aria-hidden="true"></i> Featured role</span>
                        @endif
                        @foreach ($job['badges'] as $badge)
                            <span>{{ $badge }}</span>
                        @endforeach
                    </div>

                    <h1 id="job-page-title">{{ $job['title'] }}</h1>
                    <p class="jf-job-page__summary">{{ $job['summary'] }}</p>

                    <div class="jf-job-page__meta" aria-label="Job summary">
                        <span><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $job['location'] }}</span>
                        <span><i class="fas fa-clock" aria-hidden="true"></i> {{ $job['posted'] }}</span>
                        <span><i class="fas fa-chart-simple" aria-hidden="true"></i> {{ $job['experience'] }}</span>
                    </div>
                </div>

                <aside class="jf-job-page__apply-card" aria-label="Application summary">
                    <span class="jf-job-page__apply-label">Compensation</span>
                    <strong class="jf-job-page__salary">{{ $job['salary'] }}</strong>

                    <div class="jf-job-page__offer-meta">
                        <div><small>Closing</small><span>{{ $job['deadline'] }}</span></div>
                        <div><small>Interest</small><span>{{ $job['applicants'] }}</span></div>
                    </div>

                    @auth
                        @if (! empty($job['already_applied']))
                            <button class="jf-btn jf-btn--apply is-applied" type="button" disabled>
                                <i class="fas fa-check" aria-hidden="true"></i> Already applied
                            </button>
                        @else
                            <button class="jf-btn jf-btn--apply" id="job-page-apply-button" type="button"
                                @if (request()->boolean('apply')) data-job-page-auto-open @endif>
                                Apply for this role <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        @endif
                    @else
                        {{-- Applying needs an account; browsing does not. --}}
                        <a class="jf-btn jf-btn--apply" href="{{ route('jobs.apply', $job['id']) }}">
                            Register to apply <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @endauth
                    <button class="jf-job-page__save" id="job-page-save-button" type="button"
                        data-job-id="{{ $job['id'] }}" aria-label="Save {{ $job['title'] }}" aria-pressed="false">
                        <i class="far fa-bookmark" aria-hidden="true"></i>
                        <span>Save job</span>
                    </button>
                    @auth
                        <small class="jf-job-page__demo-note">Application drafts stay in this demo and are not sent automatically.</small>
                    @else
                        <small class="jf-job-page__demo-note">
                            A free account is required to apply — it takes under two minutes.
                            Already registered? <a href="{{ route('login') }}">Sign in</a>.
                        </small>
                    @endauth
                </aside>
            </div>

            <div class="jf-job-page__layout">
                <article class="jf-job-page__content-card">
                    <div class="jf-tabs" role="tablist" aria-label="Job information">
                        <button class="jf-tab is-active" id="job-tab-description" type="button"
                            data-job-page-tab="description" role="tab" aria-selected="true"
                            aria-controls="job-page-panel">Overview</button>
                        <button class="jf-tab" id="job-tab-requirements" type="button"
                            data-job-page-tab="requirements" role="tab" aria-selected="false"
                            aria-controls="job-page-panel">Job Details</button>
                        <button class="jf-tab" id="job-tab-company" type="button"
                            data-job-page-tab="company" role="tab" aria-selected="false"
                            aria-controls="job-page-panel">Company</button>
                    </div>

                    <div class="jf-job-page__article jf-detail__article" id="job-page-panel"
                        role="tabpanel" aria-live="polite" aria-labelledby="job-tab-description">
                        {{-- Company profile banner. Server-rendered and revealed
                             by job-show.js only while the Company tab is open. --}}
                        <div class="jf-company-banner" id="job-page-company-header" hidden>
                            <div @class(['jf-company-banner__cover', 'jf-company-banner__cover--photo' => ! empty($job['company_cover_url'])])
                                @if (! empty($job['company_cover_url'])) style="background-image: url('{{ $job['company_cover_url'] }}')" @endif
                                role="img" aria-label="{{ $job['company'] }} cover image">
                            </div>

                            <div class="jf-company-banner__bar">
                                <span class="jf-company-banner__logo" aria-hidden="true">
                                    @if (! empty($job['company_logo_url']))
                                        <img src="{{ $job['company_logo_url'] }}" alt="">
                                    @else
                                        <i class="fas fa-building"></i>
                                    @endif
                                </span>

                                <h3 class="jf-company-banner__name">
                                    {{ $job['company'] }}
                                    @if (! empty($job['company_verified']))
                                        <x-verified-badge :show-label="false" :size="19" label="Verified employer" />
                                    @endif
                                </h3>
                            </div>
                        </div>

                        {{-- Employer profile, same visibility rule as the banner. --}}
                        <div class="jf-company-profile" id="job-page-company-profile" hidden>
                            @if (! empty($job['company_details']) || ! empty($job['company_address']))
                                <div class="jf-cprofile-meta">
                                    @foreach ($job['company_details'] as $label => $value)
                                        <div class="jf-cprofile-meta__item">
                                            <dt>{{ $label }}</dt>
                                            <dd>{{ $value }}</dd>
                                        </div>
                                    @endforeach

                                    @if (! empty($job['company_address']))
                                        <div class="jf-cprofile-meta__item jf-cprofile-meta__item--wide">
                                            <dt>Address</dt>
                                            <dd>
                                                {{ $job['company_address'] }}
                                                @if (! empty($job['company_website']))
                                                    <a class="jf-cprofile-meta__link" href="{{ $job['company_website'] }}"
                                                        target="_blank" rel="noopener noreferrer">
                                                        {{ preg_replace('#^https?://#', '', $job['company_website']) }}
                                                        <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                                    </a>
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- <details> rather than scripted panels: collapsing is
                                 native, keyboard-accessible, and works without JS. --}}
                            @foreach ($job['company_sections'] ?? [] as $index => $section)
                                <details class="jf-cprofile-item" @if ($index < 2) open @endif>
                                    <summary class="jf-cprofile-item__summary">
                                        <span class="jf-cprofile-item__icon" aria-hidden="true"><i class="fas {{ $section['icon'] }}"></i></span>
                                        <span class="jf-cprofile-item__title">{{ $section['title'] }}</span>
                                        <i class="fas fa-chevron-down jf-cprofile-item__chevron" aria-hidden="true"></i>
                                    </summary>

                                    <div class="jf-cprofile-item__body">
                                        {{-- Employer-authored prose: blank lines start a new
                                             paragraph; a block whose lines all start with "- "
                                             renders as a bulleted list instead. --}}
                                        @foreach (preg_split('/\R{2,}/', trim($section['body'])) as $paragraph)
                                            @php
                                                $lines = array_filter(array_map('trim', preg_split('/\R/', trim($paragraph))), fn ($line) => $line !== '');
                                                $isList = count($lines) > 0 && collect($lines)->every(fn ($line) => str_starts_with($line, '- '));
                                            @endphp
                                            @if ($isList)
                                                <ul class="jf-cprofile-item__list">
                                                    @foreach ($lines as $line)
                                                        <li>{{ substr($line, 2) }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p>{!! nl2br(e($paragraph)) !!}</p>
                                            @endif
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>

                        {{-- The JS-driven panel: Overview and the combined Job
                             description & Requirements tab both render through it.
                             Hidden only on the Company tab, which shows the
                             employer profile above. --}}
                        <div id="job-page-main-block">
                            <span class="jf-kicker" id="job-page-kicker">Role overview</span>
                            <h2 id="job-page-section-title">{{ $job['tabs']['description']['title'] }}</h2>
                            <p id="job-page-section-body">{{ $job['tabs']['description']['body'] }}</p>
                            <h3 id="job-page-list-title">{{ $job['tabs']['description']['list_title'] }}</h3>
                            <ul class="jf-detail__list" id="job-page-list">
                                @foreach ($job['tabs']['description']['list'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>

                            {{-- Populated only on the combined tab, where the former
                                 Job description panel renders beneath Requirements. --}}
                            <div class="jf-job-page__secondary-panel" id="job-page-secondary-block" hidden>
                                <h2 id="job-page-section-title-2"></h2>
                                <p id="job-page-section-body-2"></p>
                                <h3 id="job-page-list-title-2"></h3>
                                <ul class="jf-detail__list" id="job-page-list-2"></ul>
                            </div>
                        </div>

                        {{-- What the employer offers. Static, shown on the
                             Requirements tab only. --}}
                        @if (! empty($job['offer']))
                            <div class="jf-offer" id="job-page-offer" hidden>
                                <h2 class="jf-offer__title">What we can offer</h2>
                                <div class="jf-offer__grid">
                                    @foreach ($job['offer'] as $column)
                                        <section class="jf-offer__col">
                                            <h3>
                                                <span class="jf-offer__icon" aria-hidden="true"><i class="fas {{ $column['icon'] }}"></i></span>
                                                {{ $column['title'] }}
                                            </h3>
                                            <ul>
                                                @foreach ($column['items'] as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        </section>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </article>

                <aside class="jf-job-page__sidebar" aria-label="More job information">
                    <div class="jf-side-card jf-job-page__side-card">
                        <span>Role details</span>
                        <div class="jf-facts">
                            @foreach ($job['detail_items'] as $item)
                                <div class="jf-fact">
                                    <span>{{ $item['label'] }}</span>
                                    <strong>{{ $item['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="jf-quick-apply jf-job-page__quick-note">
                        <i class="fas fa-bolt" aria-hidden="true"></i>
                        <div>
                            <strong>{{ $job['quick_apply']['title'] }}</strong>
                            <p>{{ $job['quick_apply']['text'] }}</p>
                        </div>
                    </div>

                    <div class="jf-job-page__share-card">
                        <span class="jf-job-page__share-icon" aria-hidden="true">
                            <i class="fas fa-share-nodes"></i>
                        </span>
                        <div>
                            <strong>Know someone who fits?</strong>
                            <p>Copy this page and share the opportunity directly.</p>
                        </div>
                        <button id="job-page-share-button" type="button">
                            <i class="fas fa-link" aria-hidden="true"></i>
                            <span>Copy job link</span>
                        </button>
                        <p class="jf-job-page__share-status" id="job-page-share-status" role="status" aria-live="polite"></p>
                    </div>

                    <a class="jf-job-page__back-link" href="{{ route('jobs.index') }}">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Back to all jobs
                    </a>
                </aside>
            </div>

            @if (count($relatedJobs))
                <section class="jf-job-page__related" aria-labelledby="related-jobs-title">
                    <div class="jf-job-page__related-head">
                        <div>
                            <span class="jf-kicker">Keep exploring</span>
                            <h2 id="related-jobs-title">Related opportunities</h2>
                        </div>
                        <a href="{{ route('jobs.index') }}">View all jobs <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>

                    <div class="jf-job-page__related-grid">
                        @foreach ($relatedJobs as $relatedJob)
                            <a class="jf-job-page__related-card" href="{{ route('jobs.show', $relatedJob['id']) }}">
                                <div class="jf-job-page__related-top">
                                    <span>
                                        {{ $relatedJob['company'] }}
                                        @if (! empty($relatedJob['company_verified']))
                                            <x-verified-badge :show-label="false" :size="13" label="Verified employer" />
                                        @endif
                                    </span>
                                    @if ($relatedJob['featured'])
                                        <small>Featured</small>
                                    @endif
                                </div>
                                <h3>{{ $relatedJob['title'] }}</h3>
                                <p><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $relatedJob['location'] }}</p>
                                <div class="jf-job-page__related-footer">
                                    <strong>{{ $relatedJob['short_salary'] }}</strong>
                                    <span>View role <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </section>

    @auth
    <dialog class="jf-apply-dialog" id="job-page-apply-dialog" aria-labelledby="job-page-dialog-title">
        <button class="jf-dialog__close" type="button" data-job-page-close aria-label="Close application form">
            <i class="fas fa-xmark" aria-hidden="true"></i>
        </button>
        <span class="jf-dialog__icon"><i class="fas fa-paper-plane" aria-hidden="true"></i></span>
        <p class="jf-kicker">Quick application</p>
        <h2 id="job-page-dialog-title">Apply for <span>{{ $job['title'] }}</span></h2>
        <p>
            Leave your details and {{ $job['company'] }} receives your application.
            @if ($ownResume)
                Your resume on file is attached automatically.
            @else
                A CV is required — attach one below.
            @endif
        </p>
        {{-- A real POST: this is what puts the candidate in the employer's
             Applications inbox and makes the back office count move. --}}
        <form id="job-page-apply-form" method="POST" action="{{ route('jobs.apply.store', $job['id']) }}">
            @csrf
            {{-- Prefilled from the account the visitor had to create to get here. --}}
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
        <p class="jf-dialog__error" id="job-page-apply-error" role="alert" hidden></p>
        <p class="jf-dialog__success" id="job-page-apply-success" role="status" hidden>
            <i class="fas fa-circle-check" aria-hidden="true"></i> Your application was sent.
        </p>
    </dialog>
    @endauth

    <script type="application/json" id="job-page-data">@json($job)</script>
@endsection

@push('scripts')
    <script src="{{ asset('js/job-show.js') }}"></script>
@endpush
