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
        <div class="kh-bo__section-label">
            Header
            <span>The contact block printed at the top of the resume.</span>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <span class="kh-bo__label">Photo</span>
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
                    <span class="kh-bo__hint">JPG, PNG or WebP, up to 2 MB. Without one the initials are used.</span>

                    @if ($isEdit && $resume->photo)
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

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="full_name">
                Full name <span class="kh-bo__required" aria-hidden="true">*</span>
            </label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('full_name')])
                id="full_name" name="full_name" type="text" maxlength="255" required
                value="{{ old('full_name', $resume->full_name) }}" placeholder="e.g. Olivia Martinez">
            @error('full_name')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="headline">Headline</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('headline')])
                id="headline" name="headline" type="text" maxlength="120"
                value="{{ old('headline', $resume->headline) }}" placeholder="e.g. Builder">
            <span class="kh-bo__hint">The role this resume is aimed at.</span>
            @error('headline')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="email">Email</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('email')])
                id="email" name="email" type="email" maxlength="255"
                value="{{ old('email', $resume->email) }}" placeholder="name@example.com">
            @error('email')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="phone">Phone</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('phone')])
                id="phone" name="phone" type="text" maxlength="40"
                value="{{ old('phone', $resume->phone) }}" placeholder="(555) 555-5555">
            @error('phone')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="location">Location</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('location')])
                id="location" name="location" type="text" maxlength="255"
                value="{{ old('location', $resume->location) }}" placeholder="e.g. Hillcrest, NY">
            @error('location')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="status">
                Status <span class="kh-bo__required" aria-hidden="true">*</span>
            </label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('status')])
                id="status" name="status" required>
                @foreach (\App\Models\Resume::statuses() as $status)
                    <option value="{{ $status }}"
                        @selected(old('status', $resume->status ?? \App\Models\Resume::STATUS_DRAFT) === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="summary">Professional summary</label>
            <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('summary')])
                id="summary" name="summary" maxlength="2000"
                placeholder="A few lines on experience, strengths and track record.">{{ old('summary', $resume->summary) }}</textarea>
            @error('summary')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Work history ------------------------------------------------------ --}}
    <div class="kh-bo__repeater" data-bo-repeater="work_history">
        <div class="kh-bo__section-label">
            Work history
            <span>Most recent role first. Leave the end month empty for a current role.</span>
        </div>

        <div data-bo-rows>
            @foreach ($rowsFor('work_history') as $index => $row)
                <fieldset class="kh-bo__repeat-row" data-bo-row>
                    <legend class="kh-bo__repeat-title">Role <span data-bo-number>{{ $index + 1 }}</span></legend>
                    <button class="kh-bo__repeat-remove" type="button" data-bo-remove
                        aria-label="Remove this role">&times;</button>

                    <div class="kh-bo__grid">
                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Job title</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="work_history[{{ $index }}][role]"
                                value="{{ $row['role'] ?? '' }}" placeholder="e.g. Builder">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Employer</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="work_history[{{ $index }}][company]"
                                value="{{ $row['company'] ?? '' }}" placeholder="e.g. GreenBuild Constructors">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Location</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="work_history[{{ $index }}][location]"
                                value="{{ $row['location'] ?? '' }}" placeholder="e.g. Hillcrest, NY">
                        </div>

                        <div class="kh-bo__field">
                            <span class="kh-bo__label">Dates</span>
                            <div class="kh-bo__range">
                                <input class="kh-bo__control" type="month"
                                    name="work_history[{{ $index }}][started_on]"
                                    value="{{ $row['started_on'] ?? '' }}" aria-label="Started">
                                <span aria-hidden="true">–</span>
                                <input class="kh-bo__control" type="month"
                                    name="work_history[{{ $index }}][ended_on]"
                                    value="{{ $row['ended_on'] ?? '' }}" aria-label="Ended">
                            </div>
                        </div>

                        <div class="kh-bo__field kh-bo__field--wide">
                            <label class="kh-bo__label">Achievements</label>
                            <textarea class="kh-bo__control" name="work_history[{{ $index }}][bullets]"
                                placeholder="One per line.">{{ is_array($row['bullets'] ?? null) ? implode("\n", $row['bullets']) : ($row['bullets'] ?? '') }}</textarea>
                            <span class="kh-bo__hint">One bullet per line.</span>
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-add>Add another role</button>
    </div>

    {{-- Skills ------------------------------------------------------------ --}}
    <div class="kh-bo__grid">
        <div class="kh-bo__section-label">
            Skills
            <span>Printed as the two-column list.</span>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="skills">Skills</label>
            <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('skills')])
                id="skills" name="skills" maxlength="2000"
                placeholder="One per line.">{{ $skillsValue }}</textarea>
            <span class="kh-bo__hint">One skill per line.</span>
            @error('skills')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Certifications ----------------------------------------------------- --}}
    <div class="kh-bo__repeater" data-bo-repeater="certifications">
        <div class="kh-bo__section-label">
            Certifications
            <span>The awarding body prints after the certificate name.</span>
        </div>

        <div data-bo-rows>
            @foreach ($rowsFor('certifications') as $index => $row)
                <fieldset class="kh-bo__repeat-row" data-bo-row>
                    <legend class="kh-bo__repeat-title">Certificate <span data-bo-number>{{ $index + 1 }}</span></legend>
                    <button class="kh-bo__repeat-remove" type="button" data-bo-remove
                        aria-label="Remove this certificate">&times;</button>

                    <div class="kh-bo__grid">
                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Certificate</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="certifications[{{ $index }}][name]"
                                value="{{ $row['name'] ?? '' }}" placeholder="e.g. Certified Construction Manager">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Issued by</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="certifications[{{ $index }}][issuer]"
                                value="{{ $row['issuer'] ?? '' }}" placeholder="e.g. Construction Management Association">
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-add>Add another certificate</button>
    </div>

    {{-- Education --------------------------------------------------------- --}}
    <div class="kh-bo__repeater" data-bo-repeater="education">
        <div class="kh-bo__section-label">
            Education
            <span>Highest qualification first.</span>
        </div>

        <div data-bo-rows>
            @foreach ($rowsFor('education') as $index => $row)
                <fieldset class="kh-bo__repeat-row" data-bo-row>
                    <legend class="kh-bo__repeat-title">Qualification <span data-bo-number>{{ $index + 1 }}</span></legend>
                    <button class="kh-bo__repeat-remove" type="button" data-bo-remove
                        aria-label="Remove this qualification">&times;</button>

                    <div class="kh-bo__grid">
                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Qualification</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="education[{{ $index }}][degree]"
                                value="{{ $row['degree'] ?? '' }}" placeholder="e.g. Master of Architecture">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Field of study</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="education[{{ $index }}][field]"
                                value="{{ $row['field'] ?? '' }}" placeholder="e.g. Construction Management">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Institution</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="education[{{ $index }}][institution]"
                                value="{{ $row['institution'] ?? '' }}" placeholder="e.g. Illinois Institute of Technology">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Location</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="education[{{ $index }}][location]"
                                value="{{ $row['location'] ?? '' }}" placeholder="e.g. Chicago, Illinois">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Graduated</label>
                            <input class="kh-bo__control" type="month"
                                name="education[{{ $index }}][graduated_on]"
                                value="{{ $row['graduated_on'] ?? '' }}">
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-add>Add another qualification</button>
    </div>

    {{-- Languages --------------------------------------------------------- --}}
    <div class="kh-bo__repeater" data-bo-repeater="languages">
        <div class="kh-bo__section-label">
            Languages
            <span>Each one prints with its proficiency bar.</span>
        </div>

        <div data-bo-rows>
            @foreach ($rowsFor('languages') as $index => $row)
                <fieldset class="kh-bo__repeat-row" data-bo-row>
                    <legend class="kh-bo__repeat-title">Language <span data-bo-number>{{ $index + 1 }}</span></legend>
                    <button class="kh-bo__repeat-remove" type="button" data-bo-remove
                        aria-label="Remove this language">&times;</button>

                    <div class="kh-bo__grid">
                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Language</label>
                            <input class="kh-bo__control" type="text" maxlength="255"
                                name="languages[{{ $index }}][name]"
                                value="{{ $row['name'] ?? '' }}" placeholder="e.g. Spanish">
                        </div>

                        <div class="kh-bo__field">
                            <label class="kh-bo__label">Proficiency</label>
                            <select class="kh-bo__control" name="languages[{{ $index }}][level]">
                                <option value="">Choose a level…</option>
                                @foreach (\App\Models\Resume::languageLevels() as $level)
                                    <option value="{{ $level }}" @selected(($row['level'] ?? '') === $level)>{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-add>Add another language</button>
    </div>

    <div class="kh-bo__form-actions">
        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.index') }}">Cancel</a>
        <button class="kh-bo__btn" type="submit">{{ $isEdit ? 'Save changes' : 'Register resume' }}</button>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/backoffice-repeater.js') }}?v={{ filemtime(public_path('js/backoffice-repeater.js')) }}" defer></script>
@endpush
