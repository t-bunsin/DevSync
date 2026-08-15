@extends('layouts.admin')

@section('title', 'Workspace | KH-WORKS Admin')

{{-- Turns on the dashboard shell: canvas background and the simplified sidebar. --}}
@section('body-class', 'kh-dashboard-page')

@section('main-content')
    @php
        $totalUsers = $users->count();
        $administratorCount = $users->filter(fn ($user) => $user->isAdmin())->count();
        $addedThisMonth = $users->filter(fn ($user) => $user->created_at && $user->created_at->gte(now()->startOfMonth()))->count();

        // Overview data, moved here with the dashboard. Everything except the
        // user count is still placeholder copy, exactly as it was on /home.
        $userName = auth()->user()?->displayName() ?? 'Admin';
        $firstName = trim(explode(' ', $userName)[0] ?? $userName);
        $todayLabel = now()->format('l, F j');

        $stats = [
            [
                'label' => 'Active users',
                'value' => number_format($widget['users'] ?? $totalUsers),
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

    <style>
        .kh-users {
            --kh-blue: #2563eb;
            --kh-blue-dark: #1d4ed8;
            --kh-ink: #0f172a;
            --kh-muted: #64748b;
            --kh-line: #dbe5f1;
            padding: 1.5rem 1.5rem 2rem;
            background: #f8fafc;
        }

        .kh-users__hero {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            overflow: hidden;
            padding: 2rem 2rem 2rem 2rem;
            margin-bottom: 1.5rem;
            color: #fff;
            background:
                radial-gradient(circle at 95% 10%, rgba(255, 255, 255, 0.28), transparent 18%),
                radial-gradient(circle at 7% 75%, rgba(255, 255, 255, 0.18), transparent 18%),
                linear-gradient(135deg, #1e3a8a 0%, var(--kh-blue) 52%, #60a5fa 100%);
            border-radius: 26px;
            box-shadow: 0 22px 44px rgba(37, 99, 235, 0.18);
        }

        .kh-users__hero::before {
            content: "";
            position: absolute;
            top: 14%;
            left: -4%;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(8px);
        }

        .kh-users__hero::after {
            content: "";
            position: absolute;
            right: 10%;
            bottom: -40px;
            width: 220px;
            height: 220px;
            border: 24px solid rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .kh-users__heading,
        .kh-users__hero-actions {
            position: relative;
            z-index: 1;
        }

        .kh-users__heading {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .kh-users__heading-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            min-width: 56px;
            color: #fff;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 18px;
        }

        .kh-users__heading-icon svg {
            width: 24px;
            height: 24px;
        }

        .kh-users__eyebrow {
            display: block;
            margin-bottom: 0.25rem;
            color: #c7d8ff;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.11em;
            text-transform: uppercase;
        }

        .kh-users__hero h1 {
            margin: 0 0 0.35rem;
            color: #fff;
            font-size: clamp(1.75rem, 2.75vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.05;
        }

        .kh-users__hero p {
            margin: 0;
            max-width: 600px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.95rem;
            line-height: 1.65;
        }

        .kh-users__hero-actions {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .kh-users__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 44px;
            padding: 0 1rem;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.22);
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 700;
            transition: transform 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
            border-radius: 12px;
        }

        .kh-users__button:hover {
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .kh-users__button--secondary {
            background: rgba(255, 255, 255, 0.14);
        }

        .kh-users__button--secondary:hover {
            background: rgba(255, 255, 255, 0.22);
        }

        .kh-users__button--primary {
            color: var(--kh-blue-dark);
            background: #fff;
            border-color: #fff;
            box-shadow: 0 10px 24px rgba(30, 58, 138, 0.22);
        }

        .kh-users__button--primary:hover {
            color: var(--kh-blue-dark);
            background: #eff6ff;
        }

        .kh-users__stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .kh-users__stat {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
            padding: 1.15rem 1.2rem;
            border-radius: 22px;
            background: #fff;
            border: 1px solid rgba(209, 230, 255, 0.9);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
        }

        .kh-users__stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            min-width: 44px;
            color: var(--kh-blue);
            background: #eff6ff;
            border-radius: 14px;
        }

        .kh-users__stat-icon--green {
            color: #059669;
            background: #ecfdf5;
        }

        .kh-users__stat-icon--violet {
            color: #7c3aed;
            background: #f5f3ff;
        }

        .kh-users__stat-icon svg {
            width: 20px;
            height: 20px;
        }

        .kh-users__stat strong {
            display: block;
            margin-bottom: 0.06rem;
            color: var(--kh-ink);
            font-size: 1.3rem;
            line-height: 1.15;
        }

        .kh-users__stat span {
            display: block;
            color: var(--kh-muted);
            font-size: 0.82rem;
        }

        .kh-users__card {
            background: #fff;
            border: 1px solid rgba(222, 231, 255, 0.9);
            border-radius: 24px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .kh-users__card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.4rem 1.55rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            background: #f8fbff;
        }

        .kh-users__card-head h2 {
            margin: 0 0 0.2rem;
            color: var(--kh-ink);
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .kh-users__card-head p {
            margin: 0;
            color: var(--kh-muted);
            font-size: 0.86rem;
        }

        .kh-users__count {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.85rem;
            color: var(--kh-blue-dark);
            background: #eff6ff;
            font-size: 0.78rem;
            font-weight: 800;
            border-radius: 999px;
            white-space: nowrap;
        }

        .kh-users__table-area {
            padding: 1.35rem 1.4rem 1.5rem;
        }

        .kh-users .datatable-top,
        .kh-users .datatable-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .kh-users .datatable-top {
            padding: 0 0 1rem;
        }

        .kh-users .datatable-dropdown label {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin: 0;
            color: var(--kh-muted);
            font-size: 0.82rem;
        }

        .kh-users .datatable-search {
            margin-left: auto;
        }

        .kh-users .datatable-selector,
        .kh-users .datatable-input {
            min-height: 44px;
            color: #334155;
            background-color: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: none;
            font-size: 0.9rem;
        }

        .kh-users .datatable-selector {
            min-width: 92px;
            margin-right: 0.4rem;
            padding: 0 0.85rem;
        }

        .kh-users .datatable-input {
            width: min(340px, 40vw);
            padding: 0 0.95rem;
        }

        .kh-users .datatable-selector:focus,
        .kh-users .datatable-input:focus {
            border-color: var(--kh-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .kh-users .datatable-container {
            overflow-x: auto;
            border: 1px solid rgba(229, 234, 255, 0.9);
            border-radius: 18px;
        }

        .kh-users .datatable-table {
            min-width: 980px;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .kh-users .datatable-table > thead > tr > th {
            padding: 1rem 1rem;
            color: #475569;
            background: #f8fafc;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .kh-users .datatable-table > tbody > tr > td {
            padding: 1rem 1rem;
            color: #475569;
            background: #fff;
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            font-size: 0.88rem;
        }

        .kh-users .datatable-table > tbody > tr:last-child > td {
            border-bottom: 0;
        }

        .kh-users .datatable-table > tbody > tr:hover > td {
            background: #f2f7ff;
        }

        .kh-users__identity {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 170px;
        }

        .kh-users__avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            min-width: 38px;
            color: #fff;
            background: linear-gradient(135deg, var(--kh-blue-dark), #60a5fa);
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.18);
            border-radius: 14px;
        }

        .kh-users__name {
            display: block;
            margin-bottom: 0.06rem;
            color: var(--kh-ink);
            font-weight: 700;
        }

        .kh-users__id {
            display: block;
            color: #94a3b8;
            font-size: 0.72rem;
        }

        .kh-users__email {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: var(--kh-blue-dark);
            font-weight: 600;
            text-decoration: none;
        }

        .kh-users__email:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .kh-users__email svg {
            width: 15px;
            height: 15px;
        }

        .kh-users__role {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.38rem 0.62rem;
            color: #1d4ed8;
            background: #dbeafe;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
            border-radius: 999px;
        }

        .kh-users__role::before {
            content: "";
            width: 6px;
            height: 6px;
            background: currentColor;
            border-radius: 50%;
        }

        .kh-users__role--administrator {
            color: #7c3aed;
            background: #ede9fe;
        }

        .kh-users__role--manager,
        .kh-users__role--editor {
            color: #b45309;
            background: #fef3c7;
        }

        .kh-users__role--guest {
            color: #64748b;
            background: #f1f5f9;
        }

        .kh-users__groups {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
            min-width: 260px;
        }

        .kh-users__group {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.6rem;
            color: #047857;
            background: #d1fae5;
            font-size: 0.72rem;
            font-weight: 700;
            border-radius: 999px;
        }

        .kh-users__group--blue {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .kh-users__group--purple {
            color: #7c3aed;
            background: #ede9fe;
        }

        .kh-users__group-more {
            color: #64748b;
            background: #f1f5f9;
        }

        .kh-users__date strong,
        .kh-users__date span {
            display: block;
            white-space: nowrap;
        }

        .kh-users__date strong {
            margin-bottom: 0.1rem;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .kh-users__date span {
            color: #94a3b8;
            font-size: 0.72rem;
        }

        .kh-users__actions {
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .kh-users__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            padding: 0;
            color: var(--kh-blue-dark);
            background: #eff6ff;
            border: 0;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
            border-radius: 12px;
        }

        .kh-users__action:hover {
            color: #fff;
            background: var(--kh-blue);
            transform: translateY(-1px);
        }

        .kh-users__action--danger {
            color: #dc2626;
            background: #fef2f2;
        }

        .kh-users__action--danger:hover {
            color: #fff;
            background: #dc2626;
        }

        .kh-users__action svg {
            width: 16px;
            height: 16px;
        }

        .kh-users .datatable-info {
            margin: 0;
            color: var(--kh-muted);
            font-size: 0.8rem;
        }

        .kh-users .datatable-pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            min-height: 34px;
            padding: 0.35rem 0.55rem;
            border-radius: 0;
            font-size: 0.78rem;
        }

        .kh-users__empty {
            padding: 2.5rem 1rem !important;
            color: var(--kh-muted) !important;
            text-align: center;
        }

        .kh-users__id {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .kh-users__status {
            display: inline-flex;
            align-items: center;
            padding: 0.14rem 0.42rem;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border-radius: 999px;
        }

        .kh-users__status--active {
            color: #047857;
            background: #d1fae5;
        }

        .kh-users__status--pending {
            color: #b45309;
            background: #fef3c7;
        }

        .kh-users__status--danger {
            color: #b91c1c;
            background: #fee2e2;
        }

        .kh-users__action:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .kh-users__action:disabled:hover {
            color: #dc2626;
            background: #fef2f2;
            transform: none;
        }

        /* Delete confirmation modal */
        .kh-users__modal .modal-content {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.22);
            overflow: hidden;
        }

        .kh-users__modal-body {
            padding: 2rem 1.85rem 1.5rem;
            text-align: center;
        }

        .kh-users__modal-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 62px;
            height: 62px;
            margin-bottom: 1.1rem;
            color: #dc2626;
            background: #fef2f2;
            border: 8px solid #fff5f5;
            border-radius: 50%;
        }

        .kh-users__modal-icon svg {
            width: 24px;
            height: 24px;
        }

        .kh-users__modal-title {
            margin: 0 0 0.5rem;
            color: #0f172a;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .kh-users__modal-text {
            margin: 0 auto;
            max-width: 380px;
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .kh-users__modal-user {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-top: 1.35rem;
            padding: 0.85rem 1rem;
            background: #f8fafc;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 16px;
            text-align: left;
        }

        .kh-users__modal-footer {
            display: flex;
            gap: 0.65rem;
            padding: 1.1rem 1.85rem 1.6rem;
            border-top: 0;
        }

        .kh-users__modal-footer > * {
            flex: 1 1 0;
            margin: 0;
        }

        .kh-users__modal-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            min-height: 46px;
            padding: 0 1rem;
            border: 1px solid transparent;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
            border-radius: 13px;
        }

        .kh-users__modal-btn:hover {
            transform: translateY(-1px);
        }

        .kh-users__modal-btn--ghost {
            color: #475569;
            background: #f1f5f9;
            border-color: #e2e8f0;
        }

        .kh-users__modal-btn--ghost:hover {
            background: #e2e8f0;
        }

        .kh-users__modal-btn--danger {
            color: #fff;
            background: #dc2626;
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.24);
        }

        .kh-users__modal-btn--danger:hover {
            background: #b91c1c;
        }

        .kh-users__modal-btn--danger svg {
            width: 16px;
            height: 16px;
        }

        .kh-users__modal-btn.is-busy {
            opacity: 0.7;
            cursor: progress;
            transform: none;
        }

        html[data-admin-theme="dark"] .kh-users__modal-title {
            color: #eaf4f5;
        }

        html[data-admin-theme="dark"] .kh-users__modal-text {
            color: #8ea5ad;
        }

        html[data-admin-theme="dark"] .kh-users__modal-icon {
            background: rgba(220, 38, 38, 0.16);
            border-color: rgba(220, 38, 38, 0.08);
        }

        html[data-admin-theme="dark"] .kh-users__modal-user {
            background: #0b161b;
            border-color: #263940;
        }

        html[data-admin-theme="dark"] .kh-users__modal-btn--ghost {
            color: #dce8e9;
            background: #16262d;
            border-color: #263940;
        }

        html[data-admin-theme="dark"] .kh-users__modal-btn--ghost:hover {
            background: #1d323b;
        }

        @media (max-width: 992px) {
            .kh-users {
                padding: 1rem;
            }

            .kh-users__hero {
                align-items: flex-start;
                flex-direction: column;
            }

            .kh-users__stats {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .kh-users__hero,
            .kh-users__card-head,
            .kh-users__table-area {
                padding: 1rem;
            }

            .kh-users__hero-actions,
            .kh-users__button {
                width: 100%;
            }

            .kh-users .datatable-top,
            .kh-users .datatable-bottom {
                align-items: stretch;
                flex-direction: column;
            }

            .kh-users .datatable-search,
            .kh-users .datatable-input {
                width: 100%;
            }

            .kh-users .datatable-search {
                margin-left: 0;
            }
        }
        /*
         * The users block now sits inside the dashboard container, which
         * already supplies the page width, gutters and canvas. Drop its own
         * so the overview and the directory line up on the same column.
         */
        .kh-dash .kh-users {
            padding: 0;
            background: transparent;
        }

        .kh-dash .kh-users__hero {
            margin-top: 18px;
        }
    </style>

    <div class="kh-dash">
        <header class="kh-dash__intro">
            <div>
                <div class="kh-dash__breadcrumb"><span>Workspace</span><i data-feather="chevron-right"></i><strong>Overview</strong></div>
                <h1>Good to see you, {{ $firstName }}.</h1>
                <p>Here is what is happening across KH-WORKS today.</p>
            </div>
            <div class="kh-dash__intro-actions">
                <span class="kh-date"><i data-feather="calendar"></i>{{ $todayLabel }}</span>
                <a class="kh-button kh-button--ghost" href="{{ url('/') }}" target="_blank" rel="noopener"><i data-feather="external-link"></i>View site</a>
                <a class="kh-button kh-button--primary" href="{{ route('user.create') }}"><i data-feather="user-plus"></i>Add user</a>
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

    <main class="kh-users">
        <section class="kh-users__hero">
            <div class="kh-users__heading">
                <span class="kh-users__heading-icon"><i data-feather="users"></i></span>
                <div>
                    <span class="kh-users__eyebrow">User Management</span>
                    <h1>Users List</h1>
                    <p>Manage account access, roles, and team membership from one place.</p>
                </div>
            </div>

            <div class="kh-users__hero-actions">
                <a class="kh-users__button kh-users__button--secondary" href="user-management-groups-list.html">
                    <i data-feather="users"></i>
                    <span>Manage Groups</span>
                </a>
                <a class="kh-users__button kh-users__button--primary" href="{{ route('user.create') }}">
                    <i data-feather="user-plus"></i>
                    <span>Add New User</span>
                </a>
            </div>
        </section>

        <section class="kh-users__stats" aria-label="User summary">
            <article class="kh-users__stat">
                <span class="kh-users__stat-icon"><i data-feather="users"></i></span>
                <div>
                    <strong>{{ number_format($totalUsers) }}</strong>
                    <span>Total users</span>
                </div>
            </article>
            <article class="kh-users__stat">
                <span class="kh-users__stat-icon kh-users__stat-icon--violet"><i data-feather="shield"></i></span>
                <div>
                    <strong>{{ number_format($administratorCount) }}</strong>
                    <span>Administrators</span>
                </div>
            </article>
            <article class="kh-users__stat">
                <span class="kh-users__stat-icon kh-users__stat-icon--green"><i data-feather="user-plus"></i></span>
                <div>
                    <strong>{{ number_format($addedThisMonth) }}</strong>
                    <span>Added this month</span>
                </div>
            </article>
        </section>

        <section class="kh-users__card">
            <div class="kh-users__card-head">
                <div>
                    <h2>User Directory</h2>
                    <p>Search, review, and manage every registered account.</p>
                </div>
                <span class="kh-users__count">{{ $totalUsers }} {{ \Illuminate\Support\Str::plural('record', $totalUsers) }}</span>
            </div>

            <div class="kh-users__table-area">
                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif

                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Primary role</th>
                            <th>All roles</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $displayName = $user->displayName();
                                $initials = $user->initials();
                                $primaryRole = $user->primaryRole();
                                $roleKey = $primaryRole?->code ?? 'employee';
                                $roleLabel = $primaryRole?->name_en ?? 'No role';
                                $roleClass = match ($roleKey) {
                                    'admin' => 'kh-users__role--administrator',
                                    'employer' => 'kh-users__role--manager',
                                    default => '',
                                };
                                $statusClass = match ($user->status) {
                                    'active' => 'kh-users__status--active',
                                    'suspended', 'banned' => 'kh-users__status--danger',
                                    default => 'kh-users__status--pending',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="kh-users__identity">
                                        <span class="kh-users__avatar" aria-hidden="true">{{ $initials }}</span>
                                        <div>
                                            <span class="kh-users__name">{{ $displayName }}</span>
                                            <span class="kh-users__id">
                                                <span class="kh-users__status {{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                                                <span title="{{ $user->id }}">{{ Str::before($user->id, '-') }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($user->email)
                                        <a class="kh-users__email" href="mailto:{{ $user->email }}">
                                            <i data-feather="mail"></i>
                                            <span>{{ $user->email }}</span>
                                        </a>
                                    @else
                                        <span class="kh-users__id">{{ $user->phone ?: '—' }}</span>
                                    @endif
                                </td>
                                <td><span class="kh-users__role {{ $roleClass }}">{{ $roleLabel }}</span></td>
                                <td>
                                    <div class="kh-users__groups">
                                        @forelse ($user->roles->sortBy('sort_order') as $role)
                                            <span @class([
                                                'kh-users__group',
                                                'kh-users__group--purple' => $role->code === 'admin',
                                                'kh-users__group--blue' => $role->code === 'employer',
                                            ])>{{ $role->name_en }}</span>
                                        @empty
                                            <span class="kh-users__group kh-users__group-more">No roles</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="kh-users__date">
                                        <strong>{{ $user->created_at?->format('M d, Y') ?? 'Not available' }}</strong>
                                        <span>{{ $user->created_at?->format('h:i A') ?? '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="kh-users__actions">
                                        <a class="kh-users__action" href="{{ route('user.edit', $user) }}" aria-label="Edit {{ $displayName }}" title="Edit user">
                                            <i data-feather="edit-2"></i>
                                        </a>
                                        @if ($user->id === auth()->id())
                                            <button class="kh-users__action kh-users__action--danger" type="button" disabled
                                                aria-label="Delete {{ $displayName }}" title="You cannot delete your own account">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        @else
                                            <button class="kh-users__action kh-users__action--danger" type="button"
                                                data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                                                data-user-name="{{ $displayName }}"
                                                data-user-email="{{ $user->email }}"
                                                data-user-initials="{{ $initials }}"
                                                data-delete-action="{{ route('user.destroy', $user) }}"
                                                aria-label="Delete {{ $displayName }}" title="Delete user">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="kh-users__empty" colspan="6">No users have been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>

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
                    <a href="{{ route('user.create') }}"><span><i data-feather="user-plus"></i></span><strong>Add user</strong><small>Create an account</small></a>
                    <a href="{{ route('companies') }}"><span><i data-feather="briefcase"></i></span><strong>Companies</strong><small>Review employers</small></a>
                    <a href="{{ route('profile') }}"><span><i data-feather="settings"></i></span><strong>Settings</strong><small>Update profile</small></a>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener"><span><i data-feather="globe"></i></span><strong>Website</strong><small>Open frontend</small></a>
                </div>
            </article>
        </div>
    </div>

    <div class="modal fade kh-users__modal" id="deleteUserModal" tabindex="-1"
        aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body kh-users__modal-body">
                    <span class="kh-users__modal-icon" aria-hidden="true"><i data-feather="alert-triangle"></i></span>

                    <h5 class="kh-users__modal-title" id="deleteUserModalLabel">Delete this user?</h5>
                    <p class="kh-users__modal-text">
                        This permanently removes the account and revokes its access. This action cannot be undone.
                    </p>

                    <div class="kh-users__modal-user">
                        <span class="kh-users__avatar" data-delete-initials aria-hidden="true"></span>
                        <div>
                            <span class="kh-users__name" data-delete-name></span>
                            <span class="kh-users__id" data-delete-email></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer kh-users__modal-footer">
                    <button class="kh-users__modal-btn kh-users__modal-btn--ghost" type="button" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <form method="POST" action="" id="deleteUserForm">
                        @csrf
                        @method('DELETE')
                        <button class="kh-users__modal-btn kh-users__modal-btn--danger" type="submit">
                            <i data-feather="trash-2" aria-hidden="true"></i>
                            <span>Delete user</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script src="https://sb-admin-pro.startbootstrap.com/js/datatables/datatables-simple-demo.js"></script>
@endsection

@push('scripts')
    {{-- Drives the overview chart range toggle, moved here with the dashboard. --}}
    <script src="{{ asset('js/dashboard.js') }}"></script>

    <script>
        (function () {
            'use strict';

            const modal = document.getElementById('deleteUserModal');
            const form = document.getElementById('deleteUserForm');

            if (!modal || !form) {
                return;
            }

            const nameEl = modal.querySelector('[data-delete-name]');
            const emailEl = modal.querySelector('[data-delete-email]');
            const initialsEl = modal.querySelector('[data-delete-initials]');
            const submitBtn = form.querySelector('button[type="submit"]');

            // Bootstrap delegates its own toggle listener, so this keeps working
            // after simple-datatables rebuilds the rows on search or paging.
            modal.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;

                if (!trigger) {
                    return;
                }

                form.setAttribute('action', trigger.dataset.deleteAction || '');
                nameEl.textContent = trigger.dataset.userName || 'this user';
                emailEl.textContent = trigger.dataset.userEmail || '';
                initialsEl.textContent = trigger.dataset.userInitials || '';
            });

            // Guard against a double submit while the delete request is in flight.
            form.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.classList.add('is-busy');
            });

            modal.addEventListener('hidden.bs.modal', () => {
                form.setAttribute('action', '');
                submitBtn.disabled = false;
                submitBtn.classList.remove('is-busy');
            });

            // Re-draw the feather icons that were injected into the modal.
            if (window.feather) {
                window.feather.replace();
            }
        })();
    </script>
@endpush
