@extends('layouts.admin')

@php
    $userName = auth()->user()->name ?? 'Admin';
    $firstName = trim(explode(' ', $userName)[0] ?? $userName);
    $todayLabel = now()->format('F j, Y');
    $stats = [
        [
            'label' => 'Active Users',
            'value' => number_format($widget['users']),
            'icon' => 'users',
            'tone' => 'primary',
            'note' => 'People currently using KH-WORKS',
        ],
        [
            'label' => 'Open Roles',
            'value' => '128',
            'icon' => 'briefcase',
            'tone' => 'success',
            'note' => 'Vacancies published this month',
        ],
        [
            'label' => 'Applications',
            'value' => '842',
            'icon' => 'send',
            'tone' => 'warning',
            'note' => 'Candidate submissions in review',
        ],
        [
            'label' => 'Response Rate',
            'value' => '89%',
            'icon' => 'bar-chart-2',
            'tone' => 'info',
            'note' => 'Employer replies over the last 7 days',
        ],
    ];
@endphp

@section('main-content')
    <style>
        .kh-dashboard {
            padding: 1.5rem 1.5rem 2rem;
        }

        .kh-dashboard__hero {
            position: relative;
            overflow: hidden;
            padding: 2rem;
            margin-bottom: 1.5rem;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.16), transparent 24%),
                linear-gradient(135deg, #0b466f 0%, #114f7e 55%, #0d5b8f 100%);
            border-radius: 0;
            box-shadow: 0 20px 44px rgba(11, 70, 111, 0.18);
        }

        .kh-dashboard__hero::after {
            content: "";
            position: absolute;
            right: -60px;
            bottom: -80px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .kh-dashboard__hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
            gap: 1.5rem;
            align-items: start;
        }

        .kh-dashboard__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.8rem;
            margin-bottom: 1rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.12);
        }

        .kh-dashboard__hero h1 {
            margin: 0 0 0.75rem;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            letter-spacing: -0.05em;
        }

        .kh-dashboard__hero p {
            max-width: 54ch;
            margin: 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 1rem;
            line-height: 1.7;
        }

        .kh-dashboard__highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }

        .kh-dashboard__highlight {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.8rem 1rem;
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .kh-dashboard__hero-card {
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .kh-dashboard__hero-card-label {
            color: rgba(255, 255, 255, 0.74);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .kh-dashboard__hero-card-value {
            margin: 0.65rem 0 0.25rem;
            font-size: 2.4rem;
            font-weight: 700;
            line-height: 1;
        }

        .kh-dashboard__hero-card small {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .kh-dashboard__grid {
            display: grid;
            gap: 1.5rem;
        }

        .kh-dashboard__stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .kh-card {
            background: #fff;
            border: 1px solid #e6edf5;
            border-radius: 0;
            box-shadow: 0 14px 28px rgba(31, 41, 55, 0.04);
        }

        .kh-stat {
            display: grid;
            gap: 1rem;
            padding: 1.2rem;
        }

        .kh-stat__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .kh-stat__label {
            color: #667085;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .kh-stat__value {
            margin: 0.35rem 0 0;
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -0.04em;
            color: #101828;
        }

        .kh-stat__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            color: #0b466f;
            background: #e7f1f8;
        }

        .kh-stat--success .kh-stat__icon {
            color: #0f8b5f;
            background: #e7f7f1;
        }

        .kh-stat--warning .kh-stat__icon {
            color: #b7791f;
            background: #fff4de;
        }

        .kh-stat--info .kh-stat__icon {
            color: #2563eb;
            background: #e7efff;
        }

        .kh-stat__note {
            color: #667085;
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .kh-dashboard__content {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.9fr);
            gap: 1.5rem;
        }

        .kh-section {
            padding: 1.35rem;
        }

        .kh-section__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .kh-section__title {
            margin: 0;
            color: #101828;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .kh-section__subtitle {
            color: #667085;
            font-size: 0.92rem;
        }

        .kh-overview {
            display: grid;
            gap: 1rem;
        }

        .kh-overview__panel {
            padding: 1.2rem;
            background: linear-gradient(180deg, #fbfdff, #f6f9fc);
            border: 1px solid #edf2f7;
        }

        .kh-overview__bar {
            display: grid;
            gap: 0.6rem;
            margin-top: 1rem;
        }

        .kh-overview__bar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            color: #475467;
            font-size: 0.92rem;
        }

        .kh-overview__track {
            width: 100%;
            height: 10px;
            background: #e8eef5;
            overflow: hidden;
        }

        .kh-overview__fill {
            height: 100%;
            background: #0b466f;
        }

        .kh-quick-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .kh-action {
            display: grid;
            gap: 0.9rem;
            padding: 1rem;
            color: inherit;
            text-decoration: none;
            border: 1px solid #edf2f7;
            background: #fff;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .kh-action:hover {
            transform: translateY(-2px);
            border-color: #d7e3ee;
            box-shadow: 0 12px 24px rgba(11, 70, 111, 0.06);
            text-decoration: none;
        }

        .kh-action__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            color: #0b466f;
            background: #e7f1f8;
        }

        .kh-action strong {
            display: block;
            color: #101828;
            font-size: 1rem;
        }

        .kh-action span {
            color: #667085;
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .kh-list {
            display: grid;
            gap: 0.9rem;
        }

        .kh-list__item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #edf2f7;
            background: #fbfdff;
        }

        .kh-list__item strong {
            display: block;
            color: #101828;
            margin-bottom: 0.2rem;
        }

        .kh-list__item p {
            margin: 0;
            color: #667085;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .kh-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 0.7rem;
            color: #0b466f;
            background: #e7f1f8;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .kh-side-grid {
            display: grid;
            gap: 1.5rem;
        }

        .kh-insight {
            padding: 1.3rem;
            color: #fff;
            background: linear-gradient(135deg, #0b466f, #156192);
        }

        .kh-insight h3 {
            margin: 0 0 0.75rem;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .kh-insight p {
            margin: 0 0 1rem;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.7;
        }

        .kh-insight__metric {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
            margin-bottom: 0.85rem;
        }

        .kh-insight__metric strong {
            font-size: 2rem;
            line-height: 1;
        }

        .kh-insight__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .kh-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 42px;
            padding: 0 1rem;
            color: #fff;
            background: #0b466f;
            border: 0;
            text-decoration: none;
            font-weight: 700;
        }

        .kh-btn:hover {
            color: #fff;
            text-decoration: none;
            background: #093654;
        }

        .kh-btn--light {
            color: #0b466f;
            background: #fff;
        }

        .kh-btn--light:hover {
            color: #0b466f;
            background: #eef4f8;
        }

        .kh-mini-list {
            display: grid;
            gap: 0.85rem;
        }

        .kh-mini-list__item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.9rem 1rem;
            border: 1px solid #edf2f7;
            background: #fff;
        }

        .kh-mini-list__item strong {
            display: block;
            color: #101828;
            margin-bottom: 0.2rem;
        }

        .kh-mini-list__item span {
            color: #667085;
            font-size: 0.88rem;
        }

        @media (max-width: 1200px) {
            .kh-dashboard__stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .kh-dashboard__content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .kh-dashboard {
                padding: 1rem;
            }

            .kh-dashboard__hero-grid {
                grid-template-columns: 1fr;
            }

            .kh-quick-actions {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .kh-dashboard__stats {
                grid-template-columns: 1fr;
            }

            .kh-dashboard__hero,
            .kh-section,
            .kh-insight {
                padding: 1rem;
            }

            .kh-dashboard__hero h1 {
                font-size: 1.8rem;
            }
        }
    </style>

    <div class="kh-dashboard">
        @if (session('success'))
            <div class="alert alert-success border-left-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success border-left-success mb-4" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <section class="kh-dashboard__hero">
            <div class="kh-dashboard__hero-grid">
                <div>
                    <span class="kh-dashboard__eyebrow">
                        <i data-feather="activity"></i>
                        KH-WORKS Dashboard
                    </span>
                    <h1>Welcome back, {{ $firstName }}.</h1>
                    <p>Here is a polished overview of your platform activity for {{ $todayLabel }}. Track growth, monitor engagement, and jump straight into the areas that need your attention.</p>

                    <div class="kh-dashboard__highlights">
                        <div class="kh-dashboard__highlight">
                            <i data-feather="trending-up"></i>
                            <span>Performance is up 18% this week</span>
                        </div>
                        <div class="kh-dashboard__highlight">
                            <i data-feather="clock"></i>
                            <span>Average response time: 2.4 hours</span>
                        </div>
                    </div>
                </div>

                <div class="kh-dashboard__hero-card">
                    <div class="kh-dashboard__hero-card-label">Today’s Snapshot</div>
                    <div class="kh-dashboard__hero-card-value">24</div>
                    <small>New applicants landed in the pipeline since this morning.</small>

                    <div class="kh-overview__bar">
                        <div class="kh-overview__bar-row">
                            <span>Hiring velocity</span>
                            <strong>74%</strong>
                        </div>
                        <div class="kh-overview__track">
                            <div class="kh-overview__fill" style="width: 74%; background:#ffffff;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="kh-dashboard__grid">
            <section class="kh-dashboard__stats">
                @foreach ($stats as $stat)
                    <article class="kh-card kh-stat kh-stat--{{ $stat['tone'] }}">
                        <div class="kh-stat__top">
                            <div>
                                <div class="kh-stat__label">{{ $stat['label'] }}</div>
                                <div class="kh-stat__value">{{ $stat['value'] }}</div>
                            </div>
                            <div class="kh-stat__icon">
                                <i data-feather="{{ $stat['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="kh-stat__note">{{ $stat['note'] }}</div>
                    </article>
                @endforeach
            </section>

            <section class="kh-dashboard__content">
                <div class="kh-side-grid">
                    <article class="kh-card kh-section">
                        <div class="kh-section__head">
                            <div>
                                <h2 class="kh-section__title">Growth Overview</h2>
                                <div class="kh-section__subtitle">A quick read on platform momentum and operational health.</div>
                            </div>
                            <span class="kh-pill">Live</span>
                        </div>

                        <div class="kh-overview">
                            <div class="kh-overview__panel">
                                <div class="kh-overview__bar-row">
                                    <span>Candidate registrations</span>
                                    <strong>68%</strong>
                                </div>
                                <div class="kh-overview__track">
                                    <div class="kh-overview__fill" style="width: 68%;"></div>
                                </div>
                            </div>

                            <div class="kh-overview__panel">
                                <div class="kh-overview__bar-row">
                                    <span>Employer engagement</span>
                                    <strong>81%</strong>
                                </div>
                                <div class="kh-overview__track">
                                    <div class="kh-overview__fill" style="width: 81%;"></div>
                                </div>
                            </div>

                            <div class="kh-overview__panel">
                                <div class="kh-overview__bar-row">
                                    <span>Successful hires</span>
                                    <strong>57%</strong>
                                </div>
                                <div class="kh-overview__track">
                                    <div class="kh-overview__fill" style="width: 57%;"></div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="kh-card kh-section">
                        <div class="kh-section__head">
                            <div>
                                <h2 class="kh-section__title">Quick Actions</h2>
                                <div class="kh-section__subtitle">Move faster with shortcuts to the most-used admin areas.</div>
                            </div>
                        </div>

                        <div class="kh-quick-actions">
                            <a class="kh-action" href="{{ route('users') }}">
                                <div class="kh-action__icon"><i data-feather="users"></i></div>
                                <div>
                                    <strong>Manage Users</strong>
                                    <span>Review member records and account activity.</span>
                                </div>
                            </a>

                            <a class="kh-action" href="{{ route('companies') }}">
                                <div class="kh-action__icon"><i data-feather="briefcase"></i></div>
                                <div>
                                    <strong>Companies</strong>
                                    <span>Check employer profiles and hiring status.</span>
                                </div>
                            </a>

                            <a class="kh-action" href="{{ route('profile') }}">
                                <div class="kh-action__icon"><i data-feather="user"></i></div>
                                <div>
                                    <strong>My Profile</strong>
                                    <span>Update account details and preferences.</span>
                                </div>
                            </a>
                        </div>
                    </article>

                    <article class="kh-card kh-section">
                        <div class="kh-section__head">
                            <div>
                                <h2 class="kh-section__title">Recent Activity</h2>
                                <div class="kh-section__subtitle">Latest movement across recruiting and account management.</div>
                            </div>
                        </div>

                        <div class="kh-list">
                            <div class="kh-list__item">
                                <div>
                                    <strong>New company submitted verification documents</strong>
                                    <p>Tech Horizon uploaded business verification files for approval.</p>
                                </div>
                                <span class="kh-pill">Now</span>
                            </div>

                            <div class="kh-list__item">
                                <div>
                                    <strong>Profile updates were completed by 12 candidates</strong>
                                    <p>User engagement is increasing around resume completion this afternoon.</p>
                                </div>
                                <span class="kh-pill">2h ago</span>
                            </div>

                            <div class="kh-list__item">
                                <div>
                                    <strong>Senior Product Designer role reached target applicants</strong>
                                    <p>The listing hit its weekly target and is ready for shortlist review.</p>
                                </div>
                                <span class="kh-pill">Today</span>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="kh-side-grid">
                    <article class="kh-insight">
                        <h3>Hiring Insight</h3>
                        <p>Your most active category this week is technology. Candidate demand is highest for engineering, product, and hybrid-friendly roles.</p>
                        <div class="kh-insight__metric">
                            <strong>+31%</strong>
                            <span>more applications than last week</span>
                        </div>
                        <div class="kh-insight__actions">
                            <a class="kh-btn kh-btn--light" href="{{ route('companies') }}">
                                <i data-feather="eye"></i>
                                <span>View Companies</span>
                            </a>
                            <a class="kh-btn" href="{{ route('users') }}">
                                <i data-feather="users"></i>
                                <span>Open Users</span>
                            </a>
                        </div>
                    </article>

                    <article class="kh-card kh-section">
                        <div class="kh-section__head">
                            <div>
                                <h2 class="kh-section__title">Pipeline Focus</h2>
                                <div class="kh-section__subtitle">Roles that need immediate attention from the team.</div>
                            </div>
                        </div>

                        <div class="kh-mini-list">
                            <div class="kh-mini-list__item">
                                <div>
                                    <strong>Software Engineer</strong>
                                    <span>42 candidates waiting for screening</span>
                                </div>
                                <span class="kh-pill">Priority</span>
                            </div>

                            <div class="kh-mini-list__item">
                                <div>
                                    <strong>Retail Associates</strong>
                                    <span>18 interviews scheduled this week</span>
                                </div>
                                <span class="kh-pill">Active</span>
                            </div>

                            <div class="kh-mini-list__item">
                                <div>
                                    <strong>UI/UX Designer</strong>
                                    <span>Portfolio review queue updated today</span>
                                </div>
                                <span class="kh-pill">Review</span>
                            </div>
                        </div>
                    </article>

                    <article class="kh-card kh-section">
                        <div class="kh-section__head">
                            <div>
                                <h2 class="kh-section__title">System Notes</h2>
                                <div class="kh-section__subtitle">A compact operational checklist for your admin team.</div>
                            </div>
                        </div>

                        <div class="kh-list">
                            <div class="kh-list__item">
                                <div>
                                    <strong>Moderation queue</strong>
                                    <p>7 company submissions still need a final review.</p>
                                </div>
                            </div>
                            <div class="kh-list__item">
                                <div>
                                    <strong>Account hygiene</strong>
                                    <p>3 inactive users were flagged for follow-up and cleanup.</p>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection
