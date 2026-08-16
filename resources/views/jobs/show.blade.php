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

                    <button class="jf-btn jf-btn--apply" id="job-page-apply-button" type="button">
                        Apply for this role <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                    <button class="jf-job-page__save" id="job-page-save-button" type="button"
                        data-job-id="{{ $job['id'] }}" aria-label="Save {{ $job['title'] }}" aria-pressed="false">
                        <i class="far fa-bookmark" aria-hidden="true"></i>
                        <span>Save job</span>
                    </button>
                    <small class="jf-job-page__demo-note">Application drafts stay in this demo and are not sent automatically.</small>
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
                            aria-controls="job-page-panel">Requirements</button>
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
                                <div class="jf-company-profile__cards">
                                    @if (! empty($job['company_details']))
                                        <section class="jf-cprofile-card jf-cprofile-card--teal">
                                            <header class="jf-cprofile-card__head">
                                                <span class="jf-cprofile-card__icon" aria-hidden="true"><i class="fas fa-user-tie"></i></span>
                                                <h3>Employer Details</h3>
                                            </header>
                                            <dl class="jf-cprofile-facts">
                                                @foreach ($job['company_details'] as $label => $value)
                                                    <div class="jf-cprofile-facts__row">
                                                        <dt>{{ $label }}</dt>
                                                        <dd>{{ $value }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </section>
                                    @endif

                                    @if (! empty($job['company_address']))
                                        <section class="jf-cprofile-card jf-cprofile-card--gold">
                                            <header class="jf-cprofile-card__head">
                                                <span class="jf-cprofile-card__icon" aria-hidden="true"><i class="fas fa-location-dot"></i></span>
                                                <h3>Address</h3>
                                            </header>
                                            <p class="jf-cprofile-card__text">{{ $job['company_address'] }}</p>
                                            @if (! empty($job['company_website']))
                                                <a class="jf-cprofile-card__link" href="{{ $job['company_website'] }}"
                                                    target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                                    {{ preg_replace('#^https?://#', '', $job['company_website']) }}
                                                </a>
                                            @endif
                                        </section>
                                    @endif
                                </div>
                            @endif

                            @foreach ($job['company_sections'] ?? [] as $section)
                                <section class="jf-cprofile-story jf-cprofile-story--{{ $section['tone'] ?? 'teal' }}">
                                    <header class="jf-cprofile-story__head">
                                        <span class="jf-cprofile-card__icon" aria-hidden="true"><i class="fas {{ $section['icon'] }}"></i></span>
                                        <h3>{{ $section['title'] }}</h3>
                                    </header>
                                    <div class="jf-cprofile-story__body">
                                        {{-- Employer-authored prose: blank lines start a new paragraph. --}}
                                        @foreach (preg_split('/\R{2,}/', trim($section['body'])) as $paragraph)
                                            <p>{!! nl2br(e($paragraph)) !!}</p>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>

                        {{-- The JS-driven panel. Hidden on the Company tab, which
                             renders the employer profile above instead. --}}
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
                        </div>

                        {{-- What the employer offers. Static, shown with the
                             Requirements tab alongside the extra job copy. --}}
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

                        {{-- The post's extra job copy. Static, and only shown
                             while the Requirements tab is open. --}}
                        @php($extra = $job['tabs']['job_description'] ?? null)
                        @if ($extra && (filled($extra['body']) || ! empty($extra['list'])))
                            <div class="jf-job-extra" id="job-page-extra" hidden>
                                <h2 class="jf-job-extra__title">{{ $extra['title'] }}</h2>

                                @if (filled($extra['body']))
                                    <p>{{ $extra['body'] }}</p>
                                @endif

                                @if (! empty($extra['list']))
                                    <h3>{{ $extra['list_title'] }}</h3>
                                    <ul class="jf-detail__list">
                                        @foreach ($extra['list'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
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
                                    <span>{{ $relatedJob['company'] }}</span>
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

    <dialog class="jf-apply-dialog" id="job-page-apply-dialog" aria-labelledby="job-page-dialog-title">
        <button class="jf-dialog__close" type="button" data-job-page-close aria-label="Close application form">
            <i class="fas fa-xmark" aria-hidden="true"></i>
        </button>
        <span class="jf-dialog__icon"><i class="fas fa-paper-plane" aria-hidden="true"></i></span>
        <p class="jf-kicker">Quick application</p>
        <h2 id="job-page-dialog-title">Apply for <span>{{ $job['title'] }}</span></h2>
        <p>Leave your details and prepare an application draft for {{ $job['company'] }}. This demo does not send data to the employer.</p>
        <form id="job-page-apply-form">
            <label>Full name<input name="name" type="text" autocomplete="name" required></label>
            <label>Email address<input name="email" type="email" autocomplete="email" required></label>
            <button class="jf-btn jf-btn--apply" type="submit">
                Create application draft <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </button>
        </form>
        <p class="jf-dialog__success" id="job-page-apply-success" role="status" hidden>
            <i class="fas fa-circle-check" aria-hidden="true"></i> Your application draft is ready.
        </p>
    </dialog>

    <script type="application/json" id="job-page-data">@json($job)</script>
@endsection

@push('scripts')
    <script src="{{ asset('js/job-show.js') }}"></script>
@endpush
