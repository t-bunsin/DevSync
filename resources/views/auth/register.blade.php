@extends('layouts.auth')

@section('title', 'Create Account | KH-WORKS')
@section('body-class', 'jf-auth-body')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
@endpush

@section('main-content')
    @php
        $accountType = old('account_type', 'employee');

        $panels = [
            'employee' => [
                'eyebrow' => 'Job seeker account',
                'title' => 'Create your account and start applying.',
                'text' => 'Save roles, track every application, and get matched with teams hiring across Cambodia.',
                'points' => [
                    ['icon' => 'fa-id-badge', 'title' => 'Build your profile', 'body' => 'A strong candidate presence in minutes.'],
                    ['icon' => 'fa-bookmark', 'title' => 'Save jobs you love', 'body' => 'Keep your favourite roles in one place.'],
                    ['icon' => 'fa-paper-plane', 'title' => 'Apply with confidence', 'body' => 'Track next steps from your dashboard.'],
                ],
            ],
            'employer' => [
                'eyebrow' => 'Employer account',
                'title' => 'Post roles and meet Cambodia’s talent.',
                'text' => 'Publish openings, manage applicants, and keep your hiring pipeline moving from one workspace.',
                'points' => [
                    ['icon' => 'fa-bullhorn', 'title' => 'Publish openings', 'body' => 'Get roles in front of active candidates.'],
                    ['icon' => 'fa-users', 'title' => 'Manage applicants', 'body' => 'Review, shortlist, and respond in one place.'],
                    ['icon' => 'fa-chart-line', 'title' => 'Track your pipeline', 'body' => 'See what is working across every role.'],
                ],
            ],
        ];
    @endphp

    <main class="jf-auth">
        <div class="jf-auth__glow jf-auth__glow--one" aria-hidden="true"></div>
        <div class="jf-auth__glow jf-auth__glow--two" aria-hidden="true"></div>

        <div class="jf-auth__container">
            <section class="jf-auth__panel" data-auth-panel>
                <a class="jf-auth__brand" href="{{ url('/') }}">
                    <span class="jf-auth__brand-mark"><i class="fas fa-briefcase"></i></span>
                    <span>KH-<span>WORKS</span></span>
                </a>

                @foreach ($panels as $type => $panel)
                    <div class="jf-auth__panel-copy{{ $accountType === $type ? ' is-active' : '' }}" data-panel-for="{{ $type }}">
                        <span class="jf-auth__eyebrow">
                            <i class="fas {{ $type === 'employer' ? 'fa-building' : 'fa-user-plus' }}" aria-hidden="true"></i>
                            {{ $panel['eyebrow'] }}
                        </span>

                        <h1>{{ $panel['title'] }}</h1>
                        <p>{{ $panel['text'] }}</p>

                        <ul class="jf-auth__points">
                            @foreach ($panel['points'] as $point)
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
                @endforeach

                <p class="jf-auth__panel-foot">
                    <i class="fas fa-shield-halved" aria-hidden="true"></i>
                    Free to join. Your details are never shared without your consent.
                </p>
            </section>

            <section class="jf-auth__card">
                <header class="jf-auth__card-head">
                    <h2>Create account</h2>
                    <p>Choose how you want to use KH-WORKS, then fill in your details.</p>
                </header>

                @if ($errors->any())
                    <div class="jf-auth__alert" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong>We could not create your account.</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="jf-auth__form" data-auth-form novalidate>
                    @csrf

                    <fieldset class="jf-auth__roles">
                        <legend>I want to</legend>

                        <div class="jf-auth__roles-grid">
                        <label class="jf-role">
                            <input type="radio" name="account_type" value="employee" @checked($accountType === 'employee')>
                            <span class="jf-role__box">
                                <span class="jf-role__icon"><i class="fas fa-user-tie" aria-hidden="true"></i></span>
                                <span class="jf-role__text">
                                    <strong>Find a job</strong>
                                    <small>Apply and track applications</small>
                                </span>
                                <i class="fas fa-circle-check jf-role__tick" aria-hidden="true"></i>
                            </span>
                        </label>

                        <label class="jf-role">
                            <input type="radio" name="account_type" value="employer" @checked($accountType === 'employer')>
                            <span class="jf-role__box">
                                <span class="jf-role__icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                                <span class="jf-role__text">
                                    <strong>Hire talent</strong>
                                    <small>Post roles and review candidates</small>
                                </span>
                                <i class="fas fa-circle-check jf-role__tick" aria-hidden="true"></i>
                            </span>
                        </label>
                        </div>
                    </fieldset>

                    <div class="jf-auth__grid">
                        <div class="jf-auth__field">
                            <label for="first_name">First name</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}"
                                placeholder="Sokha" autocomplete="given-name" required autofocus
                                @error('first_name') aria-invalid="true" @enderror>
                            @error('first_name')<small class="jf-auth__error">{{ $message }}</small>@enderror
                        </div>

                        <div class="jf-auth__field">
                            <label for="last_name">Last name</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}"
                                placeholder="Chan" autocomplete="family-name" required
                                @error('last_name') aria-invalid="true" @enderror>
                            @error('last_name')<small class="jf-auth__error">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <div class="jf-auth__field jf-auth__field--company" data-company-field>
                        <label for="company_name">Company name</label>
                        <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}"
                            placeholder="e.g. ABA Bank" autocomplete="organization"
                            @error('company_name') aria-invalid="true" @enderror>
                        @error('company_name')
                            <small class="jf-auth__error">{{ $message }}</small>
                        @else
                            <small class="jf-auth__hint">Shown on the roles you post.</small>
                        @enderror
                    </div>

                    <div class="jf-auth__field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                            placeholder="you@example.com" autocomplete="email" required
                            @error('email') aria-invalid="true" @enderror>
                        @error('email')<small class="jf-auth__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-auth__grid">
                        <div class="jf-auth__field">
                            <label for="password">Password</label>
                            <span class="jf-auth__control">
                                <input id="password" name="password" type="password" placeholder="At least 8 characters"
                                    autocomplete="new-password" required @error('password') aria-invalid="true" @enderror>
                                <button type="button" data-toggle-password="password" aria-label="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </span>
                            @error('password')
                                <small class="jf-auth__error">{{ $message }}</small>
                            @else
                                <small class="jf-auth__hint">Minimum 8 characters.</small>
                            @enderror
                        </div>

                        <div class="jf-auth__field">
                            <label for="password_confirmation">Confirm password</label>
                            <span class="jf-auth__control">
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    placeholder="Repeat your password" autocomplete="new-password" required>
                                <button type="button" data-toggle-password="password_confirmation" aria-label="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <button class="jf-auth__submit" type="submit">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                        <span data-submit-label>Create account</span>
                    </button>
                </form>

                <footer class="jf-auth__card-foot">
                    <span>Already have an account? <a href="{{ route('login') }}">Sign in</a></span>
                    <a class="jf-auth__back" href="{{ url('/') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to jobs</a>
                </footer>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}?v={{ filemtime(public_path('js/auth.js')) }}"></script>
@endpush
