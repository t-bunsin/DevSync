@extends('layouts.auth')

@section('title', 'Password Recovery')
@section('body-class', 'auth-body auth-body--password')

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

        .auth-flow {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .auth-flow__button {
            border-radius: 0 !important;
        }

        .auth-flow__shape {
            position: absolute;
            border-radius: 999px;
            filter: blur(10px);
            pointer-events: none;
            opacity: 0.55;
        }

        .auth-flow__shape--one {
            top: 80px;
            left: -70px;
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, rgba(11, 70, 111, 0.18), rgba(11, 70, 111, 0.06));
        }

        .auth-flow__shape--two {
            right: -50px;
            bottom: 100px;
            width: 220px;
            height: 220px;
            background: linear-gradient(135deg, rgba(11, 70, 111, 0.14), rgba(11, 70, 111, 0.05));
        }

        .auth-flow__container {
            position: relative;
            z-index: 1;
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(310px, 0.98fr) minmax(360px, 0.82fr);
            gap: 28px;
            align-items: center;
            padding: 36px 0;
        }

        .auth-flow__panel {
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

        .auth-flow__panel::before,
        .auth-flow__panel::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .auth-flow__panel::before {
            top: -80px;
            right: -10px;
            width: 220px;
            height: 220px;
        }

        .auth-flow__panel::after {
            left: -50px;
            bottom: -100px;
            width: 260px;
            height: 260px;
        }

        .auth-flow__brand {
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

        .auth-flow__brand span span {
            color: #d4ecff;
        }

        .auth-flow__brand-icon {
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

        .auth-flow__eyebrow {
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

        .auth-flow__title {
            position: relative;
            z-index: 1;
            max-width: 11ch;
            margin: 0 0 14px;
            font-size: clamp(2.4rem, 4vw, 3.8rem);
            line-height: 0.98;
            letter-spacing: -0.05em;
        }

        .auth-flow__text {
            position: relative;
            z-index: 1;
            max-width: 46ch;
            margin: 0 0 28px;
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.7;
        }

        .auth-flow__list {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .auth-flow__list li {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            backdrop-filter: blur(8px);
        }

        .auth-flow__list i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.16);
            font-size: 0.95rem;
        }

        .auth-flow__list strong {
            display: block;
            margin-bottom: 2px;
            font-size: 0.96rem;
        }

        .auth-flow__list span {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.86rem;
        }

        .auth-flow__card {
            padding: 36px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(223, 228, 248, 0.94);
            border-radius: 34px;
            box-shadow: 0 24px 54px rgba(82, 94, 172, 0.12);
            backdrop-filter: blur(16px);
        }

        .auth-flow__card-top {
            margin-bottom: 24px;
        }

        .auth-flow__card-top h2 {
            margin: 0 0 8px;
            font-size: 2rem;
            letter-spacing: -0.04em;
        }

        .auth-flow__card-top p {
            margin: 0;
            color: #5f6b88;
            line-height: 1.7;
        }

        .auth-flow__alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 18px;
        }

        .auth-flow__alert--success {
            color: #185d39;
            background: #edf9f1;
            border: 1px solid #cdeed8;
        }

        .auth-flow__alert--error {
            color: #9f2c3f;
            background: #fff3f5;
            border: 1px solid #ffd8de;
        }

        .auth-flow__alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .auth-flow__form {
            display: grid;
            gap: 18px;
        }

        .auth-flow__field {
            display: grid;
            gap: 8px;
        }

        .auth-flow__field label {
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

        .auth-flow__field label i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            color: #0b466f;
            font-size: 0.82rem;
        }

        .auth-flow__input {
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

        .auth-flow__input:focus {
            border-color: #0b466f;
            box-shadow: 0 0 0 4px rgba(11, 70, 111, 0.08);
        }

        .auth-flow__input::placeholder {
            color: #98a1bb;
        }

        .auth-flow__actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .auth-flow__link {
            color: #0b466f;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-flow__link:hover {
            text-decoration: underline;
        }

        .auth-flow__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 58px;
            padding: 0 24px;
            color: #fff;
            background: #0b466f;
            border: 0;
            border-radius: 20px;
            box-shadow: 0 20px 28px rgba(11, 70, 111, 0.22);
            font-weight: 700;
            cursor: pointer;
        }

        .auth-flow__footer {
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid #ebeffc;
            text-align: center;
            color: #6f7896;
        }

        .auth-flow__footer a {
            color: #0b466f;
            font-weight: 700;
            text-decoration: none;
        }

        .auth-flow__footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 992px) {
            .auth-flow__container {
                grid-template-columns: 1fr;
            }

            .auth-flow__panel,
            .auth-flow__card {
                padding: 28px;
            }

            .auth-flow__title {
                max-width: none;
                font-size: 2.5rem;
            }
        }

        @media (max-width: 576px) {
            .auth-flow__container {
                width: min(100% - 20px, 1180px);
                padding: 18px 0;
            }

            .auth-flow__brand {
                font-size: 1.6rem;
            }

            .auth-flow__title,
            .auth-flow__card-top h2 {
                font-size: 1.8rem;
            }

            .auth-flow__panel,
            .auth-flow__card {
                padding: 22px;
                border-radius: 26px;
            }

            .auth-flow__actions {
                align-items: stretch;
            }

            .auth-flow__button {
                width: 100%;
            }
        }
    </style>
@endpush

@section('main-content')
    <main class="auth-flow">
        <div class="auth-flow__shape auth-flow__shape--one"></div>
        <div class="auth-flow__shape auth-flow__shape--two"></div>

        <div class="auth-flow__container">
            <section class="auth-flow__panel">
                <div class="auth-flow__brand">
                    <span class="auth-flow__brand-icon">
                        <i class="fas fa-briefcase"></i>
                    </span>
                    <span>KH-<span>WORKS</span></span>
                </div>

                <span class="auth-flow__eyebrow">
                    <i class="fas fa-key"></i>
                    Password Recovery
                </span>

                <h1 class="auth-flow__title">Get a secure reset link in minutes</h1>
                <p class="auth-flow__text">Enter the email tied to your account and we’ll send a password reset link so you can get back into your dashboard quickly and safely.</p>

                <ul class="auth-flow__list">
                    <li>
                        <i class="fas fa-envelope-open-text"></i>
                        <div>
                            <strong>Reset by email</strong>
                            <span>We will send a one-time link to your inbox.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-shield-halved"></i>
                        <div>
                            <strong>Secure recovery flow</strong>
                            <span>Your reset request is protected and verified.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-rotate-left"></i>
                        <div>
                            <strong>Return to work fast</strong>
                            <span>Restore access and continue your job search.</span>
                        </div>
                    </li>
                </ul>
            </section>

            <section class="auth-flow__card">
                <div class="auth-flow__card-top">
                    <h2>Password Recovery</h2>
                    <p>Send yourself a reset link and choose a new password from your email.</p>
                </div>

                @if ($errors->any())
                    <div class="auth-flow__alert auth-flow__alert--error" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="auth-flow__alert auth-flow__alert--success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="auth-flow__form">
                    @csrf

                    <div class="auth-flow__field">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </label>
                        <input id="email" type="email" class="auth-flow__input" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autofocus>
                    </div>

                    <div class="auth-flow__actions">
                        <a class="auth-flow__link" href="{{ route('login') }}">Return to login</a>
                        <button type="submit" class="auth-flow__button">
                            <i class="fas fa-paper-plane"></i>
                            <span>Send Reset Link</span>
                        </button>
                    </div>
                </form>

                <div class="auth-flow__footer">
                    Need an account?
                    <a href="{{ route('register') }}">Create one</a>
                </div>
            </section>
        </div>
    </main>
@endsection
