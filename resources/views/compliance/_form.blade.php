{{-- Shared by create and edit. $compliance is an empty model on create. --}}
@php
    $isEdit = $compliance->exists;
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
        <div class="kh-bo__field kh-bo__field--wide">
            <span class="kh-bo__label">{{ __('ui.bo.compliance.form.logo') }}</span>
            <div class="kh-bo__logo-field">
                <span class="kh-bo__logo-preview" aria-hidden="true">
                    @if ($compliance->logoUrl())
                        <img src="{{ $compliance->logoUrl() }}" alt="">
                    @else
                        {{ $isEdit ? $compliance->initials() : '—' }}
                    @endif
                </span>
                <div class="kh-bo__field" style="flex: 1;">
                    <input @class(['kh-bo__control', 'is-invalid' => $errors->has('logo')])
                        id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp">
                    <span class="kh-bo__hint">{{ __('ui.bo.compliance.form.logo_hint') }}</span>

                    @if ($isEdit && $compliance->logo)
                        <label class="kh-bo__checkbox">
                            <input type="checkbox" name="remove_logo" value="1">
                            {{ __('ui.bo.compliance.form.logo_remove') }}
                        </label>
                    @endif

                    @error('logo')
                        <span class="kh-bo__error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="company_id">
                {{ __('ui.bo.compliance.form.company') }} <span class="kh-bo__required" aria-hidden="true">*</span>
            </label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('company_id')])
                id="company_id" name="company_id" required>
                <option value="">{{ __('ui.bo.compliance.form.company_choose') }}</option>
                @foreach ($companies as $option)
                    <option value="{{ $option->id }}" @selected((int) old('company_id', $compliance->company_id) === $option->id)>
                        {{ $option->name }}
                    </option>
                @endforeach
            </select>
            <span class="kh-bo__hint">
                {{ __('ui.bo.compliance.form.company_hint') }}
                <a href="{{ route('companies.create') }}">{{ __('ui.bo.compliance.form.company_hint_link') }}</a> {{ __('ui.bo.compliance.form.company_hint_rest') }}
            </span>
            @error('company_id')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="category">
                {{ __('ui.bo.compliance.form.category') }} <span class="kh-bo__required" aria-hidden="true">*</span>
            </label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('category')])
                id="category" name="category" required>
                @foreach (\App\Models\Compliance::categories() as $category)
                    <option value="{{ $category }}" @selected(old('category', $compliance->category) === $category)>
                        {{ __('ui.bo.options.compliance_category.' . $category) }}
                    </option>
                @endforeach
            </select>
            @error('category')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="reference">{{ __('ui.bo.compliance.form.reference') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('reference')])
                id="reference" name="reference" type="text" maxlength="120"
                value="{{ old('reference', $compliance->reference) }}" placeholder="e.g. KH-BL-2026-0148">
            @error('reference')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="status">
                {{ __('ui.bo.compliance.form.status') }} <span class="kh-bo__required" aria-hidden="true">*</span>
            </label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('status')])
                id="status" name="status" required>
                @foreach (\App\Models\Compliance::statuses() as $status)
                    <option value="{{ $status }}"
                        @selected(old('status', $compliance->status ?? \App\Models\Compliance::STATUS_PENDING) === $status)>
                        {{ __('ui.bo.status.' . $status) }}
                    </option>
                @endforeach
            </select>
            <span class="kh-bo__hint">{{ __('ui.bo.compliance.form.status_hint') }}</span>
            @error('status')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="issued_on">{{ __('ui.bo.compliance.form.issued_on') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('issued_on')])
                id="issued_on" name="issued_on" type="date"
                value="{{ old('issued_on', $compliance->issued_on?->format('Y-m-d')) }}">
            @error('issued_on')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="expires_on">{{ __('ui.bo.compliance.form.expires_on') }}</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('expires_on')])
                id="expires_on" name="expires_on" type="date"
                value="{{ old('expires_on', $compliance->expires_on?->format('Y-m-d')) }}">
            @error('expires_on')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="notes">{{ __('ui.bo.compliance.form.notes') }}</label>
            <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('notes')])
                id="notes" name="notes" maxlength="2000"
                placeholder="{{ __('ui.bo.compliance.form.notes_placeholder') }}">{{ old('notes', $compliance->notes) }}</textarea>
            @error('notes')
                <span class="kh-bo__error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="kh-bo__form-actions">
        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('compliance.index') }}">{{ __('ui.bo.job_posts.form.cancel') }}</a>
        <button class="kh-bo__btn" type="submit">{{ $isEdit ? __('ui.bo.save') : __('ui.bo.billing.create_record') }}</button>
    </div>
</div>
