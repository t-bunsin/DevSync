@extends('layouts.admin')

@section('title', 'Edit User | KH-WORKS Admin')
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
            'phone' => 'inputPhone',
            'role' => 'inputRole',
            'status' => 'inputStatus',
            'preferred_locale' => 'inputLocale',
            'company_name' => 'inputCompanyName',
            'password' => 'inputPassword',
        ];

        $displayName = $user->displayName();
        $currentRole = old('role', $user->primaryRole()?->code ?? 'employee');
        $currentCompany = old('company_name', $user->employerProfile?->company_name);
    @endphp

    <div class="kh-user-create">
        <div class="kh-user-create__shell">
            <header class="kh-user-create__page-head">
                <div class="kh-user-create__heading">
                    <a class="kh-user-create__back" href="{{ route('users') }}" aria-label="{{ __('ui.bo.users.form.back_to_list') }}">
                        <i data-feather="arrow-left" aria-hidden="true"></i>
                    </a>

                    <span class="kh-user-create__heading-icon" aria-hidden="true">
                        <i data-feather="edit-2"></i>
                    </span>

                    <div>
                        <span class="kh-user-create__eyebrow">{{ __('ui.bo.users.form.kicker') }}</span>
                        <h1>{{ __('ui.bo.users.form.edit_title') }}</h1>
                        <p>{{ __('ui.bo.users.form.edit_lead_before') }} <strong>{{ $displayName }}</strong> {{ __('ui.bo.users.form.edit_lead_after') }}</p>
                    </div>
                </div>

                <div class="kh-user-create__theme-note">
                    <span class="kh-user-create__theme-note-icon" aria-hidden="true">
                        <i data-feather="clock"></i>
                    </span>
                    <span>
                        <strong>Joined {{ $user->created_at?->format('M d, Y') ?? 'Not available' }}</strong>
                        <small>{{ Str::before($user->id, '-') }}</small>
                    </span>
                </div>
            </header>

            <div class="kh-user-create__layout">
                <section class="kh-user-create__card kh-user-create__form-card" aria-labelledby="edit-user-title">
                    <div class="kh-user-create__card-head">
                        <div>
                            <span class="kh-user-create__card-kicker">{{ __('ui.bo.users.form.badge_existing') }}</span>
                            <h2 id="edit-user-title">{{ __('ui.bo.users.form.details') }}</h2>
                            <p>{{ __('ui.bo.users.form.details_hint_edit') }}</p>
                        </div>
                        <span class="kh-user-create__required-note"><b aria-hidden="true">*</b> {{ __('ui.bo.users.form.required_fields') }}</span>
                    </div>

                    @if ($errors->any())
                        <div class="kh-user-create__error-summary" role="alert" aria-live="polite" tabindex="-1">
                            <span class="kh-user-create__error-icon" aria-hidden="true">
                                <i data-feather="alert-circle"></i>
                            </span>
                            <div>
                                <strong>{{ __('ui.bo.check_fields') }}</strong>
                                <ul>
                                    @foreach ($errors->messages() as $field => $messages)
                                        @foreach ($messages as $message)
                                            <li>
                                                <a href="#{{ $errorTargets[$field] ?? 'editUserForm' }}">{{ $message }}</a>
                                            </li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form id="editUserForm" method="POST" action="{{ route('user.update', $user) }}" class="kh-user-create__form">
                        @csrf
                        @method('PUT')

                        <fieldset class="kh-user-create__form-section">
                            <legend class="kh-user-create__legend">{{ __('ui.bo.users.form.personal') }}</legend>
                            <div class="kh-user-create__section-head">
                                <span class="kh-user-create__step" aria-hidden="true">01</span>
                                <span>
                                    <strong>{{ __('ui.bo.users.form.personal') }}</strong>
                                    <small>{{ __('ui.bo.users.form.personal_hint_edit') }}</small>
                                </span>
                            </div>

                            <div class="kh-user-create__grid">
                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputFirstName">
                                        {{ __('ui.bo.users.form.first_name') }} <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap">
                                        <i data-feather="user" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('first_name')])
                                            id="inputFirstName" name="first_name" type="text"
                                            value="{{ old('first_name', $user->first_name) }}" placeholder="{{ __('ui.bo.users.form.first_name_placeholder') }}"
                                            autocomplete="given-name" maxlength="80"
                                            aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}"
                                            aria-describedby="inputNameHint{{ $errors->has('first_name') ? ' inputFirstNameError' : '' }}"
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
                                        {{ __('ui.bo.users.form.last_name') }} <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap">
                                        <i data-feather="user" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('last_name')])
                                            id="inputLastName" name="last_name" type="text"
                                            value="{{ old('last_name', $user->last_name) }}" placeholder="{{ __('ui.bo.users.form.last_name_placeholder') }}"
                                            autocomplete="family-name" maxlength="80"
                                            aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}"
                                            aria-describedby="inputNameHint{{ $errors->has('last_name') ? ' inputLastNameError' : '' }}"
                                            required>
                                    </div>
                                    @error('last_name')
                                        <p class="kh-user-create__field-error" id="inputLastNameError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <p class="kh-user-create__field-hint" id="inputNameHint">{{ __('ui.bo.users.form.name_hint') }}</p>

                            <div class="kh-user-create__grid">
                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputEmailAddress">
                                        {{ __('ui.bo.users.form.email') }} <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap">
                                        <i data-feather="mail" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('email')])
                                            id="inputEmailAddress" name="email" type="email"
                                            value="{{ old('email', $user->email) }}" placeholder="{{ __('ui.bo.users.form.email_placeholder') }}"
                                            autocomplete="email" spellcheck="false" inputmode="email"
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
                                    <label class="kh-user-create__label" for="inputPhone">{{ __('ui.bo.users.form.phone') }}</label>
                                    <div class="kh-user-create__input-wrap">
                                        <i data-feather="phone" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('phone')])
                                            id="inputPhone" name="phone" type="tel"
                                            value="{{ old('phone', $user->phone) }}" placeholder="e.g. +855 12 345 678"
                                            autocomplete="tel" inputmode="tel" maxlength="30"
                                            aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                                            aria-describedby="inputPhoneHint{{ $errors->has('phone') ? ' inputPhoneError' : '' }}">
                                    </div>
                                    <p class="kh-user-create__field-hint" id="inputPhoneHint">{{ __('ui.bo.users.form.phone_hint') }}</p>
                                    @error('phone')
                                        <p class="kh-user-create__field-error" id="inputPhoneError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputStatus">
                                        {{ __('ui.bo.users.form.status') }} <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap kh-user-create__input-wrap--select">
                                        <i data-feather="activity" aria-hidden="true"></i>
                                        <select @class(['kh-user-create__control', 'is-invalid' => $errors->has('status')])
                                            id="inputStatus" name="status" required>
                                            @foreach (['active' => __('ui.bo.users.form.status_active'), 'pending' => __('ui.bo.users.form.status_pending'), 'suspended' => __('ui.bo.users.form.status_suspended'), 'banned' => __('ui.bo.users.form.status_banned')] as $value => $label)
                                                <option value="{{ $value }}" {{ old('status', $user->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('status')
                                        <p class="kh-user-create__field-error" id="inputStatusError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputLocale">
                                        {{ __('ui.bo.users.form.locale') }} <span class="kh-required" aria-hidden="true">*</span>
                                    </label>
                                    <div class="kh-user-create__input-wrap kh-user-create__input-wrap--select">
                                        <i data-feather="globe" aria-hidden="true"></i>
                                        <select @class(['kh-user-create__control', 'is-invalid' => $errors->has('preferred_locale')])
                                            id="inputLocale" name="preferred_locale" required>
                                            <option value="en" {{ old('preferred_locale', $user->preferred_locale) === 'en' ? 'selected' : '' }}>{{ __('ui.language.english') }}</option>
                                            <option value="km" {{ old('preferred_locale', $user->preferred_locale) === 'km' ? 'selected' : '' }}>{{ __('ui.language.khmer') }}</option>
                                        </select>
                                    </div>
                                    @error('preferred_locale')
                                        <p class="kh-user-create__field-error" id="inputLocaleError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="kh-user-create__form-section">
                            <legend class="kh-user-create__legend">{{ __('ui.bo.users.form.access') }}</legend>
                            <div class="kh-user-create__section-head">
                                <span class="kh-user-create__step" aria-hidden="true">02</span>
                                <span>
                                    <strong>{{ __('ui.bo.users.form.access') }}</strong>
                                    <small>{{ __('ui.bo.users.form.access_hint_edit') }}</small>
                                </span>
                            </div>

                            <div class="kh-user-create__field kh-user-create__field--wide">
                                <label class="kh-user-create__label" for="inputRole">
                                    {{ __('ui.bo.users.form.role') }} <span class="kh-required" aria-hidden="true">*</span>
                                </label>
                                <div class="kh-user-create__input-wrap kh-user-create__input-wrap--select">
                                    <i data-feather="briefcase" aria-hidden="true"></i>
                                    <select @class(['kh-user-create__control', 'is-invalid' => $errors->has('role')])
                                        id="inputRole" name="role" data-role-select
                                        aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                                        aria-describedby="inputRoleHint{{ $errors->has('role') ? ' inputRoleError' : '' }}" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->code }}" {{ $currentRole === $role->code ? 'selected' : '' }}>
                                                {{ $role->name_en }} — {{ $role->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="kh-user-create__field-hint" id="inputRoleHint">{{ __('ui.bo.users.form.role_hint_edit') }}</p>
                                @error('role')
                                    <p class="kh-user-create__field-error" id="inputRoleError">
                                        <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="kh-user-create__field kh-user-create__field--wide" data-employer-only
                                @if ($currentRole !== 'employer') hidden @endif>
                                <label class="kh-user-create__label" for="inputCompanyName">
                                    {{ __('ui.bo.users.form.company') }} <span class="kh-required" aria-hidden="true">*</span>
                                </label>
                                <div class="kh-user-create__input-wrap">
                                    <i data-feather="briefcase" aria-hidden="true"></i>
                                    <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('company_name')])
                                        id="inputCompanyName" name="company_name" type="text"
                                        value="{{ $currentCompany }}" placeholder="{{ __('ui.bo.users.form.company_placeholder') }}"
                                        maxlength="255"
                                        aria-describedby="inputCompanyNameHint{{ $errors->has('company_name') ? ' inputCompanyNameError' : '' }}">
                                </div>
                                <p class="kh-user-create__field-hint" id="inputCompanyNameHint">{{ __('ui.bo.users.form.company_hint_edit') }}</p>
                                @error('company_name')
                                    <p class="kh-user-create__field-error" id="inputCompanyNameError">
                                        <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="kh-user-create__grid kh-user-create__grid--passwords">
                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputPassword">{{ __('ui.bo.users.form.password_new') }}</label>
                                    <div class="kh-user-create__input-wrap kh-user-create__input-wrap--password">
                                        <i data-feather="lock" aria-hidden="true"></i>
                                        <input @class(['kh-user-create__control', 'is-invalid' => $errors->has('password')])
                                            id="inputPassword" name="password" type="password"
                                            placeholder="{{ __('ui.bo.users.form.password_placeholder_edit') }}" autocomplete="new-password"
                                            minlength="8"
                                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                            aria-describedby="inputPasswordHint{{ $errors->has('password') ? ' inputPasswordError' : '' }}">
                                        <button class="kh-user-create__password-toggle" type="button"
                                            data-password-toggle="inputPassword" aria-label="{{ __('ui.bo.users.form.show_password') }}"
                                            aria-pressed="false">
                                            <i class="far fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <p class="kh-user-create__field-hint" id="inputPasswordHint">{{ __('ui.bo.users.form.password_hint_edit') }}</p>
                                    @error('password')
                                        <p class="kh-user-create__field-error" id="inputPasswordError">
                                            <i data-feather="alert-circle" aria-hidden="true"></i>{{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="kh-user-create__field">
                                    <label class="kh-user-create__label" for="inputPasswordConfirm">{{ __('ui.bo.users.form.password_confirm_new') }}</label>
                                    <div class="kh-user-create__input-wrap kh-user-create__input-wrap--password">
                                        <i data-feather="shield" aria-hidden="true"></i>
                                        <input class="kh-user-create__control" id="inputPasswordConfirm"
                                            name="password_confirmation" type="password" placeholder="{{ __('ui.bo.users.form.password_confirm_placeholder_edit') }}"
                                            autocomplete="new-password" minlength="8">
                                        <button class="kh-user-create__password-toggle" type="button"
                                            data-password-toggle="inputPasswordConfirm" aria-label="{{ __('ui.bo.users.form.show_password_confirm') }}"
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
                                {{ __('ui.bo.users.form.hashed_note') }}
                            </p>
                            <div class="kh-user-create__button-row">
                                <a class="kh-user-create__btn kh-user-create__btn--ghost" href="{{ route('users') }}">
                                    Cancel
                                </a>
                                <button class="kh-user-create__btn kh-user-create__btn--primary" type="submit">
                                    <i data-feather="save" aria-hidden="true"></i>
                                    <span>{{ __('ui.bo.save') }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <aside class="kh-user-create__side" aria-label="{{ __('ui.bo.users.form.guidance_edit') }}">
                    <section class="kh-user-create__card kh-user-create__guide-card">
                        <span class="kh-user-create__card-kicker">{{ __('ui.bo.users.form.before_you_save') }}</span>
                        <h2>{{ __('ui.bo.users.form.checklist_edit') }}</h2>
                        <p>{{ __('ui.bo.users.form.checklist_lead_edit') }}</p>

                        <ol class="kh-user-create__checklist">
                            <li>
                                <span aria-hidden="true"><i data-feather="mail"></i></span>
                                <div>
                                    <strong>{{ __('ui.bo.users.form.check_email') }}</strong>
                                    <small>{{ __('ui.bo.users.form.check_email_hint_edit') }}</small>
                                </div>
                            </li>
                            <li>
                                <span aria-hidden="true"><i data-feather="key"></i></span>
                                <div>
                                    <strong>{{ __('ui.bo.users.form.check_perms_edit') }}</strong>
                                    <small>{{ __('ui.bo.users.form.check_perms_hint') }}</small>
                                </div>
                            </li>
                            <li>
                                <span aria-hidden="true"><i data-feather="send"></i></span>
                                <div>
                                    <strong>{{ __('ui.bo.users.form.check_share_edit') }}</strong>
                                    <small>{{ __('ui.bo.users.form.check_share_hint_edit') }}</small>
                                </div>
                            </li>
                        </ol>
                    </section>

                    <section class="kh-user-create__card kh-user-create__role-card">
                        <div class="kh-user-create__role-head">
                            <span aria-hidden="true"><i data-feather="shield"></i></span>
                            <div>
                                <span class="kh-user-create__card-kicker">{{ __('ui.bo.users.form.access_guide') }}</span>
                                <h2>{{ __('ui.bo.users.form.choose_role') }}</h2>
                            </div>
                        </div>

                        <dl class="kh-user-create__role-list">
                            @foreach ($roles as $role)
                                <div>
                                    <dt>
                                        <span @class([
                                            'kh-user-create__role-dot',
                                            'kh-user-create__role-dot--admin' => $role->code === 'admin',
                                            'kh-user-create__role-dot--manager' => $role->code === 'employer',
                                            'kh-user-create__role-dot--user' => $role->code === 'employee',
                                        ])></span>{{ $role->name_en }}
                                    </dt>
                                    <dd>{{ $role->description }}</dd>
                                </div>
                            @endforeach
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
