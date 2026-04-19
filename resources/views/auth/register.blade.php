@extends('layouts.auth')

@section('title', 'Create Account')
@section('body-class', 'auth-body auth-body--register')

@push('styles')
    <style>
        .auth-body {
            min-height: 100vh;
            margin: 0;
            font-family: "Metropolis", "Segoe UI", sans-serif;
            color: #000;
            background:
                radial-gradient(circle at top left, rgba(11, 70, 111, 0.15), transparent 28%),
                radial-gradient(circle at bottom right, rgba(11, 70, 111, 0.12), transparent 24%),
                linear-gradient(135deg, #f4f8fb 0%, #eef4f8 45%, #f9fbfd 100%);
        }

        .auth-register {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .auth-register__button {
            border-radius: 0 !important;
        }

        .auth-register__shape {
            position: absolute;
            border-radius: 999px;
            filter: blur(10px);
            pointer-events: none;
            opacity: 0.55;
        }

        .auth-register__shape--one {
            top: 70px;
            left: -80px;
            width: 260px;
            height: 260px;
            background: linear-gradient(135deg, rgba(11, 70, 111, 0.18), rgba(11, 70, 111, 0.06));
        }

        .auth-register__shape--two {
            right: -40px;
            bottom: 90px;
            width: 230px;
            height: 230px;
            background: linear-gradient(135deg, rgba(11, 70, 111, 0.14), rgba(11, 70, 111, 0.05));
        }

        .auth-register__container {
            position: relative;
            z-index: 1;
            width: min(1220px, calc(100% - 32px));
            margin: 0 auto;
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(320px, 0.98fr) minmax(380px, 0.9fr);
            gap: 28px;
            align-items: center;
            padding: 36px 0;
        }

        .auth-register__panel {
            position: relative;
            overflow: hidden;
            padding: 44px;
            color: #fff;
            background:
                linear-gradient(165deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02)),
                linear-gradient(135deg, #0b466f, #0b466f);
            border-radius: 34px;
            box-shadow: 0 28px 56px rgba(11, 70, 111, 0.24);
        }

        .auth-register__panel::before,
        .auth-register__panel::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .auth-register__panel::before {
            top: -80px;
            right: -10px;
            width: 220px;
            height: 220px;
        }

        .auth-register__panel::after {
            left: -40px;
            bottom: -100px;
            width: 260px;
            height: 260px;
        }

        .auth-register__brand {
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

        .auth-register__brand span span {
            color: #d4ecff;
        }

        .auth-register__brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 62px;
            height: 62px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.16);
            font-size: 1.45rem;
        }

        .auth-register__eyebrow {
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

        .auth-register__title {
            position: relative;
            z-index: 1;
            max-width: 10ch;
            margin: 0 0 14px;
            font-size: clamp(2.4rem, 4vw, 3.8rem);
            line-height: 0.98;
            letter-spacing: -0.05em;
        }

        .auth-register__text {
            position: relative;
            z-index: 1;
            max-width: 46ch;
            margin: 0 0 28px;
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.7;
        }

        .auth-register__list {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .auth-register__list li {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            backdrop-filter: blur(8px);
        }

        .auth-register__list i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.16);
            font-size: 0.95rem;
        }

        .auth-register__list strong {
            display: block;
            margin-bottom: 2px;
            font-size: 0.96rem;
        }

        .auth-register__list span {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.86rem;
        }

        .auth-register__card {
            padding: 36px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(223, 228, 248, 0.94);
            border-radius: 34px;
            box-shadow: 0 24px 54px rgba(82, 94, 172, 0.12);
            backdrop-filter: blur(16px);
        }

        .auth-register__card-top {
            margin-bottom: 24px;
        }

        .auth-register__card-top h2 {
            margin: 0 0 8px;
            font-size: 2rem;
            letter-spacing: -0.04em;
        }

        .auth-register__card-top p {
            margin: 0;
            color: #5f6b88;
            line-height: 1.7;
        }

        .auth-register__alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            color: #9f2c3f;
            background: #fff3f5;
            border: 1px solid #ffd8de;
            border-radius: 18px;
        }

        .auth-register__alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .auth-register__form {
            display: grid;
            gap: 18px;
        }

        .auth-register__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .auth-register__field {
            display: grid;
            gap: 8px;
        }

        .auth-register__field label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: #000;
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .auth-register__field label i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            color: #0b466f;
            font-size: 0.82rem;
        }

        .auth-register__input {
            width: 100%;
            min-height: 58px;
            padding: 0 18px;
            color: #000;
            background: #fff;
            border: 1px solid #dfe5f8;
            border-radius: 18px;
            box-shadow: 0 12px 24px rgba(94, 103, 177, 0.06);
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .auth-register__input:focus {
            border-color: #0b466f;
            box-shadow: 0 0 0 4px rgba(11, 70, 111, 0.08);
        }

        .auth-register__input::placeholder {
            color: #98a1bb;
        }

        .auth-register__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 58px;
            color: #fff;
            background: #0b466f;
            border: 0;
            border-radius: 20px;
            box-shadow: 0 20px 28px rgba(11, 70, 111, 0.22);
            font-weight: 700;
            cursor: pointer;
        }

        .auth-register__footer {
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid #ebeffc;
            text-align: center;
            color: #6f7896;
        }

        .auth-register__footer a {
            color: #0b466f;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-register__footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            .auth-register__container {
                grid-template-columns: 1fr;
            }

            .auth-register__panel,
            .auth-register__card {
                padding: 28px;
            }

            .auth-register__title {
                max-width: none;
                font-size: 2.5rem;
            }
        }

        @media (max-width: 640px) {
            .auth-register__grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .auth-register__container {
                width: min(100% - 20px, 1220px);
                padding: 18px 0;
            }

            .auth-register__brand {
                font-size: 1.6rem;
            }

            .auth-register__title,
            .auth-register__card-top h2 {
                font-size: 1.8rem;
            }

            .auth-register__panel,
            .auth-register__card {
                padding: 22px;
                border-radius: 26px;
            }
        }
    </style>
