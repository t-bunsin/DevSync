{{-- Inline flag for a locale code ('en' or 'kh'). Inline SVG rather than emoji,
     since Windows renders flag emoji as two bare letters. Decorative: the
     language name or code beside it is what gets announced. --}}
@php($flagClass = trim('kh-flag ' . ($class ?? '')))
@if (($code ?? 'en') === 'kh')
    <svg class="{{ $flagClass }}" viewBox="0 0 60 40" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
        <rect width="60" height="40" fill="#032EA1" />
        <rect y="10" width="60" height="20" fill="#E00025" />
        <path fill="#fff"
            d="M17 28h26v-2H17zM19 26h22v-3H19zM21 23l1.5-5h1l1.5 5zM27 23l2-9.5h2l2 9.5zM35 23l1.5-5h1l1.5 5zM24.5 23l1-3.5h1l1 3.5zM32.5 23l1-3.5h1l1 3.5z" />
    </svg>
@else
    @php($flagClip = 'kh-flag-uk-' . uniqid())
    <svg class="{{ $flagClass }}" viewBox="0 0 60 30" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
        <clipPath id="{{ $flagClip }}">
            <path d="M30 15h30v15zv15H0zH0V0zV0h30z" />
        </clipPath>
        <rect width="60" height="30" fill="#012169" />
        <path d="M0 0l60 30m0-30L0 30" stroke="#fff" stroke-width="6" />
        <path d="M0 0l60 30m0-30L0 30" clip-path="url(#{{ $flagClip }})" stroke="#C8102E" stroke-width="4" />
        <path d="M30 0v30M0 15h60" stroke="#fff" stroke-width="10" />
        <path d="M30 0v30M0 15h60" stroke="#C8102E" stroke-width="6" />
    </svg>
@endif
