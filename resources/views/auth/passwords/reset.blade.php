@extends('layouts.auth')

@section('title', 'Choose a new password | ZIN-WORKS')
@section('body-class', 'jf-auth-body')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
@endpush

@section('main-content')
    @php
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        // The address is carried by the link, so it is known here. Showing it
        // read-only beats an empty box the user has to re-type correctly for
        // the token to match.
        $address = old('email', $email ?? request('email'));

        $points = [
            ['icon' => 'fa-wand-magic-sparkles', 'title' => 'Make it a strong one', 'body' => 'Long and unique beats short and clever.'],
            ['icon' => 'fa-clock', 'title' => 'Link lasts ' . $expire . ' minutes', 'body' => 'Expired? Request a fresh one — it takes a second.'],
            ['icon' => 'fa-right-to-bracket', 'title' => 'Straight back in', 'body' => 'We sign you in as soon as it is saved.'],
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
                        <i class="fas fa-lock-open" aria-hidden="true"></i>
                        Almost there
                    </span>

                    <h1>Set a new password and pick up where you left off.</h1>
                    <p>One password, then you're back to your applications, saved roles and dashboard — all exactly as you left them.</p>

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
                    This link works once, then it's spent.
                </p>
            </section>

            <section class="jf-auth__card">
                <header class="jf-auth__card-head">
                    <h2>Choose a new password</h2>
                    <p>
                        @if ($address)
                            Resetting the password for <strong>{{ $address }}</strong>.
                        @else
                            Pick something you haven't used on this account before.
                        @endif
                    </p>
                </header>

                @if ($errors->any())
                    <div class="jf-auth__alert" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong>We could not reset your password.</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="jf-auth__form" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="jf-auth__field">
                        <label for="email">Email</label>
                        @if ($address)
                            {{-- Read-only, not disabled: a disabled input is not submitted,
                                 and the token is only valid against this address. --}}
                            <input id="email" name="email" type="email" value="{{ $address }}"
                                autocomplete="username" readonly required
                                @error('email') aria-invalid="true" @enderror>
                        @else
                            <input id="email" name="email" type="email" value="" placeholder="you@example.com"
                                autocomplete="username" required autofocus
                                @error('email') aria-invalid="true" @enderror>
                        @endif
                        @error('email')<small class="jf-auth__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-auth__field">
                        <label for="password">New password</label>
                        <span class="jf-auth__control">
                            <input id="password" name="password" type="password" placeholder="At least 8 characters"
                                autocomplete="new-password" required data-password
                                aria-describedby="password-rules" @error('password') aria-invalid="true" @enderror
                                @if ($address) autofocus @endif>
                            <button type="button" data-toggle-password="password" aria-label="Show password">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </span>
                        @error('password')<small class="jf-auth__error">{{ $message }}</small>@enderror
                    </div>

                    {{-- Same checklist as sign-up, driven by the same auth.js. The reset
                         controller enforces RegisterController::passwordRules(), so without
                         it people guess at the policy and get rejected blind. --}}
                    <ul class="jf-auth__rules" id="password-rules" data-password-rules aria-live="polite">
                        <li data-rule="length"><i class="fas fa-circle" aria-hidden="true"></i>At least 8 characters</li>
                        <li data-rule="mixed"><i class="fas fa-circle" aria-hidden="true"></i>Upper and lower case letters</li>
                        <li data-rule="number"><i class="fas fa-circle" aria-hidden="true"></i>At least one number</li>
                        <li data-rule="symbol"><i class="fas fa-circle" aria-hidden="true"></i>At least one symbol</li>
                    </ul>

                    <div class="jf-auth__field">
                        <label for="password_confirmation">Confirm new password</label>
                        <span class="jf-auth__control">
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                placeholder="Type it once more" autocomplete="new-password" required
                                data-password-confirm aria-describedby="password-match">
                            <button type="button" data-toggle-password="password_confirmation" aria-label="Show password">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </span>
                        <small class="jf-auth__match" id="password-match" data-password-match hidden></small>
                    </div>

                    <button class="jf-auth__submit" type="submit">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <span>Save and sign in</span>
                    </button>
                </form>

                <footer class="jf-auth__card-foot">
                    <span>Link expired? <a href="{{ route('password.request') }}">Send a new one</a></span>
                    <a class="jf-auth__back" href="{{ route('login') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to sign in</a>
                </footer>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}?v={{ filemtime(public_path('js/auth.js')) }}"></script>
@endpush
