{{--
    The Activity center list. Rendered by the admin layout on page load and by
    NotificationController::feed() for the poller, so the two can never drift.
--}}
@forelse ($notifications as $notification)
    @php
        $data = $notification->data;
        $isUnread = $notification->read_at === null;
    @endphp

    <a class="dropdown-item dropdown-notifications-item{{ $isUnread ? ' is-unread' : '' }}"
        href="{{ $data['url'] ?? route('home') }}">
        <div class="dropdown-notifications-item-icon bg-success">
            <i data-feather="user-plus"></i>
        </div>
        <div class="dropdown-notifications-item-content">
            <div class="dropdown-notifications-item-content-details">
                {{ $notification->created_at?->diffForHumans() }}
                @if (($data['kind'] ?? null) === 'job-application')
                    · {{ ($data['has_cv'] ?? false) ? 'CV attached' : 'no CV' }}
                @endif
            </div>
            <div class="dropdown-notifications-item-content-text">
                {{ $data['text'] ?? 'Something happened in your workspace.' }}
            </div>
        </div>
    </a>
@empty
    <div class="dropdown-item dropdown-notifications-item kh-notify__empty">
        <div class="dropdown-notifications-item-content">
            <div class="dropdown-notifications-item-content-text">{{ __('ui.admin.notifications.empty') }}</div>
            <div class="dropdown-notifications-item-content-details">
                {{ __('ui.admin.notifications.empty_hint') }}
            </div>
        </div>
    </div>
@endforelse
