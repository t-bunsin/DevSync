@extends('layouts.admin')

@section('title', __('ui.bo.security.title') . ' | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        // Social-only accounts have no password_hash, so this page offers to set
        // a first one instead of asking for a current password that never existed.
        $hasPassword = $user->password_hash !== null;
        $lockedUntil = $user->locked_until;
        $isLocked = $lockedUntil && $lockedUntil->isFuture();
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.security.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.security.title') }}</h1>
                <p>{{ __('ui.bo.security.subtitle') }}</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('profile') }}">{{ __('ui.bo.security.edit_profile') }}</a>
        </header>

        @if (session('success'))
            <div class="kh-bo__flash" role="status">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="kh-bo__detail">
            <div class="kh-bo__detail-main">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ $hasPassword ? __('ui.bo.security.password_title') : __('ui.bo.security.password_title_set') }}</h2>
                            <p>{{ $hasPassword ? __('ui.bo.security.password_hint') : __('ui.bo.security.password_hint_set') }}</p>
                        </div>
                    </div>

                    <div class="kh-bo__card-body">
                        @if ($errors->any())
                            <div class="kh-bo__errors" role="alert" tabindex="-1">
                                <strong>{{ __('ui.bo.check_fields') }}</strong>
                                <ul>
                                    @foreach ($errors->all() as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('security.password') }}">
                            @csrf
                            @method('PUT')

                            <div class="kh-bo__grid">
                                @if ($hasPassword)
                                    <div class="kh-bo__field kh-bo__field--wide">
                                        <label class="kh-bo__label" for="current_password">{{ __('ui.bo.security.current_password') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
                                        <div class="kh-bo__password">
                                            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('current_password')])
                                                id="current_password" name="current_password" type="password" required
                                                autocomplete="current-password"
                                                placeholder="{{ __('ui.bo.security.current_password_placeholder') }}">
                                            <button class="kh-bo__password-toggle" type="button" data-password-toggle="current_password"
                                                aria-label="{{ __('ui.bo.security.show_password') }}" aria-pressed="false">
                                                <i class="far fa-eye" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        @error('current_password') <span class="kh-bo__error">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                <div class="kh-bo__field">
                                    <label class="kh-bo__label" for="password">{{ __('ui.bo.security.new_password') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
                                    <div class="kh-bo__password">
                                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('password')])
                                            id="password" name="password" type="password" minlength="8" required
                                            autocomplete="new-password"
                                            placeholder="{{ __('ui.bo.security.new_password_placeholder') }}">
                                        <button class="kh-bo__password-toggle" type="button" data-password-toggle="password"
                                            aria-label="{{ __('ui.bo.security.show_password') }}" aria-pressed="false">
                                            <i class="far fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <span class="kh-bo__hint">{{ __('ui.bo.security.strength_hint') }}</span>
                                    @error('password') <span class="kh-bo__error">{{ $message }}</span> @enderror
                                </div>

                                <div class="kh-bo__field">
                                    <label class="kh-bo__label" for="password_confirmation">{{ __('ui.bo.security.confirm_password') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
                                    <div class="kh-bo__password">
                                        <input class="kh-bo__control" id="password_confirmation" name="password_confirmation"
                                            type="password" minlength="8" required autocomplete="new-password"
                                            placeholder="{{ __('ui.bo.security.confirm_password_placeholder') }}">
                                        <button class="kh-bo__password-toggle" type="button" data-password-toggle="password_confirmation"
                                            aria-label="{{ __('ui.bo.security.show_password_confirm') }}" aria-pressed="false">
                                            <i class="far fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="kh-bo__form-actions">
                                <button class="kh-bo__btn" type="submit">{{ __('ui.bo.security.submit') }}</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <aside class="kh-bo__detail-side">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.security.overview_title') }}</h2>
                            <p>{{ __('ui.bo.security.overview_hint') }}</p>
                        </div>
                    </div>

                    <div class="kh-bo__card-body">
                        <dl class="kh-bo__facts kh-bo__facts--rows">
                            <div>
                                <dt>{{ __('ui.bo.security.last_login') }}</dt>
                                <dd>{{ $user->last_login_at?->translatedFormat('M d, Y · H:i') ?? __('ui.bo.security.never') }}</dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.security.account_status') }}</dt>
                                <dd>
                                    <span @class([
                                        'kh-bo__status',
                                        'kh-bo__status--verified' => $user->status === \App\Models\User::STATUS_ACTIVE,
                                        'kh-bo__status--pending' => $user->status === \App\Models\User::STATUS_PENDING,
                                        'kh-bo__status--rejected' => in_array($user->status, [
                                            \App\Models\User::STATUS_SUSPENDED,
                                            \App\Models\User::STATUS_BANNED,
                                        ], true),
                                    ])>{{ __('ui.bo.users.form.status_' . $user->status) }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.security.primary_role') }}</dt>
                                <dd>{{ $user->primaryRole()?->name_en ?? __('ui.bo.users.no_roles') }}</dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.security.email_label') }}</dt>
                                <dd>
                                    {{ $user->email ?? __('ui.bo.security.not_set') }}
                                    <span class="kh-bo__hint">{{ $user->email_verified_at ? __('ui.bo.security.verified') : __('ui.bo.security.unverified') }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.security.phone_label') }}</dt>
                                <dd>
                                    {{ $user->phone ?? __('ui.bo.security.not_set') }}
                                    @if ($user->phone)
                                        <span class="kh-bo__hint">{{ $user->phone_verified_at ? __('ui.bo.security.verified') : __('ui.bo.security.unverified') }}</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.security.failed_attempts') }}</dt>
                                <dd>{{ trans_choice('ui.bo.security.attempts_count', $user->failed_attempts ?? 0, ['count' => $user->failed_attempts ?? 0]) }}</dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.security.locked_until') }}</dt>
                                <dd>{{ $isLocked ? $lockedUntil->translatedFormat('M d, Y · H:i') : __('ui.bo.security.not_locked') }}</dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.security.member_since') }}</dt>
                                <dd>{{ $user->created_at?->translatedFormat('M d, Y') ?? __('ui.bo.unknown') }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/users-create.js') }}?v={{ filemtime(public_path('js/users-create.js')) }}"></script>
@endpush
