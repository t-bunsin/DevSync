<footer class="jf-footer">
    <div class="jf-shell jf-footer__inner">
        <div class="jf-footer__about">
            <a class="jf-footer__brand" href="{{ url('/') }}">KH-<span>WORKS</span></a>
            <p class="jf-footer__copy">{{ __('ui.footer.copy') }}</p>
        </div>

        <div class="jf-footer__links">
            <div>
                <strong>Explore</strong>
                <a href="{{ url('/') }}#jobs">{{ __('ui.footer.browse_jobs') }}</a>
                <a href="{{ url('/') }}#companies">{{ __('ui.nav.companies') }}</a>
            </div>
            <div>
                <strong>KH-WORKS</strong>
                <a href="{{ route('about') }}">{{ __('ui.nav.about') }}</a>
                <a href="{{ route('login') }}">{{ __('ui.actions.sign_in') }}</a>
            </div>
        </div>
    </div>
    <div class="jf-shell jf-footer__bottom">
        <span>&copy; {{ date('Y') }} KH-WORKS. Built for Cambodia’s growing workforce.</span>
        <span>Phnom Penh · Cambodia</span>
    </div>
</footer>
