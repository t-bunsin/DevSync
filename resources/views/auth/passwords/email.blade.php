@extends('layouts.auth')

@section('title', 'Password recovery | ZIN-WORKS')
@section('body-class', 'jf-auth-body')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
@endpush

@section('main-content')
    @php
        $expire = config('auth.passwords.users.expire', 60);

        $points = [
            ['icon' => 'fa-envelope-open-text', 'title' => 'A link, not a password', 'body' => 'We email a one-time link — nothing to remember.'],
            ['icon' => 'fa-clock', 'title' => 'Valid for ' . $expire . ' minutes', 'body' => 'Request another one if it runs out.'],
            ['icon' => 'fa-lock', 'title' => 'Your account stays locked', 'body' => 'The old password works until you set a new one.'],
        ];

        // The link has been sent: the form stops being the point of the page,
        // so the card leads with what to do next and keeps the form as a retry.
        $sent = (bool) session('status');
    @endphp

    <main class="jf-auth">
        <div class="jf-auth__glow jf-auth__glow--one" aria-hidden="true"></div>
        <div class="jf-auth__glow jf-auth__glow--two" aria-hidden="true"></div>

        <div class="jf-auth__container">
            <section class="jf-auth__panel">
                <a class="jf-auth__brand" href="{{ url('/') }}">
                    <span class="jf-auth__brand-mark"><i class="fas fa-briefcase" aria-hidden="true"></i></span>
                    <span class="jf-auth__brand-divider" aria-hidden="true"></span>
                    <span class="jf-auth__brand-text">
                        <span class="jf-auth__brand-word">ZIN-<span>WORKS</span></span>
                        <span class="jf-auth__brand-tagline">Build your dream job</span>
                    </span>
                </a>

                <div class="jf-auth__panel-copy is-active">
                    <span class="jf-auth__eyebrow">
                        <i class="fas fa-key" aria-hidden="true"></i>
                        Password recovery
                    </span>

                    <h1>Locked out? Get back in from your inbox.</h1>
                    <p>Give us the email on your account and we'll send a reset link. Your applications, saved roles and dashboard are waiting exactly where you left them.</p>

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
                    Didn't ask for this? Ignore the email and nothing changes.
                </p>
            </section>

            <section class="jf-auth__card">
                <header class="jf-auth__card-head">
                    @if ($sent)
                        <h2>Check your email</h2>
                        <p>The link is on its way. It expires in {{ $expire }} minutes.</p>
                    @else
                        <h2>Reset your password</h2>
                        <p>Enter your email address and we'll send you a link to choose a new password.</p>
                    @endif
                </header>

                @if ($sent)
                    <div class="jf-auth__alert jf-auth__alert--success" role="status">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <div><span>{{ session('status') }}</span></div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="jf-auth__alert" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong>We could not send the link.</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="jf-auth__form" novalidate>
                    @csrf

                    <div class="jf-auth__field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                            placeholder="you@example.com" autocomplete="email" required autofocus
                            @error('email') aria-invalid="true" @enderror>
                        @error('email')
                            <small class="jf-auth__error">{{ $message }}</small>
                        @else
                            <small class="jf-auth__hint">Use the address you signed up with.</small>
                        @enderror
                    </div>

                    <button class="jf-auth__submit" type="submit">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        <span>{{ $sent ? 'Send the link again' : 'Send reset link' }}</span>
                    </button>
                </form>

                @if ($sent)
                    <p class="jf-auth__resend">
                        <span>Nothing after a minute? Check your spam folder, or send it again.</span>
                    </p>
                @endif

                <footer class="jf-auth__card-foot">
                    <span>Remembered it? <a href="{{ route('login') }}">Sign in</a></span>
                    <a class="jf-auth__back" href="{{ route('register') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Create an account</a>
                </footer>
            </section>
        </div>
    </main>
@endsection
