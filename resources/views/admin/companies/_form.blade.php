{{-- Shared by create and edit. $company is an empty model on create. --}}
@php($isEdit = $company->exists)

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

<div class="kh-bo__form-card">
    <div class="kh-bo__grid">
        <div class="kh-bo__field kh-bo__field--wide">
            <span class="kh-bo__label">{{ __('ui.bo.companies.form.logo') }}</span>
            <div class="kh-bo__logo-field">
                <span class="kh-bo__logo-preview" aria-hidden="true">
                    @if ($company->logoUrl())
                        <img src="{{ $company->logoUrl() }}" alt="">
                    @else
                        {{ $isEdit ? $company->initials() : '—' }}
                    @endif
                </span>
                <div class="kh-bo__field" style="flex: 1;">
                    <input @class(['kh-bo__control', 'is-invalid' => $errors->has('logo')])
                        id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp">
                    <span class="kh-bo__hint">{{ __('ui.bo.companies.form.logo_hint') }}</span>

                    @if ($isEdit && $company->logo)
                        <label class="kh-bo__checkbox">
                            <input type="checkbox" name="remove_logo" value="1"> {{ __('ui.bo.companies.form.logo_remove') }}
                        </label>
                    @endif

                    @error('logo') <span class="kh-bo__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="cover">{{ __('ui.bo.companies.form.cover') }}</label>
            @if ($company->coverUrl())
                <img src="{{ $company->coverUrl() }}" alt=""
                    style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 14px; border: 1px solid var(--kh-line);">
            @endif
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('cover')])
                id="cover" name="cover" type="file" accept="image/png,image/jpeg,image/webp">
            <span class="kh-bo__hint">{{ __('ui.bo.companies.form.cover_hint') }}</span>

            @if ($isEdit && $company->cover)
                <label class="kh-bo__checkbox">
                    <input type="checkbox" name="remove_cover" value="1"> {{ __('ui.bo.companies.form.cover_remove') }}
                </label>
            @endif

            @error('cover') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="name">{{ __('ui.bo.companies.form.name') }} <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('name')])
                id="name" name="name" type="text" maxlength="255" required
                value="{{ old('name', $company->name) }}" placeholder="{{ __('ui.bo.companies.form.name_placeholder') }}">
            @error('name') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="registration_no">{{ __('ui.bo.companies.form.registration') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('registration_no')])
                id="registration_no" name="registration_no" type="text" maxlength="120"
                value="{{ old('registration_no', $company->registration_no) }}" placeholder="e.g. KH-CO-2026-0148">
            @error('registration_no') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="employer_type">{{ __('ui.bo.companies.form.employer_type') }}</label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('employer_type')])
                id="employer_type" name="employer_type">
                <option value="">{{ __('ui.bo.companies.form.not_specified') }}</option>
                @foreach (\App\Models\Company::employerTypes() as $type)
                    <option value="{{ $type }}" @selected(old('employer_type', $company->employer_type) === $type)>{{ __('ui.bo.options.employer_type.' . $type) }}</option>
                @endforeach
            </select>
            @error('employer_type') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="industry">{{ __('ui.bo.companies.form.industry') }}</label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('industry')])
                id="industry" name="industry">
                <option value="">{{ __('ui.bo.companies.form.not_specified') }}</option>
                @foreach (\App\Models\Company::industries() as $industry)
                    <option value="{{ $industry }}" @selected(old('industry', $company->industry) === $industry)>{{ __('ui.bo.options.industry.' . $industry) }}</option>
                @endforeach
            </select>
            @error('industry') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="employee_count">{{ __('ui.bo.companies.form.employees') }}</label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('employee_count')])
                id="employee_count" name="employee_count">
                <option value="">{{ __('ui.bo.companies.form.not_specified') }}</option>
                @foreach (\App\Models\Company::employeeRanges() as $range)
                    <option value="{{ $range }}" @selected(old('employee_count', $company->employee_count) === $range)>{{ __('ui.bo.options.employees.' . $range) }}</option>
                @endforeach
            </select>
            @error('employee_count') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        {{-- Status is the platform's verification decision, so an employer sees
             where they stand but cannot set it. CompaniesController::validated()
             drops the field for them too — this is presentation, not the gate. --}}
        <div class="kh-bo__field">
            <label class="kh-bo__label" for="status">
                {{ __('ui.bo.companies.form.status') }}
                @if (auth()->user()?->isAdmin())
                    <span class="kh-bo__required" aria-hidden="true">*</span>
                @endif
            </label>

            @if (auth()->user()?->isAdmin())
                <select @class(['kh-bo__control', 'is-invalid' => $errors->has('status')]) id="status" name="status" required>
                    @foreach (\App\Models\Company::statuses() as $status)
                        <option value="{{ $status }}"
                            @selected(old('status', $company->status ?? \App\Models\Company::STATUS_APPROVED) === $status)>
                            {{ __('ui.bo.status.' . $status) }}
                        </option>
                    @endforeach
                </select>
                <span class="kh-bo__hint">{{ __('ui.bo.companies.form.status_hint_admin') }}</span>
                @error('status') <span class="kh-bo__error">{{ $message }}</span> @enderror
            @else
                @php($current = $company->status ?? \App\Models\Company::STATUS_PENDING)
                <p>
                    <span @class([
                        'kh-bo__status',
                        'kh-bo__status--verified' => $current === \App\Models\Company::STATUS_APPROVED,
                        'kh-bo__status--rejected' => $current === \App\Models\Company::STATUS_REJECTED,
                        'kh-bo__status--pending' => $current === \App\Models\Company::STATUS_PENDING,
                    ])>{{ __('ui.bo.status.' . $current) }}</span>
                </p>
                <span class="kh-bo__hint">{{ __('ui.bo.companies.form.status_hint_employer') }}</span>
            @endif
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="email">{{ __('ui.bo.companies.form.email') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('email')])
                id="email" name="email" type="email" maxlength="255"
                value="{{ old('email', $company->email) }}" placeholder="{{ __('ui.bo.companies.form.email_placeholder') }}">
            @error('email') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="phone">{{ __('ui.bo.companies.form.phone') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('phone')])
                id="phone" name="phone" type="tel" maxlength="50"
                value="{{ old('phone', $company->phone) }}" placeholder="+855 12 345 678">
            @error('phone') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="website">{{ __('ui.bo.companies.form.website') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('website')])
                id="website" name="website" type="url" maxlength="255"
                value="{{ old('website', $company->website) }}" placeholder="{{ __('ui.bo.companies.form.website_placeholder') }}">
            @error('website') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="facebook">{{ __('ui.bo.companies.form.facebook') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('facebook')])
                id="facebook" name="facebook" type="url" maxlength="255"
                value="{{ old('facebook', $company->facebook) }}" placeholder="{{ __('ui.bo.companies.form.facebook_placeholder') }}">
            @error('facebook') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="linkedin">{{ __('ui.bo.companies.form.linkedin') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('linkedin')])
                id="linkedin" name="linkedin" type="url" maxlength="255"
                value="{{ old('linkedin', $company->linkedin) }}" placeholder="{{ __('ui.bo.companies.form.linkedin_placeholder') }}">
            @error('linkedin') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="tiktok">{{ __('ui.bo.companies.form.tiktok') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('tiktok')])
                id="tiktok" name="tiktok" type="url" maxlength="255"
                value="{{ old('tiktok', $company->tiktok) }}" placeholder="{{ __('ui.bo.companies.form.tiktok_placeholder') }}">
            @error('tiktok') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="address">{{ __('ui.bo.companies.form.address') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('address')])
                id="address" name="address" type="text" maxlength="255"
                value="{{ old('address', $company->address) }}" placeholder="{{ __('ui.bo.companies.form.address_placeholder') }}">
            @error('address') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="description">{{ __('ui.bo.companies.form.about') }}</label>
            <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('description')])
                id="description" name="description" maxlength="2000"
                placeholder="{{ __('ui.bo.companies.form.about_placeholder') }}">{{ old('description', $company->description) }}</textarea>
            @error('description') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <span class="kh-bo__label">{{ __('ui.bo.companies.form.profile') }}</span>
            <span class="kh-bo__hint">
                {{ __('ui.bo.companies.form.profile_hint') }}
            </span>
        </div>

        @foreach ([
            ['vision_mission', __('ui.bo.companies.form.vision_mission'), __('ui.bo.companies.form.vision_mission_hint')],
            ['what_we_do', __('ui.bo.companies.form.what_we_do'), __('ui.bo.companies.form.what_we_do_hint')],
            ['why_join_us', __('ui.bo.companies.form.why_join_us'), __('ui.bo.companies.form.why_join_us_hint')],
            ['workplace_culture', __('ui.bo.companies.form.workplace_culture'), __('ui.bo.companies.form.workplace_culture_hint')],
        ] as [$field, $label, $hint])
            <div class="kh-bo__field kh-bo__field--wide">
                <label class="kh-bo__label" for="{{ $field }}">{{ $label }}</label>
                <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has($field)])
                    id="{{ $field }}" name="{{ $field }}" maxlength="6000" style="min-height: 130px;"
                    placeholder="{{ $hint }}">{{ old($field, $company->{$field}) }}</textarea>
                <span class="kh-bo__hint">{{ $hint }}</span>
                @error($field) <span class="kh-bo__error">{{ $message }}</span> @enderror
            </div>
        @endforeach
    </div>

    <div class="kh-bo__form-actions">
        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('companies') }}">{{ __('ui.bo.job_posts.form.cancel') }}</a>
        <button class="kh-bo__btn" type="submit">{{ $isEdit ? __('ui.bo.save') : __('ui.bo.companies.submit_create') }}</button>
    </div>
</div>
