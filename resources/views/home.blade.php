@extends('layouts.admin')

@php
    $userName = auth()->user()->first_name ?? auth()->user()->name ?? 'Admin';
    $firstName = trim(explode(' ', $userName)[0] ?? $userName);
    $todayLabel = now()->format('l, F j');
    $stats = [
        [
            'label' => 'Active users',
            'value' => number_format($widget['users'] ?? 0),
            'icon' => 'users',
            'tone' => 'teal',
            'delta' => '+12.5%',
            'note' => 'from last month',
            'points' => '2,35 18,32 34,34 50,24 66,27 82,17 98,20 114,9',
        ],
        [
            'label' => 'Open roles',
            'value' => '128',
            'icon' => 'briefcase',
            'tone' => 'blue',
            'delta' => '+8.2%',
            'note' => '12 published this week',
            'points' => '2,38 18,34 34,30 50,31 66,22 82,18 98,12 114,8',
        ],
        [
            'label' => 'Applications',
            'value' => '842',
            'icon' => 'send',
            'tone' => 'gold',
            'delta' => '+31.0%',
            'note' => 'candidate submissions',
            'points' => '2,37 18,30 34,32 50,20 66,25 82,13 98,17 114,5',
        ],
        [
            'label' => 'Response rate',
            'value' => '89%',
            'icon' => 'message-circle',
            'tone' => 'violet',
            'delta' => '+4.6%',
            'note' => '7-day employer average',
            'points' => '2,35 18,29 34,31 50,25 66,20 82,21 98,12 114,10',
        ],
    ];

    $pipeline = [
        ['label' => 'New applications', 'value' => 342, 'percentage' => 100, 'tone' => 'teal'],
        ['label' => 'Screening', 'value' => 186, 'percentage' => 72, 'tone' => 'blue'],
        ['label' => 'Interview', 'value' => 74, 'percentage' => 46, 'tone' => 'gold'],
        ['label' => 'Offer', 'value' => 21, 'percentage' => 24, 'tone' => 'violet'],
    ];

    $activities = [
        ['icon' => 'building', 'tone' => 'teal', 'title' => 'Tech Horizon submitted verification', 'meta' => 'Company review · just now', 'href' => route('companies')],
        ['icon' => 'user-plus', 'tone' => 'blue', 'title' => '12 candidates completed their profiles', 'meta' => 'Candidate activity · 38 minutes ago', 'href' => route('users')],
        ['icon' => 'check-circle', 'tone' => 'gold', 'title' => 'Product Designer reached its target', 'meta' => 'Hiring milestone · 2 hours ago', 'href' => route('companies')],
        ['icon' => 'briefcase', 'tone' => 'violet', 'title' => 'ABA Bank published a new role', 'meta' => 'New listing · today', 'href' => route('companies')],
    ];
@endphp

@section('title', 'Dashboard | KH-WORKS')
@section('body-class', 'kh-dashboard-page')

