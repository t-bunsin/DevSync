<nav class="navbar navbar-expand-lg fixed-top shadow-sm" 
     style="backdrop-filter: blur(12px); background: rgba(33, 37, 41, 0.85);">
    <div class="container-fluid px-3">
        
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-warning d-flex align-items-center" 
           href="{{ url('/') }}" style="letter-spacing: 1px;">
            <i class="fas fa-search me-2"></i> JobFinder
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu Items -->
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto align-items-center gap-2 small">

                <!-- Main Links -->
                <li class="nav-item">
                    <a class="nav-link text-light px-3 rounded-pill hover-link" href="{{ url('/jobs') }}">
                        <i class="fas fa-briefcase me-1"></i> Jobs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light px-3 rounded-pill hover-link" href="{{ url('/companies') }}">
                        <i class="fas fa-building me-1"></i> Companies
                    </a>
                </li>

                <!-- Pages Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-light px-3 rounded-pill hover-link" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-th-large me-1"></i> Pages
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-sm">
                        <li><a class="dropdown-item" href="{{ url('/career') }}">Career Advice</a></li>
                        <li><a class="dropdown-item" href="{{ url('/about') }}">About Us</a></li>
                    </ul>
                </li>

                <!-- Language -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-light px-3 rounded-pill hover-link" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-language me-1"></i> {{ app()->getLocale() == 'kh' ? 'ខ្មែរ' : 'EN' }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow-sm">
                        <li><a class="dropdown-item" href="#">English</a></li>
                        <li><a class="dropdown-item" href="#">ភាសាខ្មែរ</a></li>
                    </ul>
                </li>

                <!-- Auth -->
                @auth
                    <li class="nav-item">
                        <a class="nav-link text-light hover-icon" href="#">
                            <i class="fas fa-comment-alt"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light hover-icon" href="#">
                            <i class="fas fa-bell"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center text-light" href="#" data-bs-toggle="dropdown">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" class="rounded-circle border border-light" 
                                 style="height: 32px; width: 32px; object-fit: cover;" alt="Profile">
                            <span class="ms-2">{{ Str::limit(Auth::user()->name, 10) }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow-sm">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm px-3 rounded-pill" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm px-3 rounded-pill" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Smooth hover for links */
    .hover-link:hover {
        background: rgba(255, 255, 255, 0.15);
        transition: 0.3s;
    }
    /* Icon hover glow */
    .hover-icon:hover i {
        color: #ffc107;
        transition: color 0.3s;
    }
    /* Dropdown hover effect */
    .dropdown-menu a:hover {
        background-color: rgba(255, 193, 7, 0.2);
    }
</style>
