{{-- Shared by create and edit. $resume is an empty model on create. --}}
@php
    $isEdit = $resume->exists;

    // Old input wins after a failed validation pass, so half-typed rows survive
    // the round trip; otherwise fall back to what is stored.
    $rowsFor = function (string $section) use ($resume) {
        $rows = old($section, $resume->section($section));

        return $rows === [] ? [[]] : array_values((array) $rows);
    };

    $skillsValue = old('skills', implode("\n", $resume->skillList()));
@endphp

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
        <div class="kh-bo__section-label">
            {{ __('ui.bo.resumes.form.header') }}
            <span>{{ __('ui.bo.resumes.form.header_hint') }}</span>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <span class="kh-bo__label">{{ __('ui.bo.resumes.form.photo') }}</span>
            <div class="kh-bo__logo-field">
                <span class="kh-bo__logo-preview" aria-hidden="true">
                    @if ($resume->photoUrl())
                        <img src="{{ $resume->photoUrl() }}" alt="">
                    @else
                        {{ $isEdit ? $resume->initials() : '—' }}
                    @endif
                </span>
                <div class="kh-bo__field" style="flex: 1;">
                    <input @class(['kh-bo__control', 'is-invalid' => $errors->has('photo')])
                        id="photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp">
                    <span class="kh-bo__hint">{{ __('ui.bo.resumes.form.photo_hint') }}</span>

                    @if ($isEdit && $resume->photo)
                        <label class="kh-bo__checkbox">
                            <input type="checkbox" name="remove_photo" value="1">
                            {{ __('ui.bo.resumes.form.photo_remove') }}
                        </label>
                    @endif

                    @error('photo')
                        <span class="kh-bo__error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="full_name">
                {{ __('ui.bo.resumes.form.full_name') }} <span class="kh-bo__required" aria-hidden="true">*</span>
            </label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('full_name')])
                id="full_name" name="full_name" type="text" maxlength="255" required
                value="{{ old('full_name', $resume->full_name) }}" placeholder="{{ __('ui.bo.resumes.form.full_name_placeholder') }}">
            @error('full_name')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="headline">{{ __('ui.bo.resumes.form.headline') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('headline')])
                id="headline" name="headline" type="text" maxlength="120"
                value="{{ old('headline', $resume->headline) }}" placeholder="{{ __('ui.bo.resumes.form.headline_placeholder') }}">
            <span class="kh-bo__hint">{{ __('ui.bo.resumes.form.headline_hint') }}</span>
            @error('headline')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="email">{{ __('ui.bo.resumes.form.email') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('email')])
                id="email" name="email" type="email" maxlength="255"
                value="{{ old('email', $resume->email) }}" placeholder="{{ __('ui.bo.resumes.form.email_placeholder') }}">
            @error('email')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="phone">{{ __('ui.bo.resumes.form.phone') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('phone')])
                id="phone" name="phone" type="text" maxlength="40"
                value="{{ old('phone', $resume->phone) }}" placeholder="(555) 555-5555">
            @error('phone')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="location">{{ __('ui.bo.resumes.form.location') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('location')])
                id="location" name="location" type="text" maxlength="255"
                value="{{ old('location', $resume->location) }}" placeholder="{{ __('ui.bo.resumes.form.location_placeholder') }}">
            @error('location')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="status">
                {{ __('ui.bo.resumes.form.status') }} <span class="kh-bo__required" aria-hidden="true">*</span>
            </label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('status')])
                id="status" name="status" required>
                @foreach (\App\Models\Resume::statuses() as $status)
                    <option value="{{ $status }}"
                        @selected(old('status', $resume->status ?? \App\Models\Resume::STATUS_DRAFT) === $status)>
                        {{ __('ui.bo.status.' . $status) }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="summary">{{ __('ui.bo.resumes.form.summary') }}</label>
            <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('summary')])
                id="summary" name="summary" maxlength="2000"
                placeholder="{{ __('ui.bo.resumes.form.summary_placeholder') }}">{{ old('summary', $resume->summary) }}</textarea>
            @error('summary')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Work history ------------------------------------------------------ --}}
    <div class="kh-bo__repeater" data-bo-repeater="work_history">
        <div class="kh-bo__section-label">
            {{ __('ui.bo.resumes.form.work') }}
            <span>{{ __('ui.bo.resumes.form.work_hint') }}</span>
        </div>

        <div data-bo-rows>
            @foreach ($rowsFor('work_history') as $index => $row)
                <fieldset class="kh-bo__repeat-row" data-bo-row>
                    <legend class="kh-bo__repeat-title">{{ __('ui.bo.resumes.form.role') }} <span data-bo-number>{{ $index + 1 }}</span></legend>
                    <button class="kh-bo__repeat-remove" type="button" data-bo-remove
                        aria-label="{{ __('ui.bo.resumes.form.role_remove') }}">&times;</button>

                    <div class="kh-bo__grid">
                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.job_title') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="work_history[{{ $index }}][role]"
                                value="{{ $row['role'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.job_title_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.employer') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="work_history[{{ $index }}][company]"
                                value="{{ $row['company'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.employer_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.location') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="work_history[{{ $index }}][location]"
                                value="{{ $row['location'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.location_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <span class="kh-bo__label">{{ __('ui.bo.resumes.form.dates') }}</span>
                            <div class="kh-bo__range">
                                <input class="kh-bo__control" type="month"
                                    name="work_history[{{ $index }}][started_on]"
                                    value="{{ $row['started_on'] ?? '' }}" aria-label="{{ __('ui.bo.resumes.form.started') }}">
                                <span aria-hidden="true">–</span>
                                <input class="kh-bo__control" type="month"
                                    name="work_history[{{ $index }}][ended_on]"
                                    value="{{ $row['ended_on'] ?? '' }}" aria-label="{{ __('ui.bo.resumes.form.ended') }}">
                            </div>
                        </div>

                        <div class="kh-bo__field kh-bo__field--wide">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.achievements') }}</label>
                            <textarea class="kh-bo__control" name="work_history[{{ $index }}][bullets]"
                                placeholder="{{ __('ui.bo.resumes.form.one_per_line') }}">{{ is_array($row['bullets'] ?? null) ? implode("\n", $row['bullets']) : ($row['bullets'] ?? '') }}</textarea>
                            <span class="kh-bo__hint">{{ __('ui.bo.resumes.form.one_bullet_hint') }}</span>
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-add>{{ __('ui.bo.resumes.form.add_role') }}</button>
    </div>

    {{-- Skills ------------------------------------------------------------ --}}
    <div class="kh-bo__grid">
        <div class="kh-bo__section-label">
            {{ __('ui.bo.resumes.form.skills') }}
            <span>{{ __('ui.bo.resumes.form.skills_hint') }}</span>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="skills">{{ __('ui.bo.resumes.form.skills') }}</label>
            <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('skills')])
                id="skills" name="skills" maxlength="2000"
                placeholder="{{ __('ui.bo.resumes.form.one_per_line') }}">{{ $skillsValue }}</textarea>
            <span class="kh-bo__hint">{{ __('ui.bo.resumes.form.one_skill_hint') }}</span>
            @error('skills')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Certifications ----------------------------------------------------- --}}
    <div class="kh-bo__repeater" data-bo-repeater="certifications">
        <div class="kh-bo__section-label">
            {{ __('ui.bo.resumes.form.certifications') }}
            <span>{{ __('ui.bo.resumes.form.certifications_hint') }}</span>
        </div>

        <div data-bo-rows>
            @foreach ($rowsFor('certifications') as $index => $row)
                <fieldset class="kh-bo__repeat-row" data-bo-row>
                    <legend class="kh-bo__repeat-title">{{ __('ui.bo.resumes.form.certificate') }} <span data-bo-number>{{ $index + 1 }}</span></legend>
                    <button class="kh-bo__repeat-remove" type="button" data-bo-remove
                        aria-label="{{ __('ui.bo.resumes.form.certificate_remove') }}">&times;</button>

                    <div class="kh-bo__grid">
                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.certificate') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="certifications[{{ $index }}][name]"
                                value="{{ $row['name'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.certificate_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.issued_by') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="certifications[{{ $index }}][issuer]"
                                value="{{ $row['issuer'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.issued_by_placeholder') }}">
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-add>{{ __('ui.bo.resumes.form.add_certificate') }}</button>
    </div>

    {{-- Education --------------------------------------------------------- --}}
    <div class="kh-bo__repeater" data-bo-repeater="education">
        <div class="kh-bo__section-label">
            {{ __('ui.bo.resumes.form.education') }}
            <span>{{ __('ui.bo.resumes.form.education_hint') }}</span>
        </div>

        <div data-bo-rows>
            @foreach ($rowsFor('education') as $index => $row)
                <fieldset class="kh-bo__repeat-row" data-bo-row>
                    <legend class="kh-bo__repeat-title">{{ __('ui.bo.resumes.form.qualification') }} <span data-bo-number>{{ $index + 1 }}</span></legend>
                    <button class="kh-bo__repeat-remove" type="button" data-bo-remove
                        aria-label="{{ __('ui.bo.resumes.form.qualification_remove') }}">&times;</button>

                    <div class="kh-bo__grid">
                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.qualification') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="education[{{ $index }}][degree]"
                                value="{{ $row['degree'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.qualification_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.field_of_study') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="education[{{ $index }}][field]"
                                value="{{ $row['field'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.field_of_study_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.institution') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="education[{{ $index }}][institution]"
                                value="{{ $row['institution'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.institution_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.location') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="education[{{ $index }}][location]"
                                value="{{ $row['location'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.institution_location_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.graduated') }}</label>
                            <input class="kh-bo__control" type="month"
                                name="education[{{ $index }}][graduated_on]"
                                value="{{ $row['graduated_on'] ?? '' }}">
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-add>{{ __('ui.bo.resumes.form.add_qualification') }}</button>
    </div>

    {{-- Languages --------------------------------------------------------- --}}
    <div class="kh-bo__repeater" data-bo-repeater="languages">
        <div class="kh-bo__section-label">
            {{ __('ui.bo.resumes.form.languages') }}
            <span>{{ __('ui.bo.resumes.form.languages_hint') }}</span>
        </div>

        <div data-bo-rows>
            @foreach ($rowsFor('languages') as $index => $row)
                <fieldset class="kh-bo__repeat-row" data-bo-row>
                    <legend class="kh-bo__repeat-title">{{ __('ui.bo.resumes.form.language') }} <span data-bo-number>{{ $index + 1 }}</span></legend>
                    <button class="kh-bo__repeat-remove" type="button" data-bo-remove
                        aria-label="{{ __('ui.bo.resumes.form.language_remove') }}">&times;</button>

                    <div class="kh-bo__grid">
                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.language') }}</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="languages[{{ $index }}][name]"
                                value="{{ $row['name'] ?? '' }}" placeholder="{{ __('ui.bo.resumes.form.language_placeholder') }}">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">{{ __('ui.bo.resumes.form.proficiency') }}</label>
                            <select class="kh-bo__control" name="languages[{{ $index }}][level]">
                                <option value="">{{ __('ui.bo.resumes.form.proficiency_choose') }}</option>
                                @foreach (\App\Models\Resume::languageLevels() as $level)
                                    <option value="{{ $level }}" @selected(($row['level'] ?? '') === $level)>{{ __('ui.bo.options.language_level.' . $level) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-add>{{ __('ui.bo.resumes.form.add_language') }}</button>
    </div>

    <div class="kh-bo__form-actions">
        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.index') }}">{{ __('ui.bo.job_posts.form.cancel') }}</a>
        <button class="kh-bo__btn" type="submit">{{ $isEdit ? __('ui.bo.save') : __('ui.bo.billing.register_resume') }}</button>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/backoffice-repeater.js') }}?v={{ filemtime(public_path('js/backoffice-repeater.js')) }}" defer></script>
@endpush
