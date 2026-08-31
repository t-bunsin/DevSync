@php
    // Rendered only when configured, same rule as the chat button: an unset
    // handle links nowhere, so the icon is left out rather than pointing at a
    // dead page.
    $social = array_filter([
        'telegram' => ['url' => config('site.chat_url'), 'icon' => 'fab fa-telegram-plane', 'label' => 'Telegram'],
        'facebook' => ['url' => config('site.social.facebook'), 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
        'linkedin' => ['url' => config('site.social.linkedin'), 'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn'],
    ], fn (array $item) => ! empty($item['url']));
@endphp

<footer class="jf-footer">
    <div class="jf-shell jf-footer__inner">
        <div class="jf-footer__about">
            <a class="jf-footer__brand" href="{{ url('/') }}">ZIN-<span>WORKS</span></a>
            <p class="jf-footer__copy">{{ __('ui.footer.copy') }}</p>

            @if ($social)
                <div class="jf-footer__social">
                    @foreach ($social as $item)
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ __('ui.footer.follow_on', ['network' => $item['label']]) }}">
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <nav class="jf-footer__links" aria-label="{{ __('ui.footer.nav_label') }}">
            <div>
                <strong>{{ __('ui.footer.explore') }}</strong>
                <a href="{{ route('jobs.index') }}">{{ __('ui.footer.browse_jobs') }}</a>
                <a href="{{ url('/') }}#companies">{{ __('ui.nav.companies') }}</a>
                <a href="{{ url('/') }}#pricing">{{ __('ui.footer.pricing') }}</a>
            </div>

            <div>
                <strong>ZIN-WORKS</strong>
                <a href="{{ route('about') }}">{{ __('ui.nav.about') }}</a>
                <a href="{{ route('contact') }}">{{ __('ui.nav.contact') }}</a>
            </div>

            <div>
                <strong>{{ __('ui.footer.account') }}</strong>
                @auth
                    <a href="{{ route('home') }}">{{ __('ui.actions.dashboard') }}</a>
                    <a href="{{ route('profile') }}">{{ __('ui.actions.profile') }}</a>
                @else
                    <a href="{{ route('login') }}">{{ __('ui.actions.sign_in') }}</a>
                    <a href="{{ route('register') }}">{{ __('ui.actions.post_job') }}</a>
                @endauth
            </div>
        </nav>
    </div>

    <div class="jf-shell jf-footer__bottom">
        <p>&copy; {{ date('Y') }} ZIN-WORKS. {{ __('ui.footer.rights') }}</p>
        <span class="jf-footer__place">
            <i class="fas fa-location-dot" aria-hidden="true"></i>{{ __('ui.footer.place') }}
        </span>
    </div>
</footer>
