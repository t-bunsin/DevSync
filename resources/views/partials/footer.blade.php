<footer class="jf-footer" id="companies">
    <div class="jf-shell jf-footer__inner">
        <div>
            <div class="jf-footer__brand">KH-WORKS</div>
            <p class="jf-footer__copy">{{ __('ui.footer.copy') }}</p>
        </div>

        <div class="jf-footer__links">
            <a href="{{ url('/') }}#jobs">{{ __('ui.footer.browse_jobs') }}</a>
            <a href="{{ route('about') }}">{{ __('ui.nav.about') }}</a>
            <a href="{{ route('login') }}">{{ __('ui.actions.sign_in') }}</a>
        </div>
    </div>
</footer>
