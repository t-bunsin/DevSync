@extends('layouts.admin')

@section('title', __('ui.admin.nav.title_dashboard') . ' | ZIN-WORKS Admin')

{{-- Dashboard shell: canvas background and the tightened admin chrome. --}}
@section('body-class', 'kh-dashboard-page')

@section('main-content')
    @php
        $userName = auth()->user()?->displayName() ?? 'Admin';
        $firstName = trim(explode(' ', $userName)[0] ?? $userName);
        // translatedFormat + Carbon's 'km' locale, so the date reads in Khmer too.
        // Carbon uses 'km' where the app's lang directory is 'kh'.
        $todayLabel = now()
            ->locale(app()->getLocale() === 'kh' ? 'km' : 'en')
            ->translatedFormat('l, F j');

        // Values, deltas and sparklines all come from App\Services\DashboardMetrics,
        // which reads the users, job_posts and job_applications tables. Only the
        // label, icon and tone are presentation and live here.
        $stats = collect([
            ['key' => 'users', 'lang' => 'active_users', 'icon' => 'users', 'tone' => 'teal'],
            ['key' => 'open_roles', 'lang' => 'open_roles', 'icon' => 'briefcase', 'tone' => 'blue'],
            ['key' => 'applications', 'lang' => 'applications', 'icon' => 'send', 'tone' => 'gold'],
            ['key' => 'resumes', 'lang' => 'resumes', 'icon' => 'file-text', 'tone' => 'violet'],
        ])->map(function (array $card) use ($cards) {
            $data = $cards[$card['key']];
            $delta = $data['delta'];

            return $card + [
                'label' => __('ui.dashboard.stats.' . $card['lang']),
                'note' => __('ui.dashboard.stats.' . $card['lang'] . '_note'),
                'value' => number_format($data['value']),
                'delta' => $delta,
                'delta_label' => $delta === null ? null : sprintf('%+.1f%%', $delta),
                'points' => $data['points'],
            ];
        })->all();

        $pipeline = [
            ['label' => __('ui.dashboard.funnel.new_applications'), 'value' => 342, 'percentage' => 100, 'tone' => 'teal'],
            ['label' => __('ui.dashboard.funnel.screening'), 'value' => 186, 'percentage' => 72, 'tone' => 'blue'],
            ['label' => __('ui.dashboard.funnel.interview'), 'value' => 74, 'percentage' => 46, 'tone' => 'gold'],
            ['label' => __('ui.dashboard.funnel.offer'), 'value' => 21, 'percentage' => 24, 'tone' => 'violet'],
        ];

        $activities = [
            ['icon' => 'award', 'tone' => 'teal', 'title' => __('ui.dashboard.activity.verification'), 'meta' => __('ui.dashboard.activity.verification_meta'), 'href' => route('companies')],
            ['icon' => 'user-plus', 'tone' => 'blue', 'title' => __('ui.dashboard.activity.profiles'), 'meta' => __('ui.dashboard.activity.profiles_meta'), 'href' => route('resumes.index')],
            ['icon' => 'check-circle', 'tone' => 'gold', 'title' => __('ui.dashboard.activity.milestone'), 'meta' => __('ui.dashboard.activity.milestone_meta'), 'href' => route('companies')],
            ['icon' => 'briefcase', 'tone' => 'violet', 'title' => __('ui.dashboard.activity.listing'), 'meta' => __('ui.dashboard.activity.listing_meta'), 'href' => route('companies')],
        ];
    @endphp

    <div class="kh-dash">
        <header class="kh-dash__intro">
            <div>
                <div class="kh-dash__breadcrumb"><span>{{ __('ui.dashboard.breadcrumb_workspace') }}</span><i data-feather="chevron-right"></i><strong>{{ __('ui.admin.nav.overview') }}</strong></div>
                <h1>{{ __('ui.dashboard.greeting', ['name' => $firstName]) }}</h1>
                <p>{{ __('ui.dashboard.subtitle') }}</p>
            </div>
            <div class="kh-dash__intro-actions">
                <span class="kh-date"><i data-feather="calendar"></i>{{ $todayLabel }}</span>
                <a class="kh-button kh-button--ghost" href="{{ url('/') }}" target="_blank" rel="noopener"><i data-feather="external-link"></i>{{ __('ui.dashboard.view_site') }}</a>
                <a class="kh-button kh-button--primary" href="{{ route('user.create') }}"><i data-feather="user-plus"></i>{{ __('ui.dashboard.add_user') }}</a>
            </div>
        </header>

        <section class="kh-command" aria-labelledby="command-title">
            <div class="kh-command__glow" aria-hidden="true"></div>
            <div class="kh-command__copy">
                <span class="kh-command__eyebrow"><i data-feather="activity"></i>{{ __('ui.dashboard.live_operations') }}</span>
                <h2 id="command-title">{{ __('ui.dashboard.hero_title') }}</h2>
                <p>{{ __('ui.dashboard.hero_body') }}</p>
                <div class="kh-command__signals">
                    <span><i data-feather="trending-up"></i><strong>18%</strong> {{ __('ui.dashboard.signal_growth') }}</span>
                    <span><i data-feather="clock"></i><strong>2.4h</strong> {{ __('ui.dashboard.signal_response') }}</span>
                    <span><i data-feather="zap"></i><strong>7</strong> {{ __('ui.dashboard.signal_actions') }}</span>
                </div>
            </div>

            <div class="kh-command__score">
                <div class="kh-score-ring" style="--score: 74" role="img" aria-label="{{ __('ui.bo.applications.health_aria', ['score' => 74]) }}">
                    <div><strong>74</strong><span>{{ __('ui.dashboard.health_score') }}</span></div>
                </div>
                <div class="kh-command__score-copy">
                    <span>{{ __('ui.dashboard.todays_pulse') }}</span>
                    <strong>{{ __('ui.dashboard.pulse_value') }}</strong>
                    <small>{{ __('ui.dashboard.pulse_note') }}</small>
                </div>
            </div>
        </section>

        <section class="kh-metrics" aria-label="{{ __('ui.bo.applications.metrics_aria') }}">
            @foreach ($stats as $stat)
                <article class="kh-metric kh-metric--{{ $stat['tone'] }}">
                    <div class="kh-metric__top">
                        <span class="kh-metric__icon"><i data-feather="{{ $stat['icon'] }}"></i></span>
                        {{-- Hidden when the previous period was empty: there is no
                             percentage change from nothing, and an invented one
                             would read as real movement. --}}
                        @if ($stat['delta'] !== null)
                            <span @class(['kh-trend', 'kh-trend--down' => $stat['delta'] < 0, 'kh-trend--flat' => $stat['delta'] == 0])>
                                <i data-feather="{{ $stat['delta'] < 0 ? 'arrow-down-right' : ($stat['delta'] == 0 ? 'minus' : 'arrow-up-right') }}"></i>{{ $stat['delta_label'] }}
                            </span>
                        @endif
                    </div>
                    <div class="kh-metric__body">
                        <span>{{ $stat['label'] }}</span>
                        <strong>{{ $stat['value'] }}</strong>
                        <small>{{ $stat['note'] }}</small>
                    </div>
                    <svg class="kh-sparkline" viewBox="0 0 116 44" role="img" aria-label="{{ $stat['label'] }} {{ __('ui.dashboard.stats.trend_aria') }}">
                        <polyline points="{{ $stat['points'] }}" fill="none" vector-effect="non-scaling-stroke"></polyline>
                    </svg>
                </article>
            @endforeach
        </section>

        <div class="kh-dash__main-grid">
            <article class="kh-panel kh-performance">
                <header class="kh-panel__head">
                    <div>
                        <span class="kh-panel__kicker">{{ __('ui.dashboard.priority.subtitle') }}</span>
                        <h2>{{ __('ui.dashboard.chart.title') }}</h2>
                        <p>{{ __('ui.dashboard.chart.subtitle') }}</p>
                    </div>
                    <div class="kh-range" role="group" aria-label="{{ __('ui.bo.applications.chart_range_aria') }}">
                        <button class="is-active" type="button" data-chart-range="7d" aria-pressed="true">{{ __('ui.dashboard.chart.range_7d') }}</button>
                        <button type="button" data-chart-range="30d" aria-pressed="false">{{ __('ui.dashboard.chart.range_30d') }}</button>
                    </div>
                </header>

                <div class="kh-performance__summary">
                    <div><span>{{ __('ui.dashboard.total_applications') }}</span><strong id="chart-total" aria-live="polite">{{ number_format($cards['applications']['value']) }}</strong><small><i data-feather="trending-up"></i>{{ __('ui.dashboard.vs_last_period') }}</small></div>
                    <div class="kh-chart-legend"><span><i class="kh-dot kh-dot--teal"></i>{{ __('ui.dashboard.chart.applications') }}</span><span><i class="kh-dot kh-dot--gold"></i>{{ __('ui.dashboard.chart.responses') }}</span></div>
                </div>

                <div class="kh-chart" aria-label="{{ __('ui.bo.applications.chart_aria') }}">
                    <div class="kh-chart__scale" aria-hidden="true"><span>240</span><span>180</span><span>120</span><span>60</span><span>0</span></div>
                    <svg viewBox="0 0 760 260" preserveAspectRatio="none" role="img" aria-labelledby="chart-title chart-description">
                        <title id="chart-title">{{ __('ui.dashboard.chart.alt_title') }}</title>
                        <desc id="chart-description">{{ __('ui.dashboard.chart.alt_desc') }}</desc>
                        <defs>
                            <linearGradient id="kh-chart-fill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1ca6a0" stop-opacity="0.28"></stop>
                                <stop offset="100%" stop-color="#1ca6a0" stop-opacity="0"></stop>
                            </linearGradient>
                        </defs>
                        <g class="kh-chart__grid">
                            <line x1="0" y1="20" x2="760" y2="20"></line><line x1="0" y1="75" x2="760" y2="75"></line>
                            <line x1="0" y1="130" x2="760" y2="130"></line><line x1="0" y1="185" x2="760" y2="185"></line>
                            <line x1="0" y1="240" x2="760" y2="240"></line>
                        </g>
                        <path id="chart-area" class="kh-chart__area" d="M0,214 C55,210 72,178 126,182 C180,186 192,145 252,151 C312,158 326,111 382,119 C438,127 456,82 508,91 C568,102 586,56 638,70 C692,84 717,37 760,43 L760,240 L0,240 Z"></path>
                        <path id="chart-line" class="kh-chart__line" d="M0,214 C55,210 72,178 126,182 C180,186 192,145 252,151 C312,158 326,111 382,119 C438,127 456,82 508,91 C568,102 586,56 638,70 C692,84 717,37 760,43"></path>
                        <path id="chart-response" class="kh-chart__response" d="M0,228 C70,218 84,211 126,213 C180,216 204,192 252,199 C312,207 334,175 382,181 C438,189 463,155 508,164 C568,174 591,137 638,149 C690,161 724,116 760,124"></path>
                    </svg>
                    <div class="kh-chart__labels" aria-hidden="true">@foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $khDay)<span>{{ __('ui.dashboard.days.' . $khDay) }}</span>@endforeach</div>
                </div>
            </article>

            <aside class="kh-panel kh-pipeline">
                <header class="kh-panel__head">
                    <div>
                        <span class="kh-panel__kicker">{{ __('ui.dashboard.funnel.title') }}</span>
                        <h2>{{ __('ui.dashboard.funnel.subtitle') }}</h2>
                        <p>{{ __('ui.dashboard.pipeline_where') }}</p>
                    </div>
                    <a href="{{ route('resumes.index') }}" aria-label="{{ __('ui.bo.applications.view_all_candidates') }}"><i data-feather="arrow-up-right"></i></a>
                </header>

                <div class="kh-pipeline__total"><div><strong>623</strong><span>{{ __('ui.dashboard.active_candidates') }}</span></div><span class="kh-status-dot">{{ __('ui.dashboard.priority.on_track') }}</span></div>
                <div class="kh-pipeline__stages">
                    @foreach ($pipeline as $stage)
                        <div class="kh-stage kh-stage--{{ $stage['tone'] }}">
                            <div><span>{{ $stage['label'] }}</span><strong>{{ $stage['value'] }}</strong></div>
                            <div class="kh-stage__track"><span style="--progress: {{ $stage['percentage'] }}%"></span></div>
                        </div>
                    @endforeach
                </div>
                <div class="kh-pipeline__footer"><i data-feather="info"></i><span><strong>{{ __('ui.dashboard.pipeline_waiting_lead') }}</strong> {{ __('ui.dashboard.pipeline_waiting_rest') }}</span></div>
            </aside>
        </div>

        <div class="kh-dash__lower-grid">
            <article class="kh-panel kh-activity">
                <header class="kh-panel__head">
                    <div><span class="kh-panel__kicker">{{ __('ui.dashboard.activity.subtitle') }}</span><h2>{{ __('ui.dashboard.activity.title') }}</h2></div>
                    <a href="{{ route('resumes.index') }}" class="kh-text-button">{{ __('ui.dashboard.view_all') }} <i data-feather="arrow-right"></i></a>
                </header>
                <div class="kh-activity__list">
                    @foreach ($activities as $activity)
                        <div class="kh-activity__item">
                            <span class="kh-activity__icon kh-activity__icon--{{ $activity['tone'] }}"><i data-feather="{{ $activity['icon'] }}"></i></span>
                            <div><strong>{{ $activity['title'] }}</strong><span>{{ $activity['meta'] }}</span></div>
                            <a href="{{ $activity['href'] }}" aria-label="{{ __('ui.bo.applications.view_activity', ['title' => $activity['title']]) }}"><i data-feather="chevron-right"></i></a>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="kh-panel kh-focus">
                <header class="kh-panel__head"><div><span class="kh-panel__kicker">{{ __('ui.dashboard.priority.needs_attention') }}</span><h2>{{ __('ui.dashboard.priority.title') }}</h2></div><span class="kh-count-badge">3</span></header>
                <div class="kh-focus__list">
                    <a href="{{ route('companies') }}"><span class="kh-role-logo kh-role-logo--teal"><i data-feather="code"></i></span><div><strong>Software Engineer</strong><small>{{ __('ui.dashboard.roles.engineer_note') }}</small></div><span class="kh-priority kh-priority--high">{{ __('ui.dashboard.priority.high') }}</span></a>
                    <a href="{{ route('companies') }}"><span class="kh-role-logo kh-role-logo--blue">ABA</span><div><strong>Retail Associates</strong><small>{{ __('ui.dashboard.roles.retail_note') }}</small></div><span class="kh-priority">{{ __('ui.dashboard.priority.active') }}</span></a>
                    <a href="{{ route('companies') }}"><span class="kh-role-logo kh-role-logo--gold"><i data-feather="pen-tool"></i></span><div><strong>UI/UX Designer</strong><small>{{ __('ui.dashboard.roles.designer_note') }}</small></div><span class="kh-priority">{{ __('ui.dashboard.priority.review') }}</span></a>
                </div>
            </article>

            <article class="kh-panel kh-actions">
                <header class="kh-panel__head"><div><span class="kh-panel__kicker">{{ __('ui.dashboard.actions.subtitle') }}</span><h2>{{ __('ui.dashboard.actions.title') }}</h2></div></header>
                <div class="kh-actions__grid">
                    <a href="{{ route('user.create') }}"><span><i data-feather="user-plus"></i></span><strong>{{ __('ui.dashboard.quick.add_user') }}</strong><small>{{ __('ui.dashboard.quick.add_user_note') }}</small></a>
                    <a href="{{ route('companies') }}"><span><i data-feather="briefcase"></i></span><strong>{{ __('ui.dashboard.quick.companies') }}</strong><small>{{ __('ui.dashboard.quick.companies_note') }}</small></a>
                    <a href="{{ route('profile') }}"><span><i data-feather="settings"></i></span><strong>{{ __('ui.dashboard.quick.settings') }}</strong><small>{{ __('ui.dashboard.quick.settings_note') }}</small></a>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener"><span><i data-feather="globe"></i></span><strong>{{ __('ui.dashboard.quick.website') }}</strong><small>{{ __('ui.dashboard.quick.website_note') }}</small></a>
                </div>
            </article>
        </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
