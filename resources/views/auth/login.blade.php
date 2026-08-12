@extends('layouts.auth')

@section('title', 'Sign in | KH-WORKS')
@section('body-class', 'jf-auth-body')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
@endpush

@section('main-content')
    @php
        $points = [
            ['icon' => 'fa-bolt', 'title' => 'Fast job matching', 'body' => 'Roles that fit your skills and goals.'],
            ['icon' => 'fa-shield-halved', 'title' => 'Secure account access', 'body' => 'Your profile and applications stay protected.'],
            ['icon' => 'fa-chart-line', 'title' => 'Track your progress', 'body' => 'Every application and next step in one place.'],
        ];
    @endphp

    <main class="jf-auth">
        <div class="jf-auth__glow jf-auth__glow--one" aria-hidden="true"></div>
        <div class="jf-auth__glow jf-auth__glow--two" aria-hidden="true"></div>

        <div class="jf-auth__container">
            <section class="jf-auth__panel">
                <a class="jf-auth__brand" href="{{ url('/') }}">
                    <span class="jf-auth__brand-mark"><i class="fas fa-briefcase"></i></span>
                    <span>KH-<span>WORKS</span></span>
                </a>

                <div class="jf-auth__panel-copy is-active">
                    <span class="jf-auth__eyebrow">
                        <i class="fas fa-hand-sparkles" aria-hidden="true"></i>
                        Welcome back
                    </span>

                    <h1>Sign in and continue your career journey.</h1>
                    <p>Pick up where you left off — saved roles, applications in flight, and everything on your dashboard.</p>

                    <ul class="jf-auth__points">
                        @foreach ($points as $point)
                            <li>
                                <span aria-hidden="true"><i class="fas {{ $point['icon'] }}"></i></span>
                                <div>
                                    <strong>{{ $point['title'] }}</strong>
                                    <span>{{ $point['body'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="jf-auth__panel-foot">
                    <i class="fas fa-shield-halved" aria-hidden="true"></i>
                    Job seekers and employers sign in with the same account.
                </p>
            </section>

            <section class="jf-auth__card">
                <header class="jf-auth__card-head">
                    <h2>Sign in</h2>
                    <p>Enter your details below to access your account.</p>
                </header>

                @if (session('status'))
                    <div class="jf-auth__alert jf-auth__alert--success" role="status">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <div><span>{{ session('status') }}</span></div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="jf-auth__alert" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong>We could not sign you in.</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="jf-auth__form" novalidate>
                    @csrf

                    <div class="jf-auth__field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                            placeholder="you@example.com" autocomplete="email" required autofocus
                            @error('email') aria-invalid="true" @enderror>
                        @error('email')<small class="jf-auth__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-auth__field">
                        <label for="password">Password</label>
                        <span class="jf-auth__control">
                            <input id="password" name="password" type="password" placeholder="Your password"
                                autocomplete="current-password" required @error('password') aria-invalid="true" @enderror>
                            <button type="button" data-toggle-password="password" aria-label="Show password">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </span>
                        @error('password')<small class="jf-auth__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-auth__row">
                        <label class="jf-auth__remember" for="remember">
                            <input id="remember" name="remember" type="checkbox" @checked(old('remember'))>
                            <span class="jf-auth__checkbox" aria-hidden="true"><i class="fas fa-check"></i></span>
                            <span>Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="jf-auth__row-link" href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>

                    <button class="jf-auth__submit" type="submit">
                        <i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i>
                        <span>Sign in</span>
                    </button>
                </form>

                <footer class="jf-auth__card-foot">
                    <span>New to KH-WORKS? <a href="{{ route('register') }}">Create an account</a></span>
                    <a class="jf-auth__back" href="{{ url('/') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to jobs</a>
                </footer>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}?v={{ filemtime(public_path('js/auth.js')) }}"></script>
@endpush
