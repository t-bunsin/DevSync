@extends('layouts.auth')

@section('title', 'Sign In')
@section('body-class', 'auth-body auth-body--login')

@push('styles')
    <style>
        .auth-body {
            min-height: 100vh;
            margin: 0;
            font-family: "Metropolis", "Segoe UI", sans-serif;
            color: #000;
            background:
                radial-gradient(circle at top left, rgba(110, 86, 245, 0.24), transparent 30%),
                radial-gradient(circle at bottom right, rgba(77, 106, 241, 0.2), transparent 26%),
                linear-gradient(135deg, #f4f6ff 0%, #eef2ff 45%, #f9fbff 100%);
        }

        .auth-login {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .auth-login__submit {
            border-radius: 0 !important;
        }

        .auth-login__shape {
            position: absolute;
            border-radius: 999px;
            filter: blur(12px);
            opacity: 0.55;
            pointer-events: none;
        }

        .auth-login__shape--one {
            top: 90px;
            left: -80px;
            width: 260px;
            height: 260px;
            background: linear-gradient(135deg, rgba(109, 68, 245, 0.3), rgba(77, 106, 241, 0.12));
        }

        .auth-login__shape--two {
            right: -60px;
            bottom: 90px;
            width: 240px;
            height: 240px;
            background: linear-gradient(135deg, rgba(77, 106, 241, 0.2), rgba(113, 87, 255, 0.12));
        }

        .auth-login__container {
            position: relative;
            z-index: 1;
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(300px, 0.95fr) minmax(360px, 0.85fr);
            gap: 28px;
            align-items: center;
            padding: 36px 0;
        }

        .auth-login__brand-panel {
            position: relative;
            padding: 44px;
            color: #fff;
            border-radius: 34px;
            background:
                linear-gradient(160deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.03)),
                linear-gradient(135deg, #0b466f, #0b466f);
            box-shadow: 0 30px 60px rgba(11, 70, 111, 0.24);
            overflow: hidden;
        }

        .auth-login__brand-panel::before,
        .auth-login__brand-panel::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .auth-login__brand-panel::before {
            top: -70px;
            right: -20px;
            width: 220px;
            height: 220px;
        }

        .auth-login__brand-panel::after {
            left: -50px;
            bottom: -90px;
            width: 260px;
            height: 260px;
        }

        .auth-login__brand {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 34px;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.04em;
        }

        .auth-login__brand span span {
            color: #dcd2ff;
        }

        .auth-login__brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 62px;
            height: 62px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
            font-size: 1.5rem;
        }

        .auth-login__eyebrow {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            margin-bottom: 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .auth-login__title {
            position: relative;
            z-index: 1;
            max-width: 10ch;
            margin: 0 0 14px;
            font-size: clamp(2.5rem, 4vw, 4rem);
            line-height: 0.98;
            letter-spacing: -0.05em;
        }

        .auth-login__text {
            position: relative;
            z-index: 1;
            max-width: 44ch;
            margin: 0 0 28px;
            color: rgba(255, 255, 255, 0.84);
            font-size: 1rem;
            line-height: 1.7;
        }

        .auth-login__features {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .auth-login__features li {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }

        .auth-login__features i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.16);
            font-size: 0.95rem;
        }

        .auth-login__features strong {
            display: block;
            margin-bottom: 2px;
            font-size: 0.96rem;
        }

        .auth-login__features span {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.86rem;
        }

        .auth-login__form-card {
            padding: 36px;
            border: 1px solid rgba(223, 228, 248, 0.9);
            border-radius: 34px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 24px 54px rgba(82, 94, 172, 0.12);
            backdrop-filter: blur(18px);
        }

        .auth-login__form-top {
            margin-bottom: 24px;
        }

        .auth-login__form-top h2 {
            margin: 0 0 8px;
            font-size: 2rem;
            letter-spacing: -0.04em;
        }

        .auth-login__form-top p {
            margin: 0;
            color: #6f7896;
            line-height: 1.7;
        }

        .auth-login__alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            color: #9f2c3f;
            background: #fff3f5;
            border: 1px solid #ffd8de;
            border-radius: 18px;
        }

        .auth-login__alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .auth-login__form {
            display: grid;
            gap: 18px;
        }

        .auth-login__field {
            display: grid;
            gap: 8px;
        }

        .auth-login__field label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: #000;
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .auth-login__field label i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            color: #0b466f;
            font-size: 0.82rem;
            line-height: 1;
        }

        .auth-login__input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .auth-login__input {
            width: 100%;
            min-height: 58px;
            padding: 0 18px;
            color: #000;
            background: #fff;
            border: 1px solid #dfe5f8;
            border-radius: 18px;
            box-shadow: 0 12px 24px rgba(94, 103, 177, 0.06);
            outline: none;
            line-height: 1.2;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .auth-login__input::placeholder {
            color: #98a1bb;
        }

        .auth-login__input:focus {
            border-color: #917dff;
            box-shadow: 0 0 0 4px rgba(106, 66, 244, 0.08);
        }

        .auth-login__row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .auth-login__remember {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #5f6b88;
            font-weight: 500;
        }

        .auth-login__remember input {
            width: 18px;
            height: 18px;
            accent-color: #0b466f;
        }

        .auth-login__link {
            color: #0b466f;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-login__link:hover {
            text-decoration: underline;
        }

        .auth-login__submit {
            width: 100%;
            justify-content: center;
            min-height: 58px;
            color: #fff;
            background: #0b466f;
            border: 0;
            border-radius: 20px;
            box-shadow: 0 20px 28px rgba(11, 70, 111, 0.22);
        }

        .auth-login__footer {
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid #ebeffc;
            text-align: center;
            color: #6f7896;
        }

        .auth-login__footer a {
            color: #0b466f;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-login__footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            .auth-login__container {
                grid-template-columns: 1fr;
            }

            .auth-login__brand-panel,
            .auth-login__form-card {
                padding: 28px;
            }

            .auth-login__title {
                max-width: none;
                font-size: 2.6rem;
            }
        }

        @media (max-width: 576px) {
            .auth-login__container {
                width: min(100% - 20px, 1180px);
                padding: 18px 0;
            }

            .auth-login__brand {
                font-size: 1.6rem;
            }

            .auth-login__brand-icon {
                width: 54px;
                height: 54px;
            }

            .auth-login__title,
            .auth-login__form-top h2 {
                font-size: 1.8rem;
            }

            .auth-login__form-card,
            .auth-login__brand-panel {
                padding: 22px;
                border-radius: 26px;
            }
        }
    </style>
