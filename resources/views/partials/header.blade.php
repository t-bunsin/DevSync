<header class="jf-header">
    <div class="jf-shell jf-header__inner">
        @php($currentLocale = app()->getLocale())
        <a class="jf-brand" href="{{ url('/') }}">
            <span class="jf-brand__mark">
                <span class="jf-brand__mark-main">
                    <i class="fas fa-briefcase" aria-hidden="true"></i>
                </span>
            </span>
            <span class="jf-brand__divider" aria-hidden="true"></span>
            <span class="jf-brand__text">
                <span class="jf-brand__word">ZIN-<span>WORKS</span></span>
                <span class="jf-brand__tagline">Build your dream job</span>
            </span>
        </a>

        <div class="jf-header__menu" id="jf-header-menu">
            <nav class="jf-nav" aria-label="Main navigation">
                <a class="{{ request()->is('/') ? 'is-active' : '' }}" href="{{ url('/') }}">
                    <i class="fas fa-house" aria-hidden="true"></i>
                    <span>{{ __('ui.nav.home') }}</span>
                </a>
                <a class="{{ request()->routeIs('jobs.*') ? 'is-active' : '' }}" href="{{ route('jobs.index') }}">
                    <i class="fas fa-briefcase" aria-hidden="true"></i>
                    <span>{{ __('ui.nav.jobs') }}</span>
                </a>
                <a class="{{ request()->routeIs('companies.index') || request()->routeIs('companies.profile') ? 'is-active' : '' }}"
                    href="{{ route('companies.index') }}">
                    <i class="fas fa-building" aria-hidden="true"></i>
                    <span>{{ __('ui.nav.companies') }}</span>
                </a>
                <a class="{{ request()->routeIs('about') ? 'is-active' : '' }}" href="{{ route('about') }}">
                    <i class="fas fa-circle-info" aria-hidden="true"></i>
                    <span>{{ __('ui.nav.about') }}</span>
                </a>
                <a class="{{ request()->routeIs('contact') ? 'is-active' : '' }}" href="{{ route('contact') }}">
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                    <span>{{ __('ui.nav.contact') }}</span>
                </a>
            </nav>

            <div class="jf-header__actions">
                {{-- The bar shows the locale's code; the menu spells both out. --}}
                <div class="jf-language">
                    <button class="jf-language__toggle" type="button" aria-label="{{ __('ui.language.switch') }}">
                        <i class="fas fa-globe"></i>
                        <span>{{ strtoupper($currentLocale) }}</span>
                        <i class="fas fa-angle-down"></i>
                    </button>

                    <div class="jf-language__menu">
                        <a class="jf-language__option{{ $currentLocale === 'en' ? ' is-active' : '' }}" href="{{ route('language.switch', 'en') }}">
                            <span>{{ __('ui.language.english') }}</span>
                            <small>EN</small>
                        </a>
                        <a class="jf-language__option{{ $currentLocale === 'kh' ? ' is-active' : '' }}" href="{{ route('language.switch', 'kh') }}">
                            <span>{{ __('ui.language.khmer') }}</span>
                            <small>KH</small>
                        </a>
                    </div>
                </div>

                @auth
                    <a class="jf-icon-btn" href="{{ route('home') }}" aria-label="{{ __('ui.actions.dashboard') }}"
                        title="{{ __('ui.actions.dashboard') }}">
                        <i class="fas fa-chart-line" aria-hidden="true"></i>
                        <span class="jf-icon-btn__label">{{ __('ui.actions.dashboard') }}</span>
                    </a>

                    <span class="jf-header__divider" aria-hidden="true"></span>

                    <a class="jf-profile" href="{{ route('profile') }}">
                        <span class="jf-profile__avatar" aria-hidden="true"><i class="fas fa-user"></i></span>
                        <span>{{ __('ui.actions.profile') }}</span>
                    </a>
                @else
                    <a class="jf-btn jf-btn--ghost" href="{{ route('login') }}">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        <span>{{ __('ui.actions.sign_in') }}</span>
                    </a>

                    <span class="jf-header__divider" aria-hidden="true"></span>

                    <a class="jf-profile" href="{{ route('register') }}">
                        <span class="jf-profile__avatar" aria-hidden="true"><i class="fas fa-plus"></i></span>
                        <span>{{ __('ui.actions.post_job') }}</span>
                    </a>
                @endauth
            </div>
        </div>

        <div class="jf-header__utilities">
            <button class="jf-theme-toggle jf-icon-btn" type="button" aria-label="Switch to dark theme" aria-pressed="false" title="Switch to dark theme">
                <i class="fas fa-sun jf-theme-toggle__sun" aria-hidden="true"></i>
                <i class="fas fa-moon jf-theme-toggle__moon" aria-hidden="true"></i>
            </button>

            <button class="jf-menu-toggle" type="button" aria-expanded="false" aria-controls="jf-header-menu">
                <span class="visually-hidden">Open navigation</span>
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</header>
