{{-- Shared by create and edit. $record is an empty model on create. --}}
@php($isEdit = $record->exists)

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
            <label class="kh-bo__label" for="name">Name <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('name')])
                id="name" name="name" type="text" maxlength="120" required autofocus
                value="{{ old('name', $record->name) }}">
            <span class="kh-bo__hint">Shown as-is in the job post form's {{ strtolower($label) }} field.</span>
            @error('name') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__checkbox">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $record->is_active ?? true))>
                Active (selectable on the job post form)
            </label>
        </div>
    </div>

    <div class="kh-bo__form-actions">
        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('components.index', $type) }}">Cancel</a>
        <button class="kh-bo__btn" type="submit">{{ $isEdit ? 'Save changes' : "Add {$label}" }}</button>
    </div>
</div>
