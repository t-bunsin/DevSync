{{-- Shared by create and edit. $company is an empty model on create. --}}
@php($isEdit = $company->exists)

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

<div class="kh-bo__form-card">
    <div class="kh-bo__grid">
        <div class="kh-bo__field kh-bo__field--wide">
            <span class="kh-bo__label">Logo</span>
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
                    <span class="kh-bo__hint">PNG, JPG, SVG or WebP, up to 2 MB.</span>

                    @if ($isEdit && $company->logo)
                        <label class="kh-bo__checkbox">
                            <input type="checkbox" name="remove_logo" value="1"> Remove the current logo
                        </label>
                    @endif

                    @error('logo') <span class="kh-bo__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="cover">Cover image</label>
            @if ($company->coverUrl())
                <img src="{{ $company->coverUrl() }}" alt=""
                    style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 14px; border: 1px solid var(--kh-line);">
            @endif
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('cover')])
                id="cover" name="cover" type="file" accept="image/png,image/jpeg,image/webp">
            <span class="kh-bo__hint">Wide banner behind the company name on the public job pages. Up to 4 MB.</span>

            @if ($isEdit && $company->cover)
                <label class="kh-bo__checkbox">
                    <input type="checkbox" name="remove_cover" value="1"> Remove the current cover
                </label>
            @endif

            @error('cover') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="name">Company name <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('name')])
                id="name" name="name" type="text" maxlength="255" required
                value="{{ old('name', $company->name) }}" placeholder="e.g. ABA Bank">
            @error('name') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="registration_no">Registration number</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('registration_no')])
                id="registration_no" name="registration_no" type="text" maxlength="120"
                value="{{ old('registration_no', $company->registration_no) }}" placeholder="e.g. KH-CO-2026-0148">
            @error('registration_no') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="employer_type">Employer type</label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('employer_type')])
                id="employer_type" name="employer_type">
                <option value="">Not specified</option>
                @foreach (\App\Models\Company::employerTypes() as $type)
                    <option value="{{ $type }}" @selected(old('employer_type', $company->employer_type) === $type)>{{ $type }}</option>
                @endforeach
            </select>
            @error('employer_type') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="industry">Industry</label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('industry')])
                id="industry" name="industry">
                <option value="">Not specified</option>
                @foreach (\App\Models\Company::industries() as $industry)
                    <option value="{{ $industry }}" @selected(old('industry', $company->industry) === $industry)>{{ $industry }}</option>
                @endforeach
            </select>
            @error('industry') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="employee_count">No. employees</label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('employee_count')])
                id="employee_count" name="employee_count">
                <option value="">Not specified</option>
                @foreach (\App\Models\Company::employeeRanges() as $range)
                    <option value="{{ $range }}" @selected(old('employee_count', $company->employee_count) === $range)>{{ $range }}</option>
                @endforeach
            </select>
            @error('employee_count') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        {{-- Status is the platform's verification decision, so an employer sees
             where they stand but cannot set it. CompaniesController::validated()
             drops the field for them too — this is presentation, not the gate. --}}
        <div class="kh-bo__field">
            <label class="kh-bo__label" for="status">
                Status
                @if (auth()->user()?->isAdmin())
                    <span class="kh-bo__required" aria-hidden="true">*</span>
                @endif
            </label>

            @if (auth()->user()?->isAdmin())
                <select @class(['kh-bo__control', 'is-invalid' => $errors->has('status')]) id="status" name="status" required>
                    @foreach (\App\Models\Company::statuses() as $status)
                        <option value="{{ $status }}"
                            @selected(old('status', $company->status ?? \App\Models\Company::STATUS_APPROVED) === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                <span class="kh-bo__hint">Only approved companies can be picked when posting a job or signing up.</span>
                @error('status') <span class="kh-bo__error">{{ $message }}</span> @enderror
            @else
                @php($current = $company->status ?? \App\Models\Company::STATUS_PENDING)
                <p>
                    <span @class([
                        'kh-bo__status',
                        'kh-bo__status--verified' => $current === \App\Models\Company::STATUS_APPROVED,
                        'kh-bo__status--rejected' => $current === \App\Models\Company::STATUS_REJECTED,
                        'kh-bo__status--pending' => $current === \App\Models\Company::STATUS_PENDING,
                    ])>{{ ucfirst($current) }}</span>
                </p>
                <span class="kh-bo__hint">Set by KH-WORKS when your company is reviewed. Only approved companies appear to job seekers.</span>
            @endif
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="email">Email</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('email')])
                id="email" name="email" type="email" maxlength="255"
                value="{{ old('email', $company->email) }}" placeholder="careers@company.com">
            @error('email') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="phone">Phone</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('phone')])
                id="phone" name="phone" type="tel" maxlength="50"
                value="{{ old('phone', $company->phone) }}" placeholder="+855 12 345 678">
            @error('phone') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="website">Website</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('website')])
                id="website" name="website" type="url" maxlength="255"
                value="{{ old('website', $company->website) }}" placeholder="https://company.com">
            @error('website') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="facebook">Facebook</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('facebook')])
                id="facebook" name="facebook" type="url" maxlength="255"
                value="{{ old('facebook', $company->facebook) }}" placeholder="https://facebook.com/company">
            @error('facebook') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="linkedin">LinkedIn</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('linkedin')])
                id="linkedin" name="linkedin" type="url" maxlength="255"
                value="{{ old('linkedin', $company->linkedin) }}" placeholder="https://linkedin.com/company/company">
            @error('linkedin') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="address">Address</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('address')])
                id="address" name="address" type="text" maxlength="255"
                value="{{ old('address', $company->address) }}" placeholder="Street, city, country">
            @error('address') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="description">About</label>
            <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('description')])
                id="description" name="description" maxlength="2000"
                placeholder="What this employer does.">{{ old('description', $company->description) }}</textarea>
            @error('description') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <span class="kh-bo__label">Employer profile</span>
            <span class="kh-bo__hint">
                These sections appear on the Company tab of every job this employer posts.
                Blank ones are left out rather than shown empty.
            </span>
        </div>

        @foreach ([
            ['vision_mission', 'Company Vision and Mission', 'Slogan, vision, values.'],
            ['what_we_do', 'What we do', 'What the business actually does, in the employer’s own words.'],
            ['why_join_us', 'Why you should join us', 'Benefits and reasons to apply. One per line reads well.'],
            ['workplace_culture', 'Our workplace and culture', 'Office, team and working style.'],
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
        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('companies') }}">Cancel</a>
        <button class="kh-bo__btn" type="submit">{{ $isEdit ? 'Save changes' : 'Create company' }}</button>
    </div>
</div>
