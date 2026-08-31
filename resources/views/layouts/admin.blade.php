<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="KH-WORKS administration workspace" />
    <meta name="author" content="KH-WORKS" />
    {{-- Read by js/admin-notifications.js when it marks the bell read. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="theme-color" id="admin-theme-color" content="#ffffff" />
    <script>
        (() => {
            try {
                const savedTheme = localStorage.getItem('khworks:theme');
                const theme = savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : 'light';
                document.documentElement.dataset.adminTheme = theme;
                document.documentElement.style.colorScheme = theme;
                document.getElementById('admin-theme-color')?.setAttribute('content', theme === 'dark' ? '#071015' : '#ffffff');
            } catch (error) {
                document.documentElement.dataset.adminTheme = 'light';
                document.documentElement.style.colorScheme = 'light';
                document.getElementById('admin-theme-color')?.setAttribute('content', '#ffffff');
            }
        })();
    </script>
    <title>@yield('title', 'KH-WORKS Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/admin-theme.css') }}" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="{{ asset('img/favicon.png') }}" />
    @stack('styles')
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous">
    </script>
</head>

<body class="nav-fixed @yield('body-class')">
    @php
        $adminUser = auth()->user();
        $adminFullName = $adminUser?->displayName() ?? 'Administrator';
        $adminInitial = $adminUser ? mb_substr($adminUser->initials(), 0, 1) : 'A';
        $adminAvatar = $adminUser?->avatarUrl();
        $currentLocale = app()->getLocale();
    @endphp
    <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white"
        id="sidenavAccordion">
        <!-- Sidenav Toggle Button-->
        <button class="btn btn-icon btn-transparent-dark order-0 me-1 ms-1 ms-lg-2 me-lg-0" id="sidebarToggle"
            type="button" aria-label="{{ __('ui.admin.a11y.toggle_nav') }}" aria-controls="layoutSidenav" aria-expanded="true"><i
                data-feather="menu"></i></button>
        <!-- Navbar Brand-->
        <!-- * * Tip * * You can use text or an image for your navbar brand.-->
        <!-- * * * * * * When using an image, we recommend the SVG format.-->
        <!-- * * * * * * Dimensions: Maximum height: 32px, maximum width: 240px-->
        <a class="navbar-brand kh-admin-brand pe-3 ps-4 ps-lg-2" href="{{ route('home') }}">
            <span class="kh-admin-brand__mark"><i class="fas fa-briefcase" aria-hidden="true"></i></span>
            <span class="kh-admin-brand__divider" aria-hidden="true"></span>
            <span class="kh-admin-brand__text">
                <span class="kh-admin-brand__word">KH-<strong>WORKS</strong></span>
                <span class="kh-admin-brand__tagline">{{ __('ui.admin.brand_tagline') }}</span>
            </span>
        </a>
        <!-- Navbar Search Input-->
        <!-- * * Note: * * Visible only on and above the lg breakpoint-->
        {{-- <form class="form-inline me-auto d-none d-lg-block me-3">
                <div class="input-group input-group-joined input-group-solid">
                    <input class="form-control pe-0" type="search" placeholder="Search" aria-label="Search" />
                    <div class="input-group-text"><i data-feather="search"></i></div>
                </div>
            </form> --}}
        <!-- Navbar Items-->
        <ul class="navbar-nav align-items-center ms-auto">
            {{-- Local clock. Rendered hidden and filled in by admin-clock.js:
                 APP_TIMEZONE is UTC, so a server-rendered value would show the
                 wrong time until the script replaced it. --}}
            <li class="nav-item d-none d-md-block me-3 kh-admin-clock-item">
                {{-- Day/month names are handed to admin-clock.js as data, so the
                     script itself stays free of any hardcoded language. --}}
                <time class="kh-admin-clock" id="khAdminClock" hidden
                    data-clock-days="{{ json_encode(__('ui.admin.clock.days'), JSON_UNESCAPED_UNICODE) }}"
                    data-clock-months="{{ json_encode(__('ui.admin.clock.months'), JSON_UNESCAPED_UNICODE) }}"
                    data-clock-am="{{ __('ui.admin.clock.am') }}"
                    data-clock-pm="{{ __('ui.admin.clock.pm') }}">
                    <span data-clock-date></span>
                    <span class="kh-admin-clock__time" data-clock-time></span>
                </time>
            </li>
            <li class="nav-item me-2 me-sm-3 kh-admin-theme-item">
                <button class="kh-admin-theme-toggle" type="button"
                    data-theme-to-dark="{{ __('ui.admin.a11y.theme_to_dark') }}"
                    data-theme-to-light="{{ __('ui.admin.a11y.theme_to_light') }}" aria-label="{{ __('ui.admin.a11y.theme_to_dark') }}"
                    aria-pressed="false" title="{{ __('ui.admin.a11y.theme_to_dark') }}">
                    <span class="kh-admin-theme-toggle__track" aria-hidden="true">
                        <i class="fas fa-sun kh-admin-theme-toggle__sun"></i>
                        <i class="fas fa-moon kh-admin-theme-toggle__moon"></i>
                        <span class="kh-admin-theme-toggle__thumb"></span>
                    </span>
                </button>
            </li>
            {{-- Language switcher. Same session-backed route the public header uses
                 (routes/web.php -> language.switch), so switching in either shell
                 carries over to the other. --}}
            <li class="nav-item dropdown no-caret me-2 me-sm-3 kh-admin-lang-item">
                <a class="kh-admin-lang dropdown-toggle" id="languageDropdown" href="#" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    aria-label="{{ __('ui.language.switch') }}">
                    <span class="kh-admin-lang__globe" aria-hidden="true"><i data-feather="globe"></i></span>
                    <span class="kh-admin-lang__label">
                        {{ $currentLocale === 'kh' ? __('ui.language.kh') : __('ui.language.en') }}
                    </span>
                    <i class="fas fa-chevron-down kh-admin-lang__caret" aria-hidden="true"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end kh-admin-lang__menu animated--fade-in-up"
                    aria-labelledby="languageDropdown">
                    <span class="kh-admin-lang__menu-title">{{ __('ui.language.switch') }}</span>
                    {{-- The two-letter chips are decorative: the language name that
                         follows is what a screen reader should announce. --}}
                    <a class="dropdown-item kh-admin-lang__option{{ $currentLocale === 'en' ? ' is-active' : '' }}"
                        href="{{ route('language.switch', 'en') }}">
                        <span class="kh-admin-lang__code" aria-hidden="true">EN</span>
                        <span class="kh-admin-lang__name">{{ __('ui.language.english') }}</span>
                        @if ($currentLocale === 'en')
                            <i class="fas fa-check" aria-hidden="true"></i>
                        @endif
                    </a>
                    <a class="dropdown-item kh-admin-lang__option{{ $currentLocale === 'kh' ? ' is-active' : '' }}"
                        href="{{ route('language.switch', 'kh') }}">
                        <span class="kh-admin-lang__code" aria-hidden="true">KH</span>
                        <span class="kh-admin-lang__name">{{ __('ui.language.khmer') }}</span>
                        @if ($currentLocale === 'kh')
                            <i class="fas fa-check" aria-hidden="true"></i>
                        @endif
                    </a>
                </div>
            </li>
            <li class="nav-item d-none d-md-block me-3">
                <a class="nav-link kh-view-site" href="{{ url('/') }}" target="_blank" rel="noopener">
                    <i data-feather="external-link"></i>
                    <span>{{ __('ui.admin.topnav.view_website') }}</span>
                </a>
            </li>
            <!-- Navbar Search Dropdown-->
            <!-- * * Note: * * Visible only below the lg breakpoint-->
            <li class="nav-item dropdown no-caret me-3 d-lg-none kh-admin-search-item">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="searchDropdown" href="#"
                    role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    aria-label="{{ __('ui.admin.a11y.open_search') }}"><i
                        data-feather="search"></i></a>
                <!-- Dropdown - Search-->
                <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--fade-in-up"
                    aria-labelledby="searchDropdown">
                    <form class="form-inline me-auto w-100">
                        <div class="input-group input-group-joined input-group-solid">
                            <input class="form-control pe-0" type="text" placeholder="{{ __('ui.admin.topnav.search') }}"
                                aria-label="{{ __('ui.admin.a11y.search') }}" aria-describedby="basic-addon2" />
                            <div class="input-group-text"><i data-feather="search"></i></div>
                        </div>
                    </form>
                </div>
            </li>
            <!-- Alerts Dropdown: real activity, polled by js/admin-notifications.js -->
            @php
                $khNotifications = \App\Http\Controllers\NotificationController::latestFor(auth()->user());
                $khUnread = auth()->user()?->unreadNotifications()->count() ?? 0;
            @endphp
            <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications" data-kh-notifications
                data-feed-url="{{ route('notifications.feed') }}"
                data-read-url="{{ route('notifications.read') }}">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle kh-notify__toggle" id="navbarDropdownAlerts"
                    href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" aria-label="{{ __('ui.admin.a11y.open_notifications') }}"><i data-feather="bell"></i>
                    <span class="kh-notify__badge" data-kh-badge @if ($khUnread === 0) hidden @endif>{{ $khUnread > 9 ? '9+' : $khUnread }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                    aria-labelledby="navbarDropdownAlerts">
                    <h6 class="dropdown-header dropdown-notifications-header">
                        <i class="me-2" data-feather="bell"></i>
                        {{ __('ui.admin.topnav.activity_center') }}
                    </h6>
                    <div data-kh-notification-items>
                        @include('partials.notification-items', ['notifications' => $khNotifications])
                    </div>
                    <a class="dropdown-item dropdown-notifications-footer" href="{{ route('home') }}">{{ __('ui.admin.topnav.open_workspace') }}</a>
                </div>
            </li>
            <!-- Messages Dropdown-->
            <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownMessages"
                    href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" aria-label="{{ __('ui.admin.a11y.open_messages') }}"><i data-feather="mail"></i></a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                    aria-labelledby="navbarDropdownMessages">
                    <h6 class="dropdown-header dropdown-notifications-header">
                        <i class="me-2" data-feather="mail"></i>
                        {{ __('ui.admin.topnav.team_messages') }}
                    </h6>
                    <a class="dropdown-item dropdown-notifications-item" href="{{ route('companies') }}">
                        <span class="dropdown-notifications-item-img kh-message-avatar">TH</span>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">{{ __('ui.admin.inbox.verification') }}</div>
                            <div class="dropdown-notifications-item-content-details">{{ __('ui.admin.inbox.verification_meta') }}</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-item" href="{{ route('companies') }}">
                        <span class="dropdown-notifications-item-img kh-message-avatar kh-message-avatar--blue">ABA</span>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">{{ __('ui.admin.inbox.retail') }}</div>
                            <div class="dropdown-notifications-item-content-details">{{ __('ui.admin.inbox.retail_meta') }}</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-item" href="{{ route('resumes.index') }}">
                        <span class="dropdown-notifications-item-img kh-message-avatar kh-message-avatar--gold">AC</span>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">{{ __('ui.admin.inbox.portfolio') }}</div>
                            <div class="dropdown-notifications-item-content-details">{{ __('ui.admin.inbox.portfolio_meta') }}</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-item" href="{{ route('resumes.index') }}">
                        <span class="dropdown-notifications-item-img kh-message-avatar kh-message-avatar--violet">ML</span>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">{{ __('ui.admin.inbox.screening') }}</div>
                            <div class="dropdown-notifications-item-content-details">{{ __('ui.admin.inbox.screening_meta') }}</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-footer" href="{{ route('resumes.index') }}">{{ __('ui.admin.topnav.view_candidates') }}</a>
                </div>
            </li>
            <!-- User Dropdown-->
            <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle kh-user-avatar" id="navbarDropdownUserImage"
                    href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" aria-label="{{ __('ui.admin.a11y.open_account') }}">
                    @if ($adminAvatar)
                        <img class="kh-user-avatar__img" src="{{ $adminAvatar }}" alt="">
                    @else
                        {{ $adminInitial }}
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                    aria-labelledby="navbarDropdownUserImage">
                    <h6 class="dropdown-header d-flex align-items-center">
                        <span class="dropdown-user-img kh-user-avatar kh-user-avatar--large">
                            @if ($adminAvatar)
                                <img class="kh-user-avatar__img" src="{{ $adminAvatar }}" alt="">
                            @else
                                {{ $adminInitial }}
                            @endif
                        </span>
                        <div class="dropdown-user-details">
                            <div class="dropdown-user-details-name">{{ $adminFullName }}</div>
                            <div class="dropdown-user-details-email">{{ $adminUser?->email ?? 'admin@khworks.com' }}</div>
                            @if ($adminUser?->primaryRole())
                                <span class="badge bg-primary-soft text-primary mt-1">{{ $adminUser->primaryRole()->label() }}</span>
                            @endif
                        </div>
                    </h6>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('profile') }}">
                        <div class="dropdown-item-icon"><i data-feather="settings"></i></div>
                        {{ __('ui.admin.nav.account') }}
                    </a>
                    {{-- Ungated on purpose: both pages are about the caller's own
                         account, so every role reaches them the same way. The
                         billing register below in the sidebar stays admin-only. --}}
                    <a class="dropdown-item" href="{{ route('account-billing') }}">
                        <div class="dropdown-item-icon"><i data-feather="credit-card"></i></div>
                        {{ __('ui.admin.nav.billing') }}
                    </a>
                    <a class="dropdown-item" href="{{ route('security') }}">
                        <div class="dropdown-item-icon"><i data-feather="shield"></i></div>
                        {{ __('ui.admin.nav.security') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        &nbsp;{{ __('ui.admin.logout.confirm') }}
                    </a>
                </div>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sidenav shadow-right sidenav-light">
                <div class="sidenav-menu">
                    <div class="nav accordion" id="accordionSidenav">
                        <!-- Sidenav Menu Heading (Account)-->
                        <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                        <div class="sidenav-menu-heading d-sm-none">{{ __('ui.admin.nav.account') }}</div>
                        <!-- Sidenav Link (Alerts)-->
                        <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                        <a class="nav-link d-sm-none" href="#!">
                            <div class="nav-link-icon"><i data-feather="bell"></i></div>
                            {{ __('ui.admin.topnav.alerts') }}
                            <span class="badge bg-warning-soft text-warning ms-auto">{{ __('ui.admin.topnav.new_count', ['count' => 4]) }}</span>
                        </a>
                        <!-- Sidenav Link (Messages)-->
                        <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                        <a class="nav-link d-sm-none" href="#!">
                            <div class="nav-link-icon"><i data-feather="mail"></i></div>
                            {{ __('ui.admin.topnav.messages') }}
                            <span class="badge bg-success-soft text-success ms-auto">{{ __('ui.admin.topnav.new_count', ['count' => 2]) }}</span>
                        </a>
                        <!-- Sidenav Menu Heading (Core)-->
                        <div class="sidenav-menu-heading">{{ __('ui.admin.nav.core') }}</div>
                        <!-- Sidenav Accordion (Dashboard)-->
                        <a class="nav-link collapsed {{ request()->routeIs('home') ? 'kh-nav-parent-active' : '' }}"
                            href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#collapseDashboards"
                            aria-expanded="{{ request()->routeIs('home') ? 'true' : 'false' }}"
                            aria-controls="collapseDashboards">
                            <div class="nav-link-icon"><i data-feather="activity"></i></div>
                            {{ __('ui.admin.nav.dashboards') }}
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('home') ? 'show' : '' }}"
                            id="collapseDashboards" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active fw-bold' : '' }}"
                                    href="{{ route('home') }}">
                                    {{ __('ui.admin.nav.overview') }}
                                    <span class="badge bg-primary-soft text-primary ms-auto">{{ __('ui.admin.nav.live') }}</span>
                                </a>
                            </nav>
                        </div>
                        <!-- Sidenav Heading (Custom)-->
                        <div class="sidenav-menu-heading">{{ __('ui.admin.nav.custom') }}</div>
                        @php
                            // Drives both the Pages accordion and the Account one nested
                            // inside it, so landing on any Account page leaves the whole
                            // path open instead of collapsing the highlight out of sight.
                            $khAccountActive = request()->is('admin/profile', 'admin/account-billing*', 'admin/account-security*', 'account-*');
                        @endphp
                        <!-- Sidenav Accordion (Pages)-->
                        <a class="nav-link collapsed {{ $khAccountActive ? 'kh-nav-parent-active' : '' }}"
                            href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#collapsePages"
                            aria-expanded="{{ $khAccountActive ? 'true' : 'false' }}"
                            aria-controls="collapsePages">
                            <div class="nav-link-icon"><i data-feather="grid"></i></div>
                            {{ __('ui.admin.nav.pages') }}
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ $khAccountActive ? 'show' : '' }}"
                            id="collapsePages" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPagesMenu">
                                <!-- Nested Sidenav Accordion (Pages -> Account)-->
                                <a class="nav-link collapsed {{ $khAccountActive ? 'active' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#pagesCollapseAccount"
                                    aria-expanded="{{ $khAccountActive ? 'true' : 'false' }}"
                                    aria-controls="pagesCollapseAccount">
                                    {{ __('ui.admin.nav.account') }}
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>
                                <div class="collapse {{ $khAccountActive ? 'show' : '' }}"
                                    id="pagesCollapseAccount" data-bs-parent="#accordionSidenavPagesMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ setActive('admin/profile') }}"
                                            href="{{ route('profile') }}">{{ __('ui.admin.nav.profile') }}</a>
                                        {{-- The checkout and pay screens are part of this flow and
                                             keep it lit, but the register below is its own entry,
                                             so it is excluded rather than lighting both rows. --}}
                                        <a class="nav-link {{ request()->is('admin/account-billing', 'admin/account-billing/*') && ! request()->is('admin/account-billing/list') ? 'active' : '' }}"
                                            href="{{ url('admin/account-billing') }}">{{ __('ui.admin.nav.billing') }}</a>
                                        <a class="nav-link {{ request()->routeIs('security') ? 'active' : '' }}"
                                            href="{{ route('security') }}">{{ __('ui.admin.nav.security') }}</a>
                                        {{-- The whole billing register, not the caller's own row.
                                             Shown to every role by product decision, matching the
                                             route, which no longer gates on admin either. --}}
                                        <a class="nav-link {{ setActive('admin/account-billing/list') }}"
                                            href="{{ route('account-billing.list') }}">{{ __('ui.admin.nav.billing_list') }}</a>
                                    </nav>
                                </div>
                            </nav>
                        </div>
                        {{-- Each child gates itself; this only asks whether anything
                             is left to show. A job seeker sees exactly one entry —
                             Resume Management, holding their own resume — while User
                             and Job Management stay hidden from them. --}}
                        @if ($adminUser?->isAdmin() || $adminUser?->isEmployer() || $adminUser?->isCandidateOnly())
                        <!-- Sidenav Accordion (Applications)-->
                        <!-- Applications Menu -->
                        <a class="nav-link collapsed {{ request()->is('applications/*') || request()->routeIs('users', 'user.*', 'job-posts.*', 'resumes.*') || request()->is('user-management-*') ? 'kh-nav-parent-active' : '' }}"
                            href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseApps"
                            aria-expanded="{{ request()->is('applications/*') || request()->routeIs('users', 'user.*', 'job-posts.*', 'resumes.*') || request()->is('user-management-*') ? 'true' : 'false' }}"
                            aria-controls="collapseApps">
                            <div class="nav-link-icon">
                                <i data-feather="globe"></i>
                            </div>
                            {{ __('ui.admin.nav.applications') }}
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>

                        <div class="collapse {{ request()->is('applications/*') || request()->routeIs('users', 'user.*', 'job-posts.*', 'resumes.*') || request()->is('user-management-*') ? 'show' : '' }}"
                            id="collapseApps" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavAppsMenu">

                                {{-- Account management is admin-only, so the link is hidden
                                     from everyone else rather than 403ing on click. --}}
                                @if ($adminUser?->isAdmin())
                                <!-- User Management Menu Inside Applications -->
                                <a class="nav-link collapsed {{ request()->routeIs('users', 'user.*') || request()->is('user-management-*') ? 'kh-nav-parent-active fw-bold' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#appsCollapseUserManagement"
                                    aria-expanded="{{ request()->routeIs('users', 'user.*') || request()->is('user-management-*') ? 'true' : 'false' }}"
                                    aria-controls="appsCollapseUserManagement">
                                    {{ __('ui.admin.nav.user_management') }}
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('users', 'user.*') || request()->is('user-management-*') ? 'show' : '' }}"
                                    id="appsCollapseUserManagement" data-bs-parent="#accordionSidenavAppsMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->is('admin/users') ? 'active fw-bold' : '' }}"
                                            href="{{ route('user.index') }}">{{ __('ui.admin.nav.users_list') }}</a>
                                        <a class="nav-link {{ request()->routeIs('user.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('user.create') }}">{{ __('ui.admin.nav.add_user') }}</a>
                                    </nav>
                                </div>
                                @endif

                                @php
                                    $canSeeJobPosts = $adminUser?->hasPermission(\App\Models\Permission::JOB_VIEW);
                                @endphp
                                {{-- Featured Jobs lives in this group and is admin-only, so the
                                     group survives an admin switching job.view off. --}}
                                @if ($canSeeJobPosts || $adminUser?->isAdmin())
                                <!-- Job Post Management Inside Applications -->
                                <a class="nav-link collapsed {{ request()->routeIs('job-posts.*', 'featured-jobs*') ? 'kh-nav-parent-active fw-bold' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#appsCollapseJobPosts"
                                    aria-expanded="{{ request()->routeIs('job-posts.*', 'featured-jobs*') ? 'true' : 'false' }}"
                                    aria-controls="appsCollapseJobPosts">
                                    {{ __('ui.admin.nav.job_management') }}
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('job-posts.*', 'featured-jobs*') ? 'show' : '' }}"
                                    id="appsCollapseJobPosts" data-bs-parent="#accordionSidenavAppsMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        @if ($canSeeJobPosts)
                                        <a class="nav-link {{ request()->routeIs('job-posts.index') ? 'active fw-bold' : '' }}"
                                            href="{{ route('job-posts.index') }}">{{ __('ui.admin.nav.job_posts') }}</a>
                                        @endif
                                        @if ($adminUser?->hasPermission(\App\Models\Permission::JOB_CREATE))
                                        <a class="nav-link {{ request()->routeIs('job-posts.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('job-posts.create') }}">{{ __('ui.admin.nav.add_job_post') }}</a>
                                        @endif
                                        @if ($adminUser?->isAdmin())
                                        <a class="nav-link {{ request()->routeIs('featured-jobs*') ? 'active fw-bold' : '' }}"
                                            href="{{ route('featured-jobs') }}">{{ __('ui.admin.nav.featured_jobs') }}</a>
                                        @endif
                                    </nav>
                                </div>
                                @endif

                                {{-- A job seeker reaches this area for their own resume, which is
                                     theirs by ownership rather than by a granted permission. --}}
                                @if ($adminUser?->hasPermission(\App\Models\Permission::RESUME_VIEW) || $adminUser?->isCandidateOnly())
                                <!-- Resume Management Inside Applications -->
                                <a class="nav-link collapsed {{ request()->routeIs('resumes.*') ? 'kh-nav-parent-active fw-bold' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#appsCollapseResumes"
                                    aria-expanded="{{ request()->routeIs('resumes.*') ? 'true' : 'false' }}"
                                    aria-controls="appsCollapseResumes">
                                    {{ __('ui.admin.nav.resume_management') }}
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('resumes.*') ? 'show' : '' }}"
                                    id="appsCollapseResumes" data-bs-parent="#accordionSidenavAppsMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->routeIs('resumes.index') ? 'active fw-bold' : '' }}"
                                            href="{{ route('resumes.index') }}">{{ $adminUser?->isCandidateOnly() ? __('ui.admin.nav.my_resume') : __('ui.admin.nav.resumes') }}</a>
                                        @if ($adminUser?->hasPermission(\App\Models\Permission::RESUME_CREATE) || $adminUser?->isCandidateOnly())
                                        <a class="nav-link {{ request()->routeIs('resumes.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('resumes.create') }}">{{ $adminUser?->isCandidateOnly() ? __('ui.admin.nav.create_resume') : __('ui.admin.nav.add_resume') }}</a>
                                        @endif
                                    </nav>
                                </div>
                                @endif

                                <!-- Nested Sidenav Accordion (Apps -> Posts Management)
                                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#appsCollapsePostsManagement" aria-expanded="false"
                                    aria-controls="appsCollapsePostsManagement">
                                    Posts Management
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="appsCollapsePostsManagement"
                                    data-bs-parent="#accordionSidenavAppsMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link" href="blog-management-posts-list.html">Posts List</a>
                                        <a class="nav-link" href="blog-management-create-post.html">Create Post</a>
                                        <a class="nav-link" href="blog-management-edit-post.html">Edit Post</a>
                                        <a class="nav-link" href="blog-management-posts-admin.html">Posts Admin</a>
                                    </nav>
                                </div>-->
                            </nav>
                        </div>
                        @endif
                        {{-- Every child of this accordion is either admin-only or
                             admin+employer, so an employee has nothing to see inside
                             it — hide the whole accordion rather than leave it empty. --}}
                        @if ($adminUser?->isAdmin() || $adminUser?->isEmployer())
                        <!-- Sidenav Accordion (Companies)-->
                        <a class="nav-link collapsed {{ request()->routeIs('compliance.*', 'companies', 'companies.*') ? 'kh-nav-parent-active' : '' }}"
                            href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#collapseFlows"
                            aria-expanded="{{ request()->routeIs('compliance.*', 'companies', 'companies.*') ? 'true' : 'false' }}"
                            aria-controls="collapseFlows">
                            <div class="nav-link-icon"><i data-feather="repeat"></i></div>
                            {{ __('ui.admin.nav.companies') }}
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('compliance.*', 'companies', 'companies.*') ? 'show' : '' }}"
                            id="collapseFlows" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavCompaniesMenu">

                                @if ($adminUser?->isAdmin())
                                <!-- Compliance Menu Inside Companies -->
                                <a class="nav-link collapsed {{ request()->routeIs('compliance.*') ? 'kh-nav-parent-active fw-bold' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#companiesCollapseCompliance"
                                    aria-expanded="{{ request()->routeIs('compliance.*') ? 'true' : 'false' }}"
                                    aria-controls="companiesCollapseCompliance">
                                    {{ __('ui.admin.nav.compliance') }}
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('compliance.*') ? 'show' : '' }}"
                                    id="companiesCollapseCompliance" data-bs-parent="#accordionSidenavCompaniesMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->routeIs('compliance.index') ? 'active fw-bold' : '' }}"
                                            href="{{ route('compliance.index') }}">{{ __('ui.admin.nav.compliance_register') }}</a>
                                        <a class="nav-link {{ request()->routeIs('compliance.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('compliance.create') }}">{{ __('ui.admin.nav.add_record') }}</a>
                                    </nav>
                                </div>
                                @endif

                                <!-- Company Directory Inside Companies -->
                                <a class="nav-link collapsed {{ request()->routeIs('companies', 'companies.*') ? 'kh-nav-parent-active fw-bold' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#companiesCollapseDirectory"
                                    aria-expanded="{{ request()->routeIs('companies', 'companies.*') ? 'true' : 'false' }}"
                                    aria-controls="companiesCollapseDirectory">
                                    {{ __('ui.admin.nav.company_directory') }}
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('companies', 'companies.*') ? 'show' : '' }}"
                                    id="companiesCollapseDirectory" data-bs-parent="#accordionSidenavCompaniesMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->routeIs('companies') ? 'active fw-bold' : '' }}"
                                            href="{{ route('companies') }}">{{ __('ui.admin.nav.all_companies') }}</a>
                                        @if ($adminUser?->isAdmin())
                                        <a class="nav-link {{ request()->routeIs('companies.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('companies.create') }}">{{ __('ui.admin.nav.add_company') }}</a>
                                        @endif
                                    </nav>
                                </div>
                            </nav>
                        </div>
                        @endif
                        {{-- Employee's sidebar is otherwise Dashboards + Account only
                             (Applications/Companies both hide entirely above), so this
                             stands alone rather than nesting in either accordion. --}}
                        @if ($adminUser?->isEmployee())
                        <a class="nav-link {{ request()->routeIs('my-applications') ? 'active fw-bold' : '' }}"
                            href="{{ route('my-applications') }}">
                            <div class="nav-link-icon"><i data-feather="file-text"></i></div>
                            {{ __('ui.admin.nav.my_applications') }}
                        </a>
                        @endif
                        @if ($adminUser?->isAdmin())
                        <!-- Sidenav Heading (Settings)-->
                        <div class="sidenav-menu-heading">{{ __('ui.admin.nav.settings') }}</div>
                        <!-- Sidenav Accordion (Component)-->
                        <a class="nav-link collapsed {{ request()->routeIs('components.*') ? 'kh-nav-parent-active' : '' }}"
                            href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#collapseComponent"
                            aria-expanded="{{ request()->routeIs('components.*') ? 'true' : 'false' }}"
                            aria-controls="collapseComponent">
                            <div class="nav-link-icon"><i data-feather="package"></i></div>
                            {{ __('ui.admin.nav.component') }}
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('components.*') ? 'show' : '' }}"
                            id="collapseComponent" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavComponentMenu">
                                {{-- Labels come from ui.admin.components.*, not from
                                     "All {label}" / "Add {singular}" built in the view:
                                     that composition has no Khmer equivalent, and Khmer
                                     has no singular form to derive. --}}
                                @foreach (['locations', 'departments', 'job-types'] as $componentType)
                                    @php
                                        $componentActive = request()->routeIs('components.*') && request()->route('type') === $componentType;
                                        $componentCollapseId = 'componentCollapse' . \Illuminate\Support\Str::studly($componentType);
                                    @endphp
                                    <a class="nav-link collapsed {{ $componentActive ? 'kh-nav-parent-active fw-bold' : '' }}"
                                        href="javascript:void(0);" data-bs-toggle="collapse"
                                        data-bs-target="#{{ $componentCollapseId }}"
                                        aria-expanded="{{ $componentActive ? 'true' : 'false' }}"
                                        aria-controls="{{ $componentCollapseId }}">
                                        {{ __('ui.admin.components.' . $componentType . '.label') }}
                                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse {{ $componentActive ? 'show' : '' }}"
                                        id="{{ $componentCollapseId }}" data-bs-parent="#accordionSidenavComponentMenu">
                                        <nav class="sidenav-menu-nested nav">
                                            <a class="nav-link {{ request()->routeIs('components.index') && request()->route('type') === $componentType ? 'active fw-bold' : '' }}"
                                                href="{{ route('components.index', $componentType) }}">{{ __('ui.admin.components.' . $componentType . '.all') }}</a>
                                            <a class="nav-link {{ request()->routeIs('components.create') && request()->route('type') === $componentType ? 'active fw-bold' : '' }}"
                                                href="{{ route('components.create', $componentType) }}">{{ __('ui.admin.components.' . $componentType . '.add') }}</a>
                                        </nav>
                                    </div>
                                @endforeach
                            </nav>
                        </div>

                        <!-- Sidenav Link (User Role & Permission) -->
                        <a class="nav-link {{ request()->routeIs('roles') ? 'active fw-bold' : '' }}"
                            href="{{ route('roles') }}">
                            <div class="nav-link-icon"><i data-feather="shield"></i></div>
                            {{ __('ui.admin.nav.roles_permissions') }}
                        </a>
                        @endif
                    </div>
                </div>
                <!-- Sidenav Footer-->
                <div class="sidenav-footer">
                    <div class="sidenav-footer-content">
                        <div class="sidenav-footer-subtitle">{{ __('ui.admin.footer.logged_in_as') }}</div>
                        <div class="sidenav-footer-title">{{ $adminFullName }}</div>
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <!-- Main page content-->
                {{-- <div class="container-fluid"> --}}

                @yield('main-content')

                {{-- </div> --}}
            </main>
            <footer class="footer-admin mt-auto footer-light">
                <div class="kh-admin-footer">
                    <div class="kh-admin-footer__brand">
                        <span class="kh-admin-footer__mark" aria-hidden="true">
                            <i data-feather="briefcase"></i>
                        </span>
                        <div>
                            <strong>KH-WORKS</strong>
                            <small>&copy; {{ date('Y') }}. {{ __('ui.admin.footer.rights') }}</small>
                        </div>
                    </div>

                    <nav class="kh-admin-footer__links" aria-label="{{ __('ui.admin.a11y.footer_nav') }}">
                        <a href="{{ route('about') }}">{{ __('ui.admin.footer.about') }}</a>
                        <a href="{{ url('/') }}" target="_blank" rel="noopener">
                            <span>{{ __('ui.admin.topnav.view_website') }}</span>
                            <i data-feather="external-link" aria-hidden="true"></i>
                        </a>
                    </nav>
                </div>
            </footer>
        </div>
    </div>
    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('ui.admin.logout.title') }}</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="{{ __('ui.admin.a11y.close') }}"></button>
                </div>
                <div class="modal-body">{{ __('ui.admin.logout.body') }}</div>
                <div class="modal-footer">
                    <button class="btn btn-link" type="button" data-bs-dismiss="modal">{{ __('ui.admin.logout.cancel') }}</button>
                    <a class="btn btn-danger" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('ui.admin.logout.confirm') }}</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ asset('js/admin-theme.js') }}"></script>
    <script src="{{ asset('js/admin-clock.js') }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>
    @if (request()->routeIs('companies'))
        <script src="{{ asset('js/app.js') }}"></script>
    @endif
    <script src="{{ asset('js/admin-notifications.js') }}?v={{ filemtime(public_path('js/admin-notifications.js')) }}" defer></script>
    @stack('scripts')
</body>

</html>
