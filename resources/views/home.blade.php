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
                // The line under the figure reports the card's actual state
                // rather than always describing the metric: nothing counted
                // yet, or nothing to compare against, are both worth saying.
                'note' => match (true) {
                    $data['value'] === 0 => __('ui.dashboard.stats.' . $card['lang'] . '_empty'),
                    $delta === null => __('ui.dashboard.stats.no_baseline'),
                    default => __('ui.dashboard.stats.' . $card['lang'] . '_note'),
                },
                'value' => number_format($data['value']),
                'delta' => $delta,
                // A signed zero ("+0.0%") reads as a rise of nothing, and a
                // dash says "unchanged" without spending four characters on it.
                'delta_label' => match (true) {
                    $delta === null => __('ui.dashboard.stats.new_badge'),
                    (float) $delta === 0.0 => '—',
                    default => sprintf('%+.1f%%', $delta),
                },
                'delta_title' => match (true) {
                    $delta === null => __('ui.dashboard.stats.new_title'),
                    (float) $delta === 0.0 => __('ui.dashboard.stats.flat_title'),
                    default => null,
                },
                'trend' => $data['trend'],
            ];
        })->all();

        // Counts, percentages and conversion all come from
        // DashboardMetrics::funnel(), which reads real application statuses.
        // Only the label, order and tone are presentation and live here.
        $pipeline = collect([
            ['key' => 'new', 'lang' => 'new_applications', 'tone' => 'teal'],
            ['key' => 'screening', 'lang' => 'screening', 'tone' => 'blue'],
            ['key' => 'interview', 'lang' => 'interview', 'tone' => 'gold'],
            ['key' => 'hired', 'lang' => 'offer', 'tone' => 'violet'],
        ])->map(function (array $stage) use ($funnel) {
            $data = $funnel['stages'][$stage['key']];

            return $stage + [
                'label' => __('ui.dashboard.funnel.' . $stage['lang']),
                'value' => number_format($data['value']),
                'percentage' => $data['percentage'],
                'conversion' => $data['conversion'],
            ];
        })->all();

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

        {{-- Command banner. Every figure here comes from DashboardMetrics::command();
             the view only decides how to say it. --}}
        @php
            $trend = $command['trend'];
            $score = $command['score'];
            $delta = $command['today_delta'];

            // The spark polyline closed into a filled area. The service draws
            // the line; the floor is added here because only the view knows
            // how tall its own box is. Null when nothing arrived in the
            // fortnight — there is no shape to draw.
            $sparkPoints = $command['spark'];
            $sparkArea = null;

            if ($sparkPoints !== null) {
                $sparkEnds = explode(' ', $sparkPoints);
                $sparkFirstX = explode(',', reset($sparkEnds))[0];
                $sparkLastX = explode(',', end($sparkEnds))[0];
                $sparkArea = $sparkPoints . ' ' . $sparkLastX . ',46 ' . $sparkFirstX . ',46';
            }

            $signals = [
                [
                    'icon' => $command['growth'] === null ? 'minus' : ($command['growth'] < 0 ? 'trending-down' : 'trending-up'),
                    'tone' => $command['growth'] === null ? 'muted' : ($command['growth'] < 0 ? 'warn' : 'good'),
                    'value' => $command['growth'] === null ? null : sprintf('%+.0f%%', $command['growth']),
                    'label' => __('ui.dashboard.signal_growth'),
                ],
                [
                    'icon' => 'clock',
                    // Answering inside the working day reads as good; the
                    // rest is just reported, not judged.
                    'tone' => $command['response_hours'] === null ? 'muted' : ($command['response_hours'] <= 24 ? 'good' : 'warn'),
                    'value' => $command['response_hours'] === null
                        ? null
                        : __('ui.dashboard.signal_hours', ['hours' => rtrim(rtrim(number_format($command['response_hours'], 1), '0'), '.')]),
                    'label' => __('ui.dashboard.signal_response'),
                ],
                [
                    'icon' => 'zap',
                    'tone' => $command['overdue'] > 0 ? 'warn' : 'good',
                    'value' => number_format($command['overdue']),
                    'label' => __('ui.dashboard.signal_actions'),
                ],
            ];
        @endphp

        <section class="kh-command kh-command--{{ $command['tone'] }}" aria-labelledby="command-title">
            <div class="kh-command__glow" aria-hidden="true"></div>
            <div class="kh-command__glow kh-command__glow--two" aria-hidden="true"></div>

            <div class="kh-command__copy">
                <span class="kh-command__eyebrow">
                    <span class="kh-command__live" aria-hidden="true"></span>{{ __('ui.dashboard.live_operations') }}
                </span>
                <h2 id="command-title">{{ __('ui.dashboard.hero.' . $trend . '.title') }}</h2>
                <p>{{ __('ui.dashboard.hero.' . $trend . '.body') }}</p>

                <div class="kh-command__signals">
                    @foreach ($signals as $signal)
                        <span class="kh-signal kh-signal--{{ $signal['tone'] }}">
                            <i data-feather="{{ $signal['icon'] }}"></i>
                            <strong>{{ $signal['value'] ?? '—' }}</strong>
                            {{ $signal['value'] === null ? __('ui.dashboard.signal_none') : $signal['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            <aside class="kh-command__aside">
                <div class="kh-command__gauge">
                    {{-- An SVG arc rather than a conic gradient: it takes a rounded
                         cap, and it can be given a starting length to animate from. --}}
                    <svg class="kh-gauge" viewBox="0 0 120 120" role="img"
                         aria-label="{{ $score === null ? __('ui.dashboard.score_pending') : __('ui.bo.applications.health_aria', ['score' => $score]) }}">
                        <circle class="kh-gauge__track" cx="60" cy="60" r="52"></circle>
                        @if ($score !== null)
                            {{-- 327 is the circumference at r=52, rounded down so a
                                 full score closes the ring instead of overshooting it. --}}
                            <circle class="kh-gauge__arc" cx="60" cy="60" r="52"
                                    style="--arc: {{ round(327 * $score / 100) }}"></circle>
                        @endif
                    </svg>
                    <div class="kh-command__gauge-value">
                        <strong>{{ $score ?? '—' }}</strong>
                        <span>{{ __('ui.dashboard.health_score') }}</span>
                    </div>
                </div>

                <p class="kh-command__verdict">
                    {{ $score === null ? __('ui.dashboard.score_pending') : __('ui.dashboard.score_tone.' . $command['tone']) }}
                </p>

                <div class="kh-command__pulse">
                    <div class="kh-command__pulse-head">
                        <span>{{ __('ui.dashboard.todays_pulse') }}</span>
                        <strong>{{ __('ui.dashboard.pulse_value', ['count' => number_format($command['today'])]) }}</strong>
                        <small @class(['kh-command__delta', 'is-up' => $delta > 0, 'is-down' => $delta < 0])>
                            @if ($command['today'] === 0 && $command['yesterday'] === 0)
                                {{ __('ui.dashboard.pulse_quiet') }}
                            @elseif ($delta > 0)
                                <i data-feather="arrow-up-right"></i>{{ __('ui.dashboard.pulse_up', ['count' => $delta]) }}
                            @elseif ($delta < 0)
                                <i data-feather="arrow-down-right"></i>{{ __('ui.dashboard.pulse_down', ['count' => abs($delta)]) }}
                            @else
                                {{ __('ui.dashboard.pulse_same') }}
                            @endif
                        </small>
                    </div>

                    @if ($sparkPoints !== null)
                        <svg class="kh-command__spark" viewBox="0 0 240 46" preserveAspectRatio="none"
                             role="img" aria-label="{{ __('ui.dashboard.pulse_spark_aria') }}">
                            <polygon points="{{ $sparkArea }}"></polygon>
                            <polyline points="{{ $sparkPoints }}" vector-effect="non-scaling-stroke"></polyline>
                        </svg>
                    @endif
                </div>
            </aside>
        </section>

        <section class="kh-metrics" aria-label="{{ __('ui.bo.applications.metrics_aria') }}">
            @foreach ($stats as $stat)
                <article class="kh-metric">
                    <div class="kh-metric__top">
                        <span class="kh-metric__label">
                            <i data-feather="{{ $stat['icon'] }}"></i>{{ $stat['label'] }}
                        </span>
                        <span @class([
                            'kh-metric__chip',
                            'is-up' => $stat['delta'] !== null && $stat['delta'] > 0,
                            'is-down' => $stat['delta'] !== null && $stat['delta'] < 0,
                        ]) @if ($stat['delta_title']) title="{{ $stat['delta_title'] }}" @endif>{{ $stat['delta_label'] }}</span>
                    </div>

                    <div class="kh-metric__body">
                        <strong>{{ $stat['value'] }}</strong>

                        {{-- Eight weeks of arrivals, beside the figure rather than
                             under it: the number and its shape read as one line. --}}
                        <span @class(['kh-metric__trend', 'is-flat' => $stat['trend'] === null])
                              role="img" aria-label="{{ $stat['label'] }} {{ __('ui.dashboard.stats.trend_aria') }}">
                            @if ($stat['trend'] !== null)
                                <svg viewBox="0 0 120 36" preserveAspectRatio="none" focusable="false">
                                    <path d="{{ $stat['trend']['line'] }}" vector-effect="non-scaling-stroke"></path>
                                </svg>
                                {{-- Marks where the series ends. Outside the stretched
                                     SVG so it stays a circle rather than an ellipse. --}}
                                <span class="kh-metric__dot" style="top: {{ round($stat['trend']['dot_y'] / 36 * 100, 2) }}%"></span>
                            @endif
                        </span>
                    </div>

                    <small class="kh-metric__note">{{ $stat['note'] }}</small>
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

                <div class="kh-pipeline__total">
                    <div>
                        <strong>{{ number_format($funnel['active']) }}</strong>
                        <span>{{ __('ui.dashboard.active_candidates') }}</span>
                    </div>
                    {{-- Green when nothing has gone past the review SLA, amber the
                         moment something has. It used to say "On track" no matter
                         what the pipeline actually looked like. --}}
                    <span @class(['kh-pipeline__state', 'is-warn' => $funnel['overdue'] > 0])>
                        {{ $funnel['overdue'] > 0 ? __('ui.dashboard.priority.needs_attention') : __('ui.dashboard.priority.on_track') }}
                    </span>
                </div>

                @if ($funnel['total'] === 0)
                    <p class="kh-pipeline__empty">{{ __('ui.dashboard.pipeline_empty') }}</p>
                @else
                    <div class="kh-pipeline__stages">
                        @foreach ($pipeline as $stage)
                            {{-- The share of the stage above that made it this far —
                                 skipped for the top stage (nothing above it) and
                                 whenever that share is null (the stage above was
                                 empty, so there is no share of nothing to report). --}}
                            @if (! $loop->first && $stage['conversion'] !== null)
                                <p class="kh-stage__step">{{ __('ui.dashboard.funnel.conversion', ['percent' => $stage['conversion']]) }}</p>
                            @endif
                            <div class="kh-stage kh-stage--{{ $stage['tone'] }}">
                                <div class="kh-stage__head">
                                    <span>{{ $stage['label'] }}</span>
                                    <span class="kh-stage__figures"><strong>{{ $stage['value'] }}</strong><em>{{ $stage['percentage'] }}%</em></span>
                                </div>
                                <div class="kh-stage__track"><span style="--progress: {{ $stage['percentage'] }}%"></span></div>
                            </div>
                        @endforeach
                    </div>

                    {{-- A line of text, not a banner: it is a footnote on the panel
                         above it, and only earns colour on the word that carries
                         the state. --}}
                    <p @class(['kh-pipeline__footer', 'is-ok' => $funnel['overdue'] === 0])>
                        @if ($funnel['overdue'] > 0)
                            {{ trans_choice('ui.dashboard.pipeline_waiting', $funnel['overdue'], ['count' => number_format($funnel['overdue']), 'hours' => \App\Services\DashboardMetrics::REVIEW_SLA_HOURS]) }}
                        @else
                            {{ __('ui.dashboard.pipeline_current') }}
                        @endif
                    </p>
                @endif
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