@endpush

@section('main-content')
    <main class="auth-login">
        <div class="auth-login__shape auth-login__shape--one"></div>
        <div class="auth-login__shape auth-login__shape--two"></div>

        <div class="auth-login__container">
            <section class="auth-login__brand-panel">
                <div class="auth-login__brand">
                    <span class="auth-login__brand-icon">
                        <i class="fas fa-briefcase"></i>
                    </span>
                    <span>KH-<span>WORKS</span></span>
                </div>

                <span class="auth-login__eyebrow">
                    <i class="fas fa-sparkles"></i>
                    Welcome Back
                </span>

                <h1 class="auth-login__title">Sign in and continue your career journey</h1>
                <p class="auth-login__text">Access your dashboard, saved jobs, applications, and personalized recommendations in one polished workspace.</p>

                <ul class="auth-login__features">
                    <li>
                        <i class="fas fa-bolt"></i>
                        <div>
                            <strong>Fast job matching</strong>
                            <span>Discover roles that fit your skills and goals.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-shield-heart"></i>
                        <div>
                            <strong>Secure account access</strong>
                            <span>Your profile and applications stay protected.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-chart-line"></i>
                        <div>
                            <strong>Track application progress</strong>
                            <span>Stay on top of every step from one place.</span>
                        </div>
                    </li>
                </ul>
            </section>

            <section class="auth-login__form-card">
                <div class="auth-login__form-top">
                    <h2>Login</h2>
                    <p>Enter your details below to access your account.</p>
                </div>

                @if ($errors->any())
                    <div class="auth-login__alert" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="auth-login__form">
                    @csrf

                    <div class="auth-login__field">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </label>
                        <div class="auth-login__input-wrap">
                            <input id="email" type="email" class="auth-login__input" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                        </div>
                    </div>

                    <div class="auth-login__field">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            <span>Password</span>
                        </label>
                        <div class="auth-login__input-wrap">
                            <input id="password" type="password" class="auth-login__input" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="auth-login__row">
                        <label class="auth-login__remember" for="remember">
                            <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="auth-login__link" href="{{ route('password.request') }}">Forgot Password?</a>
                        @endif
                    </div>

                    <button type="submit" class="jf-btn auth-login__submit">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        <span>{{ __('Login') }}</span>
                    </button>
                </form>

                <div class="auth-login__footer">
                    Don’t have an account?
                    <a href="{{ route('register') }}">Create one</a>
                </div>
            </section>
        </div>
    </main>
@endsection
