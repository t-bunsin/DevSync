{{--
    The back office's category filter, as a field inside the search form.

    It used to be a row of link-pills that navigated on click, which meant the
    category applied on its own while the keyword and dates waited for Search.
    As a <select> the whole form submits together, and the row stays one line
    however many options a screen has.

    Required: $name, $options (value => label; '' is the "all" entry),
    $active. Optional: $label (a11y), $allLabel (what '' reads as).
--}}
@php
    $label ??= 'Filter';
    $allLabel ??= 'All';
@endphp

<select name="{{ $name }}" aria-label="{{ $label }}" title="{{ $label }}">
    @foreach ($options as $value => $optionLabel)
        <option value="{{ $value }}" @selected((string) $active === (string) $value)>
            {{ (string) $value === '' ? $allLabel : $optionLabel }}
        </option>
    @endforeach
</select>
