@extends('layouts.admin')

@section('title', 'My profile | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $user = Auth::user();
        $primaryRole = $user->primaryRole();
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Profile</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Account</span>
                <h1>My profile</h1>
                <p>Your photo, contact details, and password.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('home') }}">Back to dashboard</a>
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
                <strong>Please check the highlighted fields.</strong>
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
                        Photo
                        <span>Shown on your account menu and in the user directory.</span>
                    </div>

                    <div class="kh-bo__field kh-bo__field--wide">
                        <span class="kh-bo__label">Profile photo</span>
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
                                    JPG, PNG or WebP, at least 100 x 100 pixels and up to 2 MB.
                                    Without one your initials are used.
                                </span>

                                @if ($user->avatar_url)
                                    <label class="kh-bo__checkbox">
                                        <input type="checkbox" name="remove_photo" value="1">
                                        Remove the current photo
                                    </label>
                                @endif

                                @error('photo')
                                    <span class="kh-bo__error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="kh-bo__section-label">
                        Details
                        <span>
                            Signed in as {{ $primaryRole?->name_en ?? 'a member' }}.
                            Roles are managed by an administrator.
                        </span>
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="first_name">
                            First name <span class="kh-bo__required" aria-hidden="true">*</span>
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
                            Last name <span class="kh-bo__required" aria-hidden="true">*</span>
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
                            Email <span class="kh-bo__required" aria-hidden="true">*</span>
                        </label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('email')])
                            id="email" name="email" type="email" maxlength="255" required
                            autocomplete="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="phone">Phone</label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('phone')])
                            id="phone" name="phone" type="tel" maxlength="30"
                            autocomplete="tel" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__section-label">
                        Password
                        <span>Leave these blank to keep your current password.</span>
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="current_password">Current password</label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('current_password')])
                            id="current_password" name="current_password" type="password" autocomplete="current-password">
                        @error('current_password')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="new_password">New password</label>
                        <input @class(['kh-bo__control', 'is-invalid' => $errors->has('new_password')])
                            id="new_password" name="new_password" type="password" autocomplete="new-password">
                        <span class="kh-bo__hint">At least 8 characters.</span>
                        @error('new_password')
                            <span class="kh-bo__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="kh-bo__field">
                        <label class="kh-bo__label" for="new_password_confirmation">Confirm new password</label>
                        <input class="kh-bo__control" id="new_password_confirmation"
                            name="new_password_confirmation" type="password" autocomplete="new-password">
                    </div>
                </div>

                <div class="kh-bo__form-actions">
                    <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('home') }}">Cancel</a>
                    <button class="kh-bo__btn" type="submit">Save changes</button>
                </div>
            </div>
        </form>
    </div>
@endsection
