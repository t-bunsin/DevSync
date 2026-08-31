@extends('layouts.admin')

@section('title', 'My profile | ZIN-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $user = Auth::user();
        $primaryRole = $user->primaryRole();
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.profile.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">{{ __('ui.bo.profile.kicker') }}</span>
                <h1>{{ __('ui.bo.profile.heading') }}</h1>
                <p>{{ __('ui.bo.profile.subtitle') }}</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('home') }}">{{ __('ui.bo.profile.back_to_dashboard') }}</a>
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

        @if ($errors->any())
            <div class="kh-bo__errors" role="alert">
                <strong>{{ __('ui.bo.check_fields') }}</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="kh-bo__form-card">
                <div class="kh-bo__grid">
                    <div class="kh-bo__section-label">
                        {{ __('ui.bo.profile.photo') }}
                        <span>{{ __('ui.bo.profile.photo_hint') }}</span>
                    </div>

                    <div class="kh-bo__field kh-bo__field--wide">
                        <span class="kh-bo__label">{{ __('ui.bo.profile.profile_photo') }}</span>
                        <div class="kh-bo__logo-field">
                            <span class="kh-bo__logo-preview" aria-hidden="true">
                                @if ($user->avatarUrl())
                                    <img src="{{ $user->avatarUrl() }}" alt="">
                                @else
                                    {{ $user->initials() }}
                                @endif
                            </span>
                            <div class="kh-bo__field" style="flex: 1;">
                                <input @class(['kh-bo__control', 'is-invalid' => $errors->has('photo')])
                                    id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp">
                                <span class="kh-bo__hint">
                                    {{ __('ui.bo.profile.photo_rules') }}
                                    Without one your initials are used.
                                </span>

                                @if ($user->avatar_url)
                                    <label class="kh-bo__checkbox">
                                        <input type="checkbox" name="remove_photo" value="1">
                                        {{ __('ui.bo.profile.photo_remove') }}
                                    </label>
                                @endif

                                @error('photo')
                                    <span class="kh-bo__error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="kh-bo__section-label">
                        {{ __('ui.bo.profile.details') }}
                        <span>
                            Signed in as {{ $primaryRole?->name_en ?? 'a member' }}.
                            Roles are managed by an administrator.
                        </span>
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="first_name">
                            {{ __('ui.bo.profile.first_name') }} <span class="kh-bo__required" aria-hidden="true">*</span>
                        </label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('first_name')])
                            id="first_name" name="first_name" type="text" maxlength="80" required
                            value="{{ old('first_name', $user->first_name) }}">
                        @error('first_name')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="last_name">
                            {{ __('ui.bo.profile.last_name') }} <span class="kh-bo__required" aria-hidden="true">*</span>
                        </label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('last_name')])
                            id="last_name" name="last_name" type="text" maxlength="80" required
                            value="{{ old('last_name', $user->last_name) }}">
                        @error('last_name')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="email">
                            {{ __('ui.bo.profile.email') }} <span class="kh-bo__required" aria-hidden="true">*</span>
                        </label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('email')])
                            id="email" name="email" type="email" maxlength="255" required
                            autocomplete="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="phone">{{ __('ui.bo.profile.phone') }}</label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('phone')])
                            id="phone" name="phone" type="tel" maxlength="30"
                            autocomplete="tel" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__section-label">
                        {{ __('ui.bo.profile.password') }}
                        <span>{{ __('ui.bo.profile.password_hint') }}</span>
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="current_password">{{ __('ui.bo.profile.current_password') }}</label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('current_password')])
                            id="current_password" name="current_password" type="password" autocomplete="current-password">
                        @error('current_password')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="new_password">{{ __('ui.bo.profile.new_password') }}</label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('new_password')])
                            id="new_password" name="new_password" type="password" autocomplete="new-password">
                        <span class="kh-bo__hint">{{ __('ui.bo.profile.new_password_hint') }}</span>
                        @error('new_password')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="new_password_confirmation">{{ __('ui.bo.profile.confirm_new_password') }}</label>
                        <input class="kh-bo__control" id="new_password_confirmation"
                            name="new_password_confirmation" type="password" autocomplete="new-password">
                    </div>
                </div>

                <div class="kh-bo__form-actions">
                    <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('home') }}">{{ __('ui.bo.job_posts.form.cancel') }}</a>
                    <button class="kh-bo__btn" type="submit">{{ __('ui.bo.save') }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection
