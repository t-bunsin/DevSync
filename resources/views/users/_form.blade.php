{{-- Shared by create and edit. $user is an empty model on create. --}}
@php
    $isEdit = $user->exists;

    // Guarded on $isEdit so an unsaved model never touches its relations.
    $currentRole = old('role', $isEdit ? ($user->primaryRole()?->code ?? 'employee') : 'employee');
    $currentCompany = old('company_name', $isEdit ? $user->employerProfile?->company_name : null);
@endphp

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

<div class="kh-bo__form-card">
    <div class="kh-bo__grid">
        <div class="kh-bo__section-label">
            {{ __('ui.bo.users.form.personal') }}
            <span>{{ $isEdit ? __('ui.bo.users.form.personal_hint_edit') : __('ui.bo.users.form.personal_hint_create') }}</span>
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="first_name">{{ __('ui.bo.users.form.first_name') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('first_name')])
                id="first_name" name="first_name" type="text" maxlength="80" required autofocus
                autocomplete="given-name"
                value="{{ old('first_name', $user->first_name) }}" placeholder="{{ __('ui.bo.users.form.first_name_placeholder') }}">
            <span class="kh-bo__hint">{{ __('ui.bo.users.form.name_hint') }}</span>
            @error('first_name') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="last_name">{{ __('ui.bo.users.form.last_name') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('last_name')])
                id="last_name" name="last_name" type="text" maxlength="80" required
                autocomplete="family-name"
                value="{{ old('last_name', $user->last_name) }}" placeholder="{{ __('ui.bo.users.form.last_name_placeholder') }}">
            @error('last_name') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="email">{{ __('ui.bo.users.form.email') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('email')])
                id="email" name="email" type="email" required
                autocomplete="email" inputmode="email" spellcheck="false"
                value="{{ old('email', $user->email) }}" placeholder="{{ __('ui.bo.users.form.email_placeholder') }}">
            @error('email') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="phone">{{ __('ui.bo.users.form.phone') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('phone')])
                id="phone" name="phone" type="tel" maxlength="30" required
                autocomplete="tel" inputmode="tel"
                value="{{ old('phone', $user->phone) }}" placeholder="+855 12 345 678">
            <span class="kh-bo__hint">{{ __('ui.bo.users.form.phone_hint') }}</span>
            @error('phone') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="status">{{ __('ui.bo.users.form.status') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('status')]) id="status" name="status" required>
                @foreach (['active', 'pending', 'suspended', 'banned'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $user->status ?? 'active') === $status)>
                        {{ __('ui.bo.users.form.status_' . $status) }}
                    </option>
                @endforeach
            </select>
            @error('status') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="preferred_locale">{{ __('ui.bo.users.form.locale') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            @php($currentLocale = old('preferred_locale', $user->preferred_locale ?? 'en'))
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('preferred_locale')])
                id="preferred_locale" name="preferred_locale" required>
                <option value="en" @selected($currentLocale === 'en')>{{ __('ui.language.english') }}</option>
                <option value="km" @selected($currentLocale === 'km')>{{ __('ui.language.khmer') }}</option>
            </select>
            @error('preferred_locale') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__section-label">
            {{ __('ui.bo.users.form.access') }}
            <span>{{ $isEdit ? __('ui.bo.users.form.access_hint_edit') : __('ui.bo.users.form.access_hint_create') }}</span>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="role">{{ __('ui.bo.users.form.role') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('role')])
                id="role" name="role" data-role-select required>
                @foreach ($roles as $role)
                    <option value="{{ $role->code }}" @selected($currentRole === $role->code)>
                        {{ $role->name_en }} — {{ $role->description }}
                    </option>
                @endforeach
            </select>
            <span class="kh-bo__hint">{{ $isEdit ? __('ui.bo.users.form.role_hint_edit') : __('ui.bo.users.form.role_hint_create') }}</span>
            @error('role') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        {{-- Company name only applies to employers; users-create.js hides it
             and drops the required flag for every other role. --}}
        <div class="kh-bo__field kh-bo__field--wide" data-employer-only
            @if ($currentRole !== 'employer') hidden @endif>
            <label class="kh-bo__label" for="company_name">{{ __('ui.bo.users.form.company') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('company_name')])
                id="company_name" name="company_name" type="text" maxlength="255"
                value="{{ $currentCompany }}" placeholder="{{ __('ui.bo.users.form.company_placeholder') }}">
            <span class="kh-bo__hint">{{ $isEdit ? __('ui.bo.users.form.company_hint_edit') : __('ui.bo.users.form.company_hint_create') }}</span>
            @error('company_name') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        {{-- On edit the password is optional: leaving both boxes empty keeps the
             current one, which is why nothing here is required. --}}
        <div class="kh-bo__field">
            <label class="kh-bo__label" for="inputPassword">
                {{ $isEdit ? __('ui.bo.users.form.password_new') : __('ui.bo.users.form.password') }}
                @unless ($isEdit) <span class="kh-bo__required" aria-hidden="true">*</span> @endunless
            </label>
            <div class="kh-bo__password">
                <input @class(['kh-bo__control', 'is-invalid' => $errors->has('password')])
                    id="inputPassword" name="password" type="password" minlength="8" autocomplete="new-password"
                    @unless ($isEdit) required @endunless
                    placeholder="{{ $isEdit ? __('ui.bo.users.form.password_placeholder_edit') : __('ui.bo.users.form.password_placeholder') }}">
                <button class="kh-bo__password-toggle" type="button" data-password-toggle="inputPassword"
                    aria-label="{{ __('ui.bo.users.form.show_password') }}" aria-pressed="false">
                    <i class="far fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <span class="kh-bo__hint">{{ $isEdit ? __('ui.bo.users.form.password_hint_edit') : __('ui.bo.users.form.password_hint') }}</span>
            @error('password') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="inputPasswordConfirm">
                {{ $isEdit ? __('ui.bo.users.form.password_confirm_new') : __('ui.bo.users.form.password_confirm') }}
                @unless ($isEdit) <span class="kh-bo__required" aria-hidden="true">*</span> @endunless
            </label>
            <div class="kh-bo__password">
                <input class="kh-bo__control" id="inputPasswordConfirm" name="password_confirmation"
                    type="password" minlength="8" autocomplete="new-password"
                    @unless ($isEdit) required @endunless
                    placeholder="{{ $isEdit ? __('ui.bo.users.form.password_confirm_placeholder_edit') : __('ui.bo.users.form.password_confirm_placeholder') }}">
                <button class="kh-bo__password-toggle" type="button" data-password-toggle="inputPasswordConfirm"
                    aria-label="{{ __('ui.bo.users.form.show_password_confirm') }}" aria-pressed="false">
                    <i class="far fa-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <span class="kh-bo__hint">{{ __('ui.bo.users.form.hashed_note') }}</span>
        </div>
    </div>

    <div class="kh-bo__form-actions">
        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('users') }}">{{ __('ui.bo.job_posts.form.cancel') }}</a>
        <button class="kh-bo__btn" type="submit">{{ $isEdit ? __('ui.bo.save') : __('ui.bo.users.form.submit_create') }}</button>
    </div>
</div>
