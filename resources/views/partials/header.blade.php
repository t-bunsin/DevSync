<header class="jf-header">
    <div class="jf-shell jf-header__inner">
        @php($currentLocale = app()->getLocale())
        <a class="jf-brand" href="{{ url('/') }}">
            <span class="jf-brand__mark">
                <span class="jf-brand__mark-main">
                    <i class="fas fa-briefcase"></i>
                </span>
                <span class="jf-brand__mark-badge">
                    <i class="fas fa-star"></i>
                </span>
            </span>
            <span class="jf-brand__text">KH-<span>WORKS</span></span>
        </a>

        <nav class="jf-nav" aria-label="Main navigation">
            <a href="{{ url('/') }}">{{ __('ui.nav.home') }}</a>
            <a href="{{ url('/') }}#jobs">{{ __('ui.nav.jobs') }}</a>
            <a href="{{ url('/') }}#companies">{{ __('ui.nav.companies') }}</a>
            <a href="{{ route('about') }}">{{ __('ui.nav.about') }}</a>
        </nav>

        <div class="jf-header__actions">
            <div class="jf-language">
                <button class="jf-language__toggle" type="button" aria-label="{{ __('ui.language.switch') }}">
                    <i class="fas fa-globe"></i>
                    <span>{{ $currentLocale === 'kh' ? __('ui.language.kh') : __('ui.language.en') }}</span>
                    <i class="fas fa-angle-down"></i>
                </button>

                <div class="jf-language__menu">
                    <a class="jf-language__option{{ $currentLocale === 'en' ? ' is-active' : '' }}" href="{{ route('language.switch', 'en') }}">
                        {{ __('ui.language.english') }}
                    </a>
                    <a class="jf-language__option{{ $currentLocale === 'kh' ? ' is-active' : '' }}" href="{{ route('language.switch', 'kh') }}">
                        {{ __('ui.language.khmer') }}
                    </a>
                </div>
            </div>

            @auth
                <a class="jf-btn jf-btn--ghost" href="{{ route('home') }}">
                    <i class="fas fa-chart-line"></i>
                    <span>{{ __('ui.actions.dashboard') }}</span>
                </a>
                <a class="jf-btn jf-btn--primary" href="{{ route('profile') }}">
                    <i class="fas fa-user"></i>
                    <span>{{ __('ui.actions.profile') }}</span>
                </a>
            @else
                <a class="jf-btn jf-btn--ghost" href="{{ route('login') }}">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    <span>{{ __('ui.actions.sign_in') }}</span>
                </a>
                <a class="jf-btn jf-btn--primary" href="{{ route('register') }}">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('ui.actions.post_job') }}</span>
                </a>
            @endauth
        </div>
    </div>
</header>
