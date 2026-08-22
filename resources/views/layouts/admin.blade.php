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
    @endphp
    <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white"
        id="sidenavAccordion">
        <!-- Sidenav Toggle Button-->
        <button class="btn btn-icon btn-transparent-dark order-0 me-1 ms-1 ms-lg-2 me-lg-0" id="sidebarToggle"
            type="button" aria-label="Toggle navigation" aria-controls="layoutSidenav" aria-expanded="true"><i
                data-feather="menu"></i></button>
        <!-- Navbar Brand-->
        <!-- * * Tip * * You can use text or an image for your navbar brand.-->
        <!-- * * * * * * When using an image, we recommend the SVG format.-->
        <!-- * * * * * * Dimensions: Maximum height: 32px, maximum width: 240px-->
        <a class="navbar-brand kh-admin-brand pe-3 ps-4 ps-lg-2" href="{{ route('home') }}">
            <span class="kh-admin-brand__mark"><i class="fas fa-briefcase"></i></span>
            <span>KH-<strong>WORKS</strong></span>
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
                <time class="kh-admin-clock" id="khAdminClock" hidden>
                    <span data-clock-date></span>
                    <span class="kh-admin-clock__time" data-clock-time></span>
                </time>
            </li>
            <li class="nav-item me-2 me-sm-3 kh-admin-theme-item">
                <button class="kh-admin-theme-toggle" type="button" aria-label="Switch to dark theme"
                    aria-pressed="false" title="Switch to dark theme">
                    <span class="kh-admin-theme-toggle__track" aria-hidden="true">
                        <i class="fas fa-sun kh-admin-theme-toggle__sun"></i>
                        <i class="fas fa-moon kh-admin-theme-toggle__moon"></i>
                        <span class="kh-admin-theme-toggle__thumb"></span>
                    </span>
                </button>
            </li>
            <li class="nav-item d-none d-md-block me-3">
                <a class="nav-link kh-view-site" href="{{ url('/') }}" target="_blank" rel="noopener">
                    <i data-feather="external-link"></i>
                    <span>View website</span>
                </a>
            </li>
            <!-- Navbar Search Dropdown-->
            <!-- * * Note: * * Visible only below the lg breakpoint-->
            <li class="nav-item dropdown no-caret me-3 d-lg-none kh-admin-search-item">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="searchDropdown" href="#"
                    role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    aria-label="Open search"><i
                        data-feather="search"></i></a>
                <!-- Dropdown - Search-->
                <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--fade-in-up"
                    aria-labelledby="searchDropdown">
                    <form class="form-inline me-auto w-100">
                        <div class="input-group input-group-joined input-group-solid">
                            <input class="form-control pe-0" type="text" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2" />
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
                    aria-expanded="false" aria-label="Open notifications"><i data-feather="bell"></i>
                    <span class="kh-notify__badge" data-kh-badge @if ($khUnread === 0) hidden @endif>{{ $khUnread > 9 ? '9+' : $khUnread }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                    aria-labelledby="navbarDropdownAlerts">
                    <h6 class="dropdown-header dropdown-notifications-header">
                        <i class="me-2" data-feather="bell"></i>
                        Activity center
                    </h6>
                    <div data-kh-notification-items>
                        @include('partials.notification-items', ['notifications' => $khNotifications])
                    </div>
                    <a class="dropdown-item dropdown-notifications-footer" href="{{ route('home') }}">Open workspace</a>
                </div>
            </li>
            <!-- Messages Dropdown-->
            <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownMessages"
                    href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" aria-label="Open messages"><i data-feather="mail"></i></a>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                    aria-labelledby="navbarDropdownMessages">
                    <h6 class="dropdown-header dropdown-notifications-header">
                        <i class="me-2" data-feather="mail"></i>
                        Team messages
                    </h6>
                    <a class="dropdown-item dropdown-notifications-item" href="{{ route('companies') }}">
                        <span class="dropdown-notifications-item-img kh-message-avatar">TH</span>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">Could you review our employer verification today?</div>
                            <div class="dropdown-notifications-item-content-details">Tech Horizon · 12m</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-item" href="{{ route('companies') }}">
                        <span class="dropdown-notifications-item-img kh-message-avatar kh-message-avatar--blue">ABA</span>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">Our new retail role is ready for candidate matching.</div>
                            <div class="dropdown-notifications-item-content-details">ABA Bank · 1h</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-item" href="{{ route('resumes.index') }}">
                        <span class="dropdown-notifications-item-img kh-message-avatar kh-message-avatar--gold">AC</span>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">The design portfolio shortlist has been updated.</div>
                            <div class="dropdown-notifications-item-content-details">Angkor Creative · 3h</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-item" href="{{ route('resumes.index') }}">
                        <span class="dropdown-notifications-item-img kh-message-avatar kh-message-avatar--violet">ML</span>
                        <div class="dropdown-notifications-item-content">
                            <div class="dropdown-notifications-item-content-text">Seven new candidates are available for screening.</div>
                            <div class="dropdown-notifications-item-content-details">Matching team · Today</div>
                        </div>
                    </a>
                    <a class="dropdown-item dropdown-notifications-footer" href="{{ route('resumes.index') }}">View candidates</a>
                </div>
            </li>
            <!-- User Dropdown-->
            <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
                <a class="btn btn-icon btn-transparent-dark dropdown-toggle kh-user-avatar" id="navbarDropdownUserImage"
                    href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" aria-label="Open account menu">
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
                        Account
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        &nbsp{{ __('Logout') }}
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
                        <div class="sidenav-menu-heading d-sm-none">Account</div>
                        <!-- Sidenav Link (Alerts)-->
                        <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                        <a class="nav-link d-sm-none" href="#!">
                            <div class="nav-link-icon"><i data-feather="bell"></i></div>
                            Alerts
                            <span class="badge bg-warning-soft text-warning ms-auto">4 New!</span>
                        </a>
                        <!-- Sidenav Link (Messages)-->
                        <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                        <a class="nav-link d-sm-none" href="#!">
                            <div class="nav-link-icon"><i data-feather="mail"></i></div>
                            Messages
                            <span class="badge bg-success-soft text-success ms-auto">2 New!</span>
                        </a>
                        <!-- Sidenav Menu Heading (Core)-->
                        <div class="sidenav-menu-heading">Core</div>
                        <!-- Sidenav Accordion (Dashboard)-->
                        <a class="nav-link collapsed {{ request()->routeIs('home') ? 'kh-nav-parent-active' : '' }}"
                            href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#collapseDashboards"
                            aria-expanded="{{ request()->routeIs('home') ? 'true' : 'false' }}"
                            aria-controls="collapseDashboards">
                            <div class="nav-link-icon"><i data-feather="activity"></i></div>
                            Dashboards
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('home') ? 'show' : '' }}"
                            id="collapseDashboards" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active fw-bold' : '' }}"
                                    href="{{ route('home') }}">
                                    Overview
                                    <span class="badge bg-primary-soft text-primary ms-auto">Live</span>
                                </a>
                            </nav>
                        </div>
                        <!-- Sidenav Heading (Custom)-->
                        <div class="sidenav-menu-heading">Custom</div>
                        <!-- Sidenav Accordion (Pages)-->
                        <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                            <div class="nav-link-icon"><i data-feather="grid"></i></div>
                            Pages
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePages" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPagesMenu">
                                <!-- Nested Sidenav Accordion (Pages -> Account)-->
                                <a class="nav-link collapsed {{ setActive(['account-profile', 'admin/account-billing', 'admin/account-billing/*', 'account-security', 'account-notifications']) }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#pagesCollapseAccount"
                                    aria-expanded="{{ request()->is('account-*', 'admin/account-billing*') ? 'true' : 'false' }}"
                                    aria-controls="pagesCollapseAccount">
                                    Account
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>
                                <div class="collapse {{ request()->is('account-*', 'admin/account-billing*') ? 'show' : '' }}"
                                    id="pagesCollapseAccount" data-bs-parent="#accordionSidenavPagesMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ setActive('account-profile') }}"
                                            href="{{ url('account-profile') }}">Profile</a>
                                        <a class="nav-link {{ setActive(['admin/account-billing', 'admin/account-billing/*']) }}"
                                            href="{{ url('admin/account-billing') }}">Billing</a>
                                        <a class="nav-link {{ setActive('account-security') }}"
                                            href="{{ url('account-security') }}">Security</a>
                                        <a class="nav-link {{ setActive('account-notifications') }}"
                                            href="{{ url('account-notifications') }}">Notifications</a>
                                    </nav>
                                </div>
                            </nav>
                        </div>
                        {{-- Every child of this accordion is either admin-only or
                             admin+employer, so an employee has nothing to see inside
                             it — hide the whole accordion rather than leave it empty. --}}
                        @if ($adminUser?->isAdmin() || $adminUser?->isEmployer())
                        <!-- Sidenav Accordion (Applications)-->
                        <!-- Applications Menu -->
                        <a class="nav-link collapsed {{ request()->is('applications/*') || request()->routeIs('users', 'user.*', 'job-posts.*', 'resumes.*') || request()->is('user-management-*') ? 'kh-nav-parent-active' : '' }}"
                            href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseApps"
                            aria-expanded="{{ request()->is('applications/*') || request()->routeIs('users', 'user.*', 'job-posts.*', 'resumes.*') || request()->is('user-management-*') ? 'true' : 'false' }}"
                            aria-controls="collapseApps">
                            <div class="nav-link-icon">
                                <i data-feather="globe"></i>
                            </div>
                            Applications
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
                                    User Management
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('users', 'user.*') || request()->is('user-management-*') ? 'show' : '' }}"
                                    id="appsCollapseUserManagement" data-bs-parent="#accordionSidenavAppsMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->is('admin/users') ? 'active fw-bold' : '' }}"
                                            href="{{ route('resumes.index') }}">Users List</a>
                                        <a class="nav-link {{ request()->routeIs('user.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('user.create') }}">Add New User</a>
                                    </nav>
                                </div>
                                @endif

                                <!-- Job Post Management Inside Applications -->
                                <a class="nav-link collapsed {{ request()->routeIs('job-posts.*', 'featured-jobs*') ? 'kh-nav-parent-active fw-bold' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#appsCollapseJobPosts"
                                    aria-expanded="{{ request()->routeIs('job-posts.*', 'featured-jobs*') ? 'true' : 'false' }}"
                                    aria-controls="appsCollapseJobPosts">
                                    Job Management
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('job-posts.*', 'featured-jobs*') ? 'show' : '' }}"
                                    id="appsCollapseJobPosts" data-bs-parent="#accordionSidenavAppsMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->routeIs('job-posts.index') ? 'active fw-bold' : '' }}"
                                            href="{{ route('job-posts.index') }}">Job Posts</a>
                                        @if ($adminUser?->hasPermission(\App\Models\Permission::JOB_CREATE))
                                        <a class="nav-link {{ request()->routeIs('job-posts.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('job-posts.create') }}">Add Job Post</a>
                                        @endif
                                        @if ($adminUser?->isAdmin())
                                        <a class="nav-link {{ request()->routeIs('featured-jobs*') ? 'active fw-bold' : '' }}"
                                            href="{{ route('featured-jobs') }}">Featured Jobs</a>
                                        @endif
                                    </nav>
                                </div>

                                @php
                                    $canSeeResumes = collect([
                                        \App\Models\Permission::RESUME_CREATE,
                                        \App\Models\Permission::RESUME_EDIT,
                                        \App\Models\Permission::RESUME_DELETE,
                                        \App\Models\Permission::RESUME_DOWNLOAD,
                                    ])->contains(fn ($code) => $adminUser?->hasPermission($code));
                                @endphp
                                @if ($canSeeResumes)
                                <!-- Resume Management Inside Applications -->
                                <a class="nav-link collapsed {{ request()->routeIs('resumes.*') ? 'kh-nav-parent-active fw-bold' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#appsCollapseResumes"
                                    aria-expanded="{{ request()->routeIs('resumes.*') ? 'true' : 'false' }}"
                                    aria-controls="appsCollapseResumes">
                                    Resume Management
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('resumes.*') ? 'show' : '' }}"
                                    id="appsCollapseResumes" data-bs-parent="#accordionSidenavAppsMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->routeIs('resumes.index') ? 'active fw-bold' : '' }}"
                                            href="{{ route('resumes.index') }}">Resumes</a>
                                        @if ($adminUser?->hasPermission(\App\Models\Permission::RESUME_CREATE))
                                        <a class="nav-link {{ request()->routeIs('resumes.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('resumes.create') }}">Add Resume</a>
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
                            Companies
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
                                    Compliance
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('compliance.*') ? 'show' : '' }}"
                                    id="companiesCollapseCompliance" data-bs-parent="#accordionSidenavCompaniesMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->routeIs('compliance.index') ? 'active fw-bold' : '' }}"
                                            href="{{ route('compliance.index') }}">Compliance Register</a>
                                        <a class="nav-link {{ request()->routeIs('compliance.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('compliance.create') }}">Add Record</a>
                                    </nav>
                                </div>
                                @endif

                                <!-- Company Directory Inside Companies -->
                                <a class="nav-link collapsed {{ request()->routeIs('companies', 'companies.*') ? 'kh-nav-parent-active fw-bold' : '' }}"
                                    href="javascript:void(0);" data-bs-toggle="collapse"
                                    data-bs-target="#companiesCollapseDirectory"
                                    aria-expanded="{{ request()->routeIs('companies', 'companies.*') ? 'true' : 'false' }}"
                                    aria-controls="companiesCollapseDirectory">
                                    Company Directory
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse {{ request()->routeIs('companies', 'companies.*') ? 'show' : '' }}"
                                    id="companiesCollapseDirectory" data-bs-parent="#accordionSidenavCompaniesMenu">
                                    <nav class="sidenav-menu-nested nav">
                                        <a class="nav-link {{ request()->routeIs('companies') ? 'active fw-bold' : '' }}"
                                            href="{{ route('companies') }}">All Companies</a>
                                        @if ($adminUser?->isAdmin())
                                        <a class="nav-link {{ request()->routeIs('companies.create') ? 'active fw-bold' : '' }}"
                                            href="{{ route('companies.create') }}">Add Company</a>
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
                            My Applications
                        </a>
                        @endif
                        @if ($adminUser?->isAdmin())
                        <!-- Sidenav Heading (Settings)-->
                        <div class="sidenav-menu-heading">Settings</div>
                        <!-- Sidenav Accordion (Component)-->
                        <a class="nav-link collapsed {{ request()->routeIs('components.*') ? 'kh-nav-parent-active' : '' }}"
                            href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#collapseComponent"
                            aria-expanded="{{ request()->routeIs('components.*') ? 'true' : 'false' }}"
                            aria-controls="collapseComponent">
                            <div class="nav-link-icon"><i data-feather="package"></i></div>
                            Component
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ request()->routeIs('components.*') ? 'show' : '' }}"
                            id="collapseComponent" data-bs-parent="#accordionSidenav">
                            <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavComponentMenu">
                                @foreach ([
                                    'locations' => 'Locations',
                                    'departments' => 'Departments',
                                    'job-types' => 'Job Types',
                                ] as $componentType => $componentLabel)
                                    @php
                                        $componentActive = request()->routeIs('components.*') && request()->route('type') === $componentType;
                                        $componentCollapseId = 'componentCollapse' . \Illuminate\Support\Str::studly($componentType);
                                    @endphp
                                    <a class="nav-link collapsed {{ $componentActive ? 'kh-nav-parent-active fw-bold' : '' }}"
                                        href="javascript:void(0);" data-bs-toggle="collapse"
                                        data-bs-target="#{{ $componentCollapseId }}"
                                        aria-expanded="{{ $componentActive ? 'true' : 'false' }}"
                                        aria-controls="{{ $componentCollapseId }}">
                                        {{ $componentLabel }}
                                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse {{ $componentActive ? 'show' : '' }}"
                                        id="{{ $componentCollapseId }}" data-bs-parent="#accordionSidenavComponentMenu">
                                        <nav class="sidenav-menu-nested nav">
                                            <a class="nav-link {{ request()->routeIs('components.index') && request()->route('type') === $componentType ? 'active fw-bold' : '' }}"
                                                href="{{ route('components.index', $componentType) }}">All {{ $componentLabel }}</a>
                                            <a class="nav-link {{ request()->routeIs('components.create') && request()->route('type') === $componentType ? 'active fw-bold' : '' }}"
                                                href="{{ route('components.create', $componentType) }}">Add {{ \Illuminate\Support\Str::singular($componentLabel) }}</a>
                                        </nav>
                                    </div>
                                @endforeach
                            </nav>
                        </div>

                        <!-- Sidenav Link (User Role & Permission) -->
                        <a class="nav-link {{ request()->routeIs('roles') ? 'active fw-bold' : '' }}"
                            href="{{ route('roles') }}">
                            <div class="nav-link-icon"><i data-feather="shield"></i></div>
                            User Role &amp; Permission
                        </a>
                        @endif
                    </div>
                </div>
                <!-- Sidenav Footer-->
                <div class="sidenav-footer">
                    <div class="sidenav-footer-content">
                        <div class="sidenav-footer-subtitle">Logged in as:</div>
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
                            <small>&copy; {{ date('Y') }}. All rights reserved.</small>
                        </div>
                    </div>

                    <nav class="kh-admin-footer__links" aria-label="Footer navigation">
                        <a href="{{ route('about') }}">About</a>
                        <a href="{{ url('/') }}" target="_blank" rel="noopener">
                            <span>View website</span>
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
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Ready to Leave?') }}</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-link" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <a class="btn btn-danger" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
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
