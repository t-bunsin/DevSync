@extends('layouts.admin')

@section('title', 'Add New User | KH-WORKS Admin')
@section('body-class', 'kh-user-create-page')

@push('styles')
    <link href="{{ asset('css/users-create.css') }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $errorTargets = [
            'first_name' => 'inputFirstName',
            'last_name' => 'inputLastName',
            'email' => 'inputEmailAddress',
            'phone_number' => 'inputPhone',
            'role' => 'inputRole',
            'password' => 'inputPassword',
        ];
    @endphp

    <div class="kh-user-create">
        <div class="kh-user-create__shell">
            <header class="kh-user-create__page-head">
                <div class="kh-user-create__heading">
                    <a class="kh-user-create__back" href="{{ route('users') }}" aria-label="Back to users list">
                        <i data-feather="arrow-left" aria-hidden="true"></i>
                    </a>

                    <span class="kh-user-create__heading-icon" aria-hidden="true">
                        <i data-feather="user-plus"></i>
                    </span>

                    <div>
                        <span class="kh-user-create__eyebrow">User management</span>
                        <h1>Add a new user</h1>
                        <p>Create an account, choose the right access level, and keep your team directory organized.</p>
                    </div>
                </div>

                <div class="kh-user-create__theme-note">
                    <span class="kh-user-create__theme-note-icon" aria-hidden="true">
                        <i data-feather="sun"></i>
                    </span>
                    <span>
                        <strong>Light mode by default</strong>
                        <small>Use the switch in the top menu anytime.</small>
                    </span>
                </div>
            </header>

            <div class="kh-user-create__layout">
                <section class="kh-user-create__card kh-user-create__form-card" aria-labelledby="create-user-title">
                    <div class="kh-user-create__card-head">
                        <div>
                            <span class="kh-user-create__card-kicker">New account</span>
                            <h2 id="create-user-title">User details</h2>
                            <p>Enter the information this person will use to access KH-WORKS.</p>
                        </div>
                        <span class="kh-user-create__required-note"><b aria-hidden="true">*</b> Required fields</span>
                    </div>

                    @if ($errors->any())
                        <div class="kh-user-create__error-summary" role="alert" aria-live="polite" tabindex="-1">
                            <span class="kh-user-create__error-icon" aria-hidden="true">
                                <i data-feather="alert-circle"></i>
                            </span>
                            <div>
                                <strong>Please check the highlighted fields.</strong>
                                <ul>
                                    @foreach ($errors->messages() as $field => $messages)
                                        @foreach ($messages as $message)
                                            <li>
                                                <a href="#{{ $errorTargets[$field] ?? 'createUserForm' }}">{{ $message }}</a>
                                            </li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form id="createUserForm" method="POST" action="{{ route('users.store') }}" class="kh-user-create__form">
                        @csrf

                        <fieldset class="kh-user-create__form-section">
                            <legend class="kh-user-create__legend">Personal information</legend>
                            <div class="kh-user-create__section-head">
                                <span class="kh-user-create__step" aria-hidden="true">01</span>
                                <span>
                                    <strong>Personal information</strong>
                                    <small>Basic contact details for the new team member.</small>
                                </span>
                            </div>

                            <div class="kh-user-create__grid">
                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputFirstName">
                                        First name <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap">
                                        <i data-feather="user" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('first_name')])
                                            id="inputFirstName" name="first_name" type="text"
                                            value="{{ old('first_name') }}" placeholder="e.g. Dara" autocomplete="given-name"
                                            aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}"
                                            @if ($errors->has('first_name')) aria-describedby="inputFirstNameError" @endif
                                            autofocus required>
                                    </div>
                                    @error('first_name')
                                        <p class="kh-user-create__field-error" id="inputFirstNameError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputLastName">
                                        Last name <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap">
                                        <i data-feather="user-check" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('last_name')])
                                            id="inputLastName" name="last_name" type="text"
                                            value="{{ old('last_name') }}" placeholder="e.g. Sok"
                                            autocomplete="family-name"
                                            aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}"
                                            @if ($errors->has('last_name')) aria-describedby="inputLastNameError" @endif
                                            required>
                                    </div>
                                    @error('last_name')
                                        <p class="kh-user-create__field-error" id="inputLastNameError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputEmailAddress">
                                        Email address <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap">
                                        <i data-feather="mail" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('email')])
                                            id="inputEmailAddress" name="email" type="email"
                                            value="{{ old('email') }}" placeholder="name@company.com" autocomplete="email"
                                            spellcheck="false" inputmode="email"
                                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                            @if ($errors->has('email')) aria-describedby="inputEmailAddressError" @endif
                                            required>
                                    </div>
                                    @error('email')
                                        <p class="kh-user-create__field-error" id="inputEmailAddressError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputPhone">
                                        Phone number <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap">
                                        <i data-feather="phone" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('phone_number')])
                                            id="inputPhone" name="phone_number" type="tel"
                                            value="{{ old('phone_number') }}" placeholder="e.g. +855 12 345 678"
                                            autocomplete="tel" inputmode="tel"
                                            aria-invalid="{{ $errors->has('phone_number') ? 'true' : 'false' }}"
                                            @if ($errors->has('phone_number')) aria-describedby="inputPhoneError" @endif
                                            required>
                                    </div>
                                    @error('phone_number')
                                        <p class="kh-user-create__field-error" id="inputPhoneError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="kh-user-create__form-section">
                            <legend class="kh-user-create__legend">Access and security</legend>
                            <div class="kh-user-create__section-head">
                                <span class="kh-user-create__step" aria-hidden="true">02</span>
                                <span>
                                    <strong>Access and security</strong>
                                    <small>Set permissions and secure the account.</small>
                                </span>
                            </div>

                            <div class="kh-user-create__field kh-user-create__field--wide">
                                <label class="kh-user-create__label" for="inputRole">
                                    Account role <span class="kh-required" aria-hidden="true">*</span>
                                </label>
                                <div class="kh-user-create__input-wrap kh-user-create__input-wrap--select">
                                    <i data-feather="briefcase" aria-hidden="true"></i>
                                    <select @class(['kh-user-create__control', 'is-invalid' => $errors->has('role')])
                                        id="inputRole" name="role"
                                        aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                                        aria-describedby="inputRoleHint{{ $errors->has('role') ? ' inputRoleError' : '' }}" required>
                                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Choose an access level</option>
                                        <option value="Administrator" {{ old('role') === 'Administrator' ? 'selected' : '' }}>Administrator — full platform access</option>
                                        <option value="Manager" {{ old('role') === 'Manager' ? 'selected' : '' }}>Manager — manage operational content</option>
                                        <option value="User" {{ old('role') === 'User' ? 'selected' : '' }}>User — standard account access</option>
                                    </select>
                                </div>
                                <p class="kh-user-create__field-hint" id="inputRoleHint">Choose the lowest access level this person needs.</p>
                                @error('role')
                                    <p class="kh-user-create__field-error" id="inputRoleError">
                                        <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="kh-user-create__grid kh-user-create__grid--passwords">
                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputPassword">
                                        Password <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap kh-user-create__input-wrap--password">
                                        <i data-feather="lock" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('password')])
                                            id="inputPassword" name="password" type="password"
                                            placeholder="Create a secure password" autocomplete="new-password"
                                            minlength="8"
                                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                            aria-describedby="inputPasswordHint{{ $errors->has('password') ? ' inputPasswordError' : '' }}" required>
                                        <button class="kh-user-create__password-toggle" type="button"
                                            data-password-toggle="inputPassword" aria-label="Show password"
                                            aria-pressed="false">
                                            <i class="far fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <p class="kh-user-create__field-hint" id="inputPasswordHint">Use at least 8 characters.</p>
                                    @error('password')
                                        <p class="kh-user-create__field-error" id="inputPasswordError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputPasswordConfirm">
                                        Confirm password <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap kh-user-create__input-wrap--password">
                                        <i data-feather="shield" aria-hidden="true"></i>
                                        <input class="kh-user-create__control" id="inputPasswordConfirm"
                                            name="password_confirmation" type="password" placeholder="Repeat the password"
                                            autocomplete="new-password" minlength="8" required>
                                        <button class="kh-user-create__password-toggle" type="button"
                                            data-password-toggle="inputPasswordConfirm" aria-label="Show password confirmation"
                                            aria-pressed="false">
                                            <i class="far fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="kh-user-create__actions">
                            <p>
                                <i data-feather="shield" aria-hidden="true"></i>
                                Passwords are securely hashed before storage.
                            </p>
                            <div class="kh-user-create__button-row">
                                <a class="kh-user-create__btn kh-user-create__btn--ghost" href="{{ route('users') }}">
                                    Cancel
                                </a>
                                <button class="kh-user-create__btn kh-user-create__btn--primary" type="submit">
                                    <i data-feather="user-plus" aria-hidden="true"></i>
                                    <span>Create user</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <aside class="kh-user-create__side" aria-label="User creation guidance">
                    <section class="kh-user-create__card kh-user-create__guide-card">
                        <span class="kh-user-create__card-kicker">Before you save</span>
                        <h2>Account checklist</h2>
                        <p>A quick review keeps access safe and onboarding smooth.</p>

                        <ol class="kh-user-create__checklist">
                            <li>
                                <span aria-hidden="true"><i data-feather="mail"></i></span>
                                <div>
                                    <strong>Confirm the email</strong>
                                    <small>It will be used for sign-in and recovery.</small>
                                </div>
                            </li>
                            <li>
                                <span aria-hidden="true"><i data-feather="key"></i></span>
                                <div>
                                    <strong>Limit permissions</strong>
                                    <small>Only grant the access required for the role.</small>
                                </div>
                            </li>
                            <li>
                                <span aria-hidden="true"><i data-feather="send"></i></span>
                                <div>
                                    <strong>Share credentials safely</strong>
                                    <small>Use a trusted channel after creating the account.</small>
                                </div>
                            </li>
                        </ol>
                    </section>

                    <section class="kh-user-create__card kh-user-create__role-card">
                        <div class="kh-user-create__role-head">
                            <span aria-hidden="true"><i data-feather="shield"></i></span>
                            <div>
                                <span class="kh-user-create__card-kicker">Access guide</span>
                                <h2>Choose the right role</h2>
                            </div>
                        </div>

                        <dl class="kh-user-create__role-list">
                            <div>
                                <dt><span class="kh-user-create__role-dot kh-user-create__role-dot--admin"></span>Administrator</dt>
                                <dd>Full configuration and team access.</dd>
                            </div>
                            <div>
                                <dt><span class="kh-user-create__role-dot kh-user-create__role-dot--manager"></span>Manager</dt>
                                <dd>Operational and content management.</dd>
                            </div>
                            <div>
                                <dt><span class="kh-user-create__role-dot kh-user-create__role-dot--user"></span>User</dt>
                                <dd>Standard platform access.</dd>
                            </div>
                        </dl>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/users-create.js') }}"></script>
@endpush
