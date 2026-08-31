@extends('layouts.auth')

@section('title', 'Confirm your email | ZIN-WORKS')
@section('body-class', 'jf-auth-body')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
@endpush

@section('main-content')
    @php
        $points = [
            ['icon' => 'fa-envelope-open-text', 'title' => 'Check your inbox', 'body' => 'The code lands within a minute of signing up.'],
            ['icon' => 'fa-clock', 'title' => 'Valid for ' . \App\Models\EmailVerificationCode::TTL_MINUTES . ' minutes', 'body' => 'Ask for a fresh one if it runs out.'],
            ['icon' => 'fa-shield-halved', 'title' => 'Keeps your account yours', 'body' => 'Only someone reading your email can finish signing up.'],
        ];
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
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        One last step
                    </span>

                    <h1>Confirm the email you signed up with.</h1>
                    <p>We sent a 6-digit code to <strong>{{ $user->email }}</strong>. Enter it here and your account is ready.</p>

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
                    Nothing is sent to employers until your email is confirmed.
                </p>
            </section>

            <section class="jf-auth__card">
                <header class="jf-auth__card-head">
                    <h2>Enter your code</h2>
                    <p>Sent to {{ $user->email }} · expires in {{ \App\Models\EmailVerificationCode::TTL_MINUTES }} minutes.</p>
                </header>

                @if (session('success'))
                    <div class="jf-auth__alert jf-auth__alert--success" role="status">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <div><span>{{ session('success') }}</span></div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="jf-auth__alert" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong>We could not confirm that code.</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.code.verify') }}" class="jf-auth__form" novalidate>
                    @csrf

                    <div class="jf-auth__field">
                        <label for="code">6-digit code</label>
                        {{-- One field rather than six boxes: it pastes in one go, works
                             with the browser's own one-time-code autofill, and needs no
                             JavaScript to submit. --}}
                        <input id="code" name="code" class="jf-auth__code" type="text"
                            inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
                            placeholder="000000" required autofocus
                            @error('code') aria-invalid="true" @enderror>
                        @error('code')<small class="jf-auth__error">{{ $message }}</small>@enderror
                    </div>

                    <button class="jf-auth__submit" type="submit">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <span>Confirm and continue</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('verification.code.resend') }}" class="jf-auth__resend">
                    @csrf
                    <span>Didn't get it? Check spam, or</span>
                    <button type="submit" @disabled($waitSeconds > 0)>
                        @if ($waitSeconds > 0)
                            send again in {{ $waitSeconds }}s
                        @else
                            send a new code
                        @endif
                    </button>
                </form>

                <footer class="jf-auth__card-foot">
                    <span>Wrong address? <a href="{{ route('register') }}">Start over</a></span>
                    <a class="jf-auth__back" href="{{ route('login') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to sign in</a>
                </footer>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        /* Counts the resend button back down without a reload, and strips
           anything non-numeric so a pasted "123 456" still submits. */
        (function () {
            const code = document.getElementById('code');
            if (code) {
                code.addEventListener('input', () => {
                    code.value = code.value.replace(/\D/g, '').slice(0, 6);
                });
            }

            const button = document.querySelector('.jf-auth__resend button');
            let left = {{ $waitSeconds }};
            if (!button || left <= 0) return;

            const tick = setInterval(() => {
                left -= 1;
                if (left > 0) {
                    button.textContent = `send again in ${left}s`;
                    return;
                }
                clearInterval(tick);
                button.disabled = false;
                button.textContent = 'send a new code';
            }, 1000);
        })();
    </script>
@endpush
