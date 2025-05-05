<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm py-2">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-warning small" href="{{ url('/') }}">
            JobFinder
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu Items -->
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto align-items-center gap-2 small">
                <!-- Main Links -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/jobs') }}"><i class="fas fa-briefcase me-1"></i> Jobs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/companies') }}"><i class="fas fa-building me-1"></i> Companies</a>
                </li>

                <!-- Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-th-large me-1"></i> Pages
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ url('/career') }}">Career Advice</a></li>
                        <li><a class="dropdown-item" href="{{ url('/about') }}">About Us</a></li>
                    </ul>
                </li>

                <!-- Language -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-language me-1"></i> {{ app()->getLocale() == 'kh' ? 'ខ្មែរ' : 'EN' }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">English</a></li>
                        <li><a class="dropdown-item" href="#">ភាសាខ្មែរ</a></li>
                    </ul>
                </li>

                <!-- Auth -->
                @auth
                    <li class="nav-item">
                        <i class="fas fa-comment-alt text-white small"></i>
                    </li>
                    <li class="nav-item">
                        <i class="fas fa-bell text-white small"></i>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" class="rounded-circle" style="height: 28px;" alt="Profile">
                            <span class="ms-1">{{ Str::limit(Auth::user()->name, 10) }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
