@php($chatUrl = config('site.chat_url'))

@if ($chatUrl)
    <a class="jf-chat" href="{{ $chatUrl }}" target="_blank" rel="noopener noreferrer"
        aria-label="{{ __('ui.chat.aria') }}" title="{{ __('ui.chat.label') }}">
        <span class="jf-chat__icon" aria-hidden="true">
            <i class="fab fa-telegram-plane"></i>
        </span>
        <span class="jf-chat__label">{{ __('ui.chat.label') }}</span>
    </a>
@endif
