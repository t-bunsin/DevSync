<!-- Header Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            <img src="https://www.simplyhired.com/next-assets/simplyhired-logo.svg" alt="Logo" style="height: 40px;">
            <span class="ms-2">JobFinder</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Left Nav -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#">Jobs</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Companies</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Career Advice</a></li>
            </ul>

            <!-- Right Nav -->
            <ul class="navbar-nav ms-auto align-items-center gap-3">
                @auth
                    <li class="nav-item position-relative">
                        <i class="fas fa-comment-alt text-white fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                    </li>
                    <li class="nav-item position-relative">
                        <i class="fas fa-bell text-white fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">5</span>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" class="rounded-circle" style="height: 35px;" alt="Profile">
                            <span class="ms-2">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Dashboard</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="btn btn-outline-light" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="btn btn-light" href="{{ route('register') }}">Register</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
