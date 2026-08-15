@props([
    'label' => 'Verified',
    'showLabel' => true,
    'size' => 18,
])

{{--
    The blue verification badge. Inline SVG rather than an icon-font glyph so
    the blue is fixed regardless of the surrounding text colour, and so it
    still renders on the admin pages that do not load Feather.
--}}
<span {{ $attributes->merge(['class' => 'kh-verified']) }}>
    <svg class="kh-verified__mark" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
        role="img" aria-label="{{ $showLabel ? '' : $label }}"
        @if ($showLabel) aria-hidden="true" focusable="false" @endif>
        <path fill="#1d9bf0"
            d="M12 1.5l2.42 1.77 2.99-.05.9 2.86 2.44 1.73-1.03 2.81 1.03 2.81-2.44 1.73-.9 2.86-2.99-.05L12 22.5l-2.42-1.77-2.99.05-.9-2.86-2.44-1.73L4.28 12.6 3.25 9.79l2.44-1.73.9-2.86 2.99.05L12 1.5z" />
        <path fill="none" stroke="#ffffff" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"
            d="M8.2 12.3l2.6 2.6 5-5.2" />
    </svg>

    @if ($showLabel)
        <span class="kh-verified__text">{{ $label }}</span>
    @endif
</span>