@section('main-content')
    <div class="kh-dash">
        @if (session('success'))
            <div class="kh-flash" role="status">
                <i data-feather="check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" data-dismiss-flash aria-label="Dismiss message"><i data-feather="x"></i></button>
            </div>
        @endif

        @if (session('status'))
            <div class="kh-flash" role="status">
                <i data-feather="check-circle"></i>
                <span>{{ session('status') }}</span>
                <button type="button" data-dismiss-flash aria-label="Dismiss message"><i data-feather="x"></i></button>
            </div>
        @endif

        <header class="kh-dash__intro">
            <div>
                <div class="kh-dash__breadcrumb"><span>Workspace</span><i data-feather="chevron-right"></i><strong>Overview</strong></div>
                <h1>Good to see you, {{ $firstName }}.</h1>
                <p>Here is what is happening across KH-WORKS today.</p>
            </div>
            <div class="kh-dash__intro-actions">
                <span class="kh-date"><i data-feather="calendar"></i>{{ $todayLabel }}</span>
                <a class="kh-button kh-button--ghost" href="{{ url('/') }}" target="_blank" rel="noopener"><i data-feather="external-link"></i>View site</a>
                <a class="kh-button kh-button--primary" href="{{ route('companies') }}"><i data-feather="plus"></i>Add company</a>
            </div>
        </header>

        <section class="kh-command" aria-labelledby="command-title">
            <div class="kh-command__glow" aria-hidden="true"></div>
            <div class="kh-command__copy">
                <span class="kh-command__eyebrow"><i data-feather="activity"></i>Live operations</span>
                <h2 id="command-title">Your hiring network is gaining momentum.</h2>
                <p>Applications are moving faster this week, with technology and product roles generating the strongest candidate engagement.</p>
                <div class="kh-command__signals">
                    <span><i data-feather="trending-up"></i><strong>18%</strong> weekly growth</span>
                    <span><i data-feather="clock"></i><strong>2.4h</strong> response time</span>
                    <span><i data-feather="zap"></i><strong>7</strong> priority actions</span>
                </div>
            </div>

            <div class="kh-command__score">
                <div class="kh-score-ring" style="--score: 74" role="img" aria-label="Hiring health score: 74 percent">
                    <div><strong>74</strong><span>Health score</span></div>
                </div>
                <div class="kh-command__score-copy">
                    <span>Today’s pulse</span>
                    <strong>24 new applicants</strong>
                    <small>6 more than yesterday</small>
                </div>
            </div>
        </section>

        <section class="kh-metrics" aria-label="Platform metrics">
            @foreach ($stats as $stat)
                <article class="kh-metric kh-metric--{{ $stat['tone'] }}">
                    <div class="kh-metric__top">
                        <span class="kh-metric__icon"><i data-feather="{{ $stat['icon'] }}"></i></span>
                        <span class="kh-trend"><i data-feather="arrow-up-right"></i>{{ $stat['delta'] }}</span>
                    </div>
                    <div class="kh-metric__body">
                        <span>{{ $stat['label'] }}</span>
                        <strong>{{ $stat['value'] }}</strong>
                        <small>{{ $stat['note'] }}</small>
                    </div>
                    <svg class="kh-sparkline" viewBox="0 0 116 44" role="img" aria-label="{{ $stat['label'] }} upward trend">
                        <polyline points="{{ $stat['points'] }}" fill="none" vector-effect="non-scaling-stroke"></polyline>
                    </svg>
                </article>
            @endforeach
        </section>

        <div class="kh-dash__main-grid">
            <article class="kh-panel kh-performance">
                <header class="kh-panel__head">
                    <div>
                        <span class="kh-panel__kicker">Performance</span>
                        <h2>Application activity</h2>
                        <p>Candidate volume and employer responses over time.</p>
                    </div>
                    <div class="kh-range" role="group" aria-label="Chart range">
                        <button class="is-active" type="button" data-chart-range="7d" aria-pressed="true">7 days</button>
                        <button type="button" data-chart-range="30d" aria-pressed="false">30 days</button>
                    </div>
                </header>

                <div class="kh-performance__summary">
                    <div><span>Total applications</span><strong id="chart-total" aria-live="polite">842</strong><small><i data-feather="trending-up"></i>31% vs last period</small></div>
                    <div class="kh-chart-legend"><span><i class="kh-dot kh-dot--teal"></i>Applications</span><span><i class="kh-dot kh-dot--gold"></i>Responses</span></div>
                </div>

                <div class="kh-chart" aria-label="Application performance chart">
                    <div class="kh-chart__scale" aria-hidden="true"><span>240</span><span>180</span><span>120</span><span>60</span><span>0</span></div>
                    <svg viewBox="0 0 760 260" preserveAspectRatio="none" role="img" aria-labelledby="chart-title chart-description">
                        <title id="chart-title">Applications and employer responses</title>
                        <desc id="chart-description">Both metrics trend upward during the selected period.</desc>
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
                    <div class="kh-chart__labels" aria-hidden="true"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
                </div>
            </article>

            <aside class="kh-panel kh-pipeline">
                <header class="kh-panel__head">
                    <div>
                        <span class="kh-panel__kicker">Recruitment funnel</span>
                        <h2>Pipeline health</h2>
                        <p>Where candidates are right now.</p>
                    </div>
                    <a href="{{ route('users') }}" aria-label="View all candidates"><i data-feather="arrow-up-right"></i></a>
                </header>

                <div class="kh-pipeline__total"><div><strong>623</strong><span>active candidates</span></div><span class="kh-status-dot">On track</span></div>
                <div class="kh-pipeline__stages">
                    @foreach ($pipeline as $stage)
                        <div class="kh-stage kh-stage--{{ $stage['tone'] }}">
                            <div><span>{{ $stage['label'] }}</span><strong>{{ $stage['value'] }}</strong></div>
                            <div class="kh-stage__track"><span style="--progress: {{ $stage['percentage'] }}%"></span></div>
                        </div>
                    @endforeach
                </div>
                <div class="kh-pipeline__footer"><i data-feather="info"></i><span><strong>12 candidates</strong> have been waiting for review longer than 48 hours.</span></div>
            </aside>
        </div>

        <div class="kh-dash__lower-grid">
            <article class="kh-panel kh-activity">
                <header class="kh-panel__head">
                    <div><span class="kh-panel__kicker">Live feed</span><h2>Recent activity</h2></div>
                    <a href="{{ route('users') }}" class="kh-text-button">View all <i data-feather="arrow-right"></i></a>
                </header>
                <div class="kh-activity__list">
                    @foreach ($activities as $activity)
                        <div class="kh-activity__item">
                            <span class="kh-activity__icon kh-activity__icon--{{ $activity['tone'] }}"><i data-feather="{{ $activity['icon'] }}"></i></span>
                            <div><strong>{{ $activity['title'] }}</strong><span>{{ $activity['meta'] }}</span></div>
                            <a href="{{ $activity['href'] }}" aria-label="View {{ $activity['title'] }}"><i data-feather="chevron-right"></i></a>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="kh-panel kh-focus">
                <header class="kh-panel__head"><div><span class="kh-panel__kicker">Needs attention</span><h2>Priority roles</h2></div><span class="kh-count-badge">3</span></header>
                <div class="kh-focus__list">
                    <a href="{{ route('companies') }}"><span class="kh-role-logo kh-role-logo--teal"><i data-feather="code"></i></span><div><strong>Software Engineer</strong><small>42 awaiting screening</small></div><span class="kh-priority kh-priority--high">High</span></a>
                    <a href="{{ route('companies') }}"><span class="kh-role-logo kh-role-logo--blue">ABA</span><div><strong>Retail Associates</strong><small>18 interviews this week</small></div><span class="kh-priority">Active</span></a>
                    <a href="{{ route('companies') }}"><span class="kh-role-logo kh-role-logo--gold"><i data-feather="pen-tool"></i></span><div><strong>UI/UX Designer</strong><small>9 portfolios to review</small></div><span class="kh-priority">Review</span></a>
                </div>
            </article>

            <article class="kh-panel kh-actions">
                <header class="kh-panel__head"><div><span class="kh-panel__kicker">Shortcuts</span><h2>Quick actions</h2></div></header>
                <div class="kh-actions__grid">
                    <a href="{{ route('users') }}"><span><i data-feather="users"></i></span><strong>Users</strong><small>Manage accounts</small></a>
                    <a href="{{ route('companies') }}"><span><i data-feather="briefcase"></i></span><strong>Companies</strong><small>Review employers</small></a>
                    <a href="{{ route('profile') }}"><span><i data-feather="settings"></i></span><strong>Settings</strong><small>Update profile</small></a>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener"><span><i data-feather="globe"></i></span><strong>Website</strong><small>Open frontend</small></a>
                </div>
            </article>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
