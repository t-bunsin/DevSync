{{-- Shared by create and edit. $compliance is an empty model on create. --}}
@php
    $isEdit = $compliance->exists;
@endphp

@if ($errors->any())
    <div class="kh-comp__errors" role="alert">
        <strong>Please check the highlighted fields.</strong>
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="kh-comp__form-card">
    <div class="kh-comp__grid">
        <div class="kh-comp__field kh-comp__field--wide">
            <span class="kh-comp__label">Logo</span>
            <div class="kh-comp__logo-field">
                <span class="kh-comp__logo-preview" aria-hidden="true">
                    @if ($compliance->logoUrl())
                        <img src="{{ $compliance->logoUrl() }}" alt="">
                    @else
                        {{ $isEdit ? $compliance->initials() : '—' }}
                    @endif
                </span>
                <div class="kh-comp__field" style="flex: 1;">
                    <input @class(['kh-comp__control', 'is-invalid' => $errors->has('logo')])
                        id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp">
                    <span class="kh-comp__hint">PNG, JPG, SVG or WebP, up to 2 MB.</span>

                    @if ($isEdit && $compliance->logo)
                        <label class="kh-comp__checkbox">
                            <input type="checkbox" name="remove_logo" value="1">
                            Remove the current logo
                        </label>
                    @endif

                    @error('logo')
                        <span class="kh-comp__error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="kh-comp__field">
            <label class="kh-comp__label" for="name">
                Organisation <span class="kh-comp__required" aria-hidden="true">*</span>
            </label>
            <input @class(['kh-comp__control', 'is-invalid' => $errors->has('name')])
                id="name" name="name" type="text" maxlength="255" required
                value="{{ old('name', $compliance->name) }}" placeholder="e.g. ABA Bank">
            @error('name')
                <span class="kh-comp__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-comp__field">
            <label class="kh-comp__label" for="category">
                Category <span class="kh-comp__required" aria-hidden="true">*</span>
            </label>
            <select @class(['kh-comp__control', 'is-invalid' => $errors->has('category')])
                id="category" name="category" required>
                @foreach (\App\Models\Compliance::categories() as $category)
                    <option value="{{ $category }}" @selected(old('category', $compliance->category) === $category)>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
            @error('category')
                <span class="kh-comp__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-comp__field">
            <label class="kh-comp__label" for="reference">Reference number</label>
            <input @class(['kh-comp__control', 'is-invalid' => $errors->has('reference')])
                id="reference" name="reference" type="text" maxlength="120"
                value="{{ old('reference', $compliance->reference) }}" placeholder="e.g. KH-BL-2026-0148">
            @error('reference')
                <span class="kh-comp__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-comp__field">
            <label class="kh-comp__label" for="status">
                Status <span class="kh-comp__required" aria-hidden="true">*</span>
            </label>
            <select @class(['kh-comp__control', 'is-invalid' => $errors->has('status')])
                id="status" name="status" required>
                @foreach (\App\Models\Compliance::statuses() as $status)
                    <option value="{{ $status }}"
                        @selected(old('status', $compliance->status ?? \App\Models\Compliance::STATUS_PENDING) === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
            <span class="kh-comp__hint">Setting this to verified stamps your name against the record.</span>
            @error('status')
                <span class="kh-comp__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-comp__field">
            <label class="kh-comp__label" for="issued_on">Issued on</label>
            <input @class(['kh-comp__control', 'is-invalid' => $errors->has('issued_on')])
                id="issued_on" name="issued_on" type="date"
                value="{{ old('issued_on', $compliance->issued_on?->format('Y-m-d')) }}">
            @error('issued_on')
                <span class="kh-comp__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-comp__field">
            <label class="kh-comp__label" for="expires_on">Expires on</label>
            <input @class(['kh-comp__control', 'is-invalid' => $errors->has('expires_on')])
                id="expires_on" name="expires_on" type="date"
                value="{{ old('expires_on', $compliance->expires_on?->format('Y-m-d')) }}">
            @error('expires_on')
                <span class="kh-comp__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="kh-comp__field kh-comp__field--wide">
            <label class="kh-comp__label" for="notes">Notes</label>
            <textarea @class(['kh-comp__control', 'is-invalid' => $errors->has('notes')])
                id="notes" name="notes" maxlength="2000"
                placeholder="What was checked, and anything the next reviewer should know.">{{ old('notes', $compliance->notes) }}</textarea>
            @error('notes')
                <span class="kh-comp__error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="kh-comp__form-actions">
        <a class="kh-comp__btn kh-comp__btn--ghost" href="{{ route('compliance.index') }}">Cancel</a>
        <button class="kh-comp__btn" type="submit">{{ $isEdit ? 'Save changes' : 'Create record' }}</button>
    </div>
</div>