@endpush

@section('main-content')
    <main class="auth-register">
        <div class="auth-register__shape auth-register__shape--one"></div>
        <div class="auth-register__shape auth-register__shape--two"></div>

        <div class="auth-register__container">
            <section class="auth-register__panel">
                <div class="auth-register__brand">
                    <span class="auth-register__brand-icon">
                        <i class="fas fa-briefcase"></i>
                    </span>
                    <span>KH-<span>WORKS</span></span>
                </div>

                <span class="auth-register__eyebrow">
                    <i class="fas fa-user-plus"></i>
                    New Account
                </span>

                <h1 class="auth-register__title">Create your account and start applying</h1>
                <p class="auth-register__text">Set up your KH-WORKS profile to save jobs, track applications, and discover opportunities tailored to your career goals.</p>

                <ul class="auth-register__list">
                    <li>
                        <i class="fas fa-id-badge"></i>
                        <div>
                            <strong>Build your profile</strong>
                            <span>Create a strong candidate presence in minutes.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-bookmark"></i>
                        <div>
                            <strong>Save jobs you love</strong>
                            <span>Keep your favorite roles in one organized place.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-paper-plane"></i>
                        <div>
                            <strong>Apply with confidence</strong>
                            <span>Track your next steps from a polished dashboard.</span>
                        </div>
                    </li>
                </ul>
            </section>

            <section class="auth-register__card">
                <div class="auth-register__card-top">
                    <h2>Create Account</h2>
                    <p>Enter your details below to create your KH-WORKS account.</p>
                </div>

                @if ($errors->any())
                    <div class="auth-register__alert" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="auth-register__form">
                    @csrf

                    <div class="auth-register__grid">
                        <div class="auth-register__field">
                            <label for="name">
                                <i class="fas fa-user"></i>
                                <span>First Name</span>
                            </label>
                            <input id="name" type="text" class="auth-register__input" name="name" value="{{ old('name') }}" placeholder="Enter your first name" required autofocus>
                        </div>

                        <div class="auth-register__field">
                            <label for="last_name">
                                <i class="fas fa-user-tag"></i>
                                <span>Last Name</span>
                            </label>
                            <input id="last_name" type="text" class="auth-register__input" name="last_name" value="{{ old('last_name') }}" placeholder="Enter your last name" required>
                        </div>
                    </div>

                    <div class="auth-register__field">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </label>
                        <input id="email" type="email" class="auth-register__input" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required>
                    </div>

                    <div class="auth-register__grid">
                        <div class="auth-register__field">
                            <label for="password">
                                <i class="fas fa-lock"></i>
                                <span>Password</span>
                            </label>
                            <input id="password" type="password" class="auth-register__input" name="password" placeholder="Enter your password" required>
                        </div>

                        <div class="auth-register__field">
                            <label for="password_confirmation">
                                <i class="fas fa-check-double"></i>
                                <span>Confirm Password</span>
                            </label>
                            <input id="password_confirmation" type="password" class="auth-register__input" name="password_confirmation" placeholder="Confirm your password" required>
                        </div>
                    </div>

                    <button type="submit" class="auth-register__button">
                        <i class="fas fa-user-plus"></i>
                        <span>Create Account</span>
                    </button>
                </form>

                <div class="auth-register__footer">
                    Already have an account?
                    <a href="{{ route('login') }}">Sign in</a>
                </div>
            </section>
        </div>
    </main>
@endsection
