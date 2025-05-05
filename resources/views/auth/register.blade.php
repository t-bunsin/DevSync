@extends('layouts.auth')

@section('main-content')
<div id="layoutAuthentication">
    <div id="layoutAuthentication_content">
        <main>
            <div class="container-xl px-4">
                <div class="row justify-content-center">
                    <!-- Create Organization-->
                    <div class="col-xl-5 col-lg-6 col-md-8 col-sm-11 mt-4">
                        <div class="card text-center h-100">
                            <div class="card-body px-5 pt-5 d-flex flex-column">
                                <div>
                                    <div class="h3 text-primary">Create</div>
                                    <p class="text-muted mb-4">Create an organization and invite new members</p>
                                </div>
                                <div class="icons-org-create align-items-center mx-auto mt-auto">
                                    <i class="icon-users" data-feather="users"></i>
                                    <i class="icon-plus fas fa-plus"></i>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent px-5 py-4">
                                <div class="small text-center"><a class="btn btn-block btn-primary" href="multi-tenant-create.html">Create new organization</a></div>
                            </div>
                        </div>
                    </div>
                    <!-- Join Organization-->
                    <div class="col-xl-5 col-lg-6 col-md-8 col-sm-11 mt-4">
                        <div class="card text-center h-100">
                            <div class="card-body px-5 pt-5 d-flex flex-column align-items-between">
                                <div>
                                    <div class="h3 text-secondary">Join</div>
                                    <p class="text-muted mb-4">Enter an access code or request access to an existing organization</p>
                                </div>
                                <div class="icons-org-join align-items-center mx-auto">
                                    <i class="icon-user" data-feather="user"></i>
                                    <i class="icon-arrow fas fa-long-arrow-alt-right"></i>
                                    <i class="icon-users" data-feather="users"></i>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent px-5 py-4">
                                <div class="small text-center"><a class="btn btn-block btn-secondary" href="multi-tenant-join.html">Join an organization</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
{{-- <div id="layoutAuthentication">
    <div id="layoutAuthentication_content">
        <main>
            <div class="container-xl px-4">
                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <!-- Basic registration form-->
                        <div class="card shadow-lg border-0 rounded-lg mt-5">
                            <div class="card-header justify-content-center"><h3 class="fw-light my-4">Create Account</h3></div>
                            <div class="card-body">
                                <!-- Registration form-->
                                <form>
                                    <!-- Form Row-->
                                    <div class="row gx-3">
                                        <div class="col-md-6">
                                            <!-- Form Group (first name)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputFirstName">First Name</label>
                                                <input class="form-control" id="inputFirstName" type="text" placeholder="Enter first name" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Form Group (last name)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputLastName">Last Name</label>
                                                <input class="form-control" id="inputLastName" type="text" placeholder="Enter last name" />
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Form Group (email address)            -->
                                    <div class="mb-3">
                                        <label class="small mb-1" for="inputEmailAddress">Email</label>
                                        <input class="form-control" id="inputEmailAddress" type="email" aria-describedby="emailHelp" placeholder="Enter email address" />
                                    </div>
                                    <!-- Form Row    -->
                                    <div class="row gx-3">
                                        <div class="col-md-6">
                                            <!-- Form Group (password)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputPassword">Password</label>
                                                <input class="form-control" id="inputPassword" type="password" placeholder="Enter password" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Form Group (confirm password)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputConfirmPassword">Confirm Password</label>
                                                <input class="form-control" id="inputConfirmPassword" type="password" placeholder="Confirm password" />
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Form Group (create account submit)-->
                                    <a class="btn btn-primary btn-block" href="auth-login-basic.html">Create Account</a>
                                </form>
                            </div>
                            <div class="card-footer text-center">
                                <div class="small"><a href="auth-login-basic.html">Have an account? Go to login</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div id="layoutAuthentication_footer">
        <footer class="footer-admin mt-auto footer-dark">
            <div class="container-xl px-4">
                <div class="row">
                    <div class="col-md-6 small">Copyright © Your Website 2021</div>
                    <div class="col-md-6 text-md-end small">
                        <a href="#!">Privacy Policy</a>
                        ·
                        <a href="#!">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div> --}}
{{-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">{{ __('Register') }}</h1>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger border-left-danger" role="alert">
                                        <ul class="pl-4 my-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('register') }}" class="user">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-user" name="name" placeholder="{{ __('Name') }}" value="{{ old('name') }}" required autofocus>
                                    </div>

                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-user" name="last_name" placeholder="{{ __('Last Name') }}" value="{{ old('last_name') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <input type="email" class="form-control form-control-user" name="email" placeholder="{{ __('E-Mail Address') }}" value="{{ old('email') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user" name="password" placeholder="{{ __('Password') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user" name="password_confirmation" placeholder="{{ __('Confirm Password') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            {{ __('Register') }}
                                        </button>
                                    </div>
                                </form>

                                <hr>

                                <div class="text-center">
                                    <a class="small" href="{{ route('login') }}">
                                        {{ __('Already have an account? Login!') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

@endsection
<script src="https://sb-admin-pro.startbootstrap.com/js/scripts.js"></script>
