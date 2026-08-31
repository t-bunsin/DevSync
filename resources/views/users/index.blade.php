@extends('layouts.admin')

@section('title', 'Users | ZIN-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $shown = $users->count();
        $isFiltered = $activeRole || $searchTerm || $fromDate || $toDate;

        // Same three badge tones the rest of the back office uses.
        $tones = [
            'active' => 'verified',
            'pending' => 'pending',
            'suspended' => 'rejected',
            'banned' => 'rejected',
        ];

        // Only four tile icon variants exist, so each role keeps a fixed one.
        $roleIcons = ['admin' => 'kh-bo__tile-icon--danger', 'employer' => 'kh-bo__tile-icon--blue'];
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.users.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.users.title') }}</h1>
                <p>{{ __('ui.bo.users.subtitle') }}</p>
            </div>

            <a class="kh-bo__btn" href="{{ route('user.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                {{ __('ui.bo.users.add') }}
            </a>
        </header>

        @if (session('success'))
            <div class="kh-bo__flash" role="status">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="kh-bo__errors" role="alert">
                <strong>{{ session('error') }}</strong>
            </div>
        @endif

        <section class="kh-bo__tiles" aria-label="{{ __('ui.bo.users.summary_label') }}">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 00-3-3.87" />
                    </svg>
                </span>
                <div><strong>{{ number_format($total) }}</strong><span>{{ __('ui.bo.users.accounts') }}</span></div>
            </article>

            {{-- One tile per role this viewer may see; admins are absent from
                 $roles for everyone but an admin. --}}
            @foreach ($roles as $role)
                <article class="kh-bo__tile">
                    <span class="kh-bo__tile-icon {{ $roleIcons[$role->code] ?? 'kh-bo__tile-icon--amber' }}" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            @if ($role->code === 'admin')
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            @elseif ($role->code === 'employer')
                                <rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                            @else
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /><circle cx="12" cy="7" r="4" />
                            @endif
                        </svg>
                    </span>
                    <div>
                        <strong>{{ number_format($counts->get($role->code, 0)) }}</strong>
                        <span>{{ \Illuminate\Support\Str::plural($role->name_en) }}</span>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>{{ __('ui.bo.users.directory') }}</h2>
                    <p>
                        {{ trans_choice('ui.bo.users.accounts_shown', $shown, ['count' => $shown]) }}
                        @if ($fromDate && $toDate)
                            {{ __('ui.bo.users.joined_between', ['from' => $fromDate, 'to' => $toDate]) }}
                        @elseif ($fromDate)
                            {{ __('ui.bo.users.joined_from', ['from' => $fromDate]) }}
                        @elseif ($toDate)
                            {{ __('ui.bo.users.joined_to', ['to' => $toDate]) }}
                        @endif
                    </p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('users') }}" role="search">
                        @include('partials.kh-bo-filter-select', [
                            'name' => 'role',
                            'options' => ['' => __('ui.bo.all')] + $roles->pluck('name_en', 'code')->all(),
                            'active' => $activeRole,
                            'label' => __('ui.bo.users.filter_role'),
                            'allLabel' => __('ui.bo.users.all_roles'),
                        ])

                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ __('ui.bo.users.search_placeholder') }}" aria-label="{{ __('ui.bo.users.search_aria') }}">

                        <div class="kh-bo__range">
                            <input type="date" name="from" value="{{ $fromDate }}"
                                aria-label="{{ __('ui.bo.users.from_date') }}" title="{{ __('ui.bo.users.from_date') }}">
                            <span aria-hidden="true">–</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                aria-label="{{ __('ui.bo.users.to_date') }}" title="{{ __('ui.bo.users.to_date') }}">
                        </div>

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">{{ __('ui.bo.search') }}</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('users') }}">{{ __('ui.bo.clear') }}</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.bo.users.col_user') }}</th>
                            <th scope="col">{{ __('ui.bo.users.col_email') }}</th>
                            <th scope="col">{{ __('ui.bo.users.col_primary_role') }}</th>
                            <th scope="col">{{ __('ui.bo.users.col_all_roles') }}</th>
                            <th scope="col">{{ __('ui.bo.users.col_status') }}</th>
                            <th scope="col">{{ __('ui.bo.users.col_joined') }}</th>
                            <th scope="col"><span class="visually-hidden">{{ __('ui.bo.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $displayName = $user->displayName();
                                $primaryRole = $user->primaryRole();
                            @endphp
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">
                                            @if ($user->avatarUrl())
                                                <img src="{{ $user->avatarUrl() }}" alt="">
                                            @else
                                                {{ $user->initials() }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">
                                                <a class="kh-bo__name-link" href="{{ route('user.edit', $user) }}">{{ $displayName }}</a>
                                            </span>
                                            <span class="kh-bo__ref" title="{{ $user->id }}">
                                                {{ \Illuminate\Support\Str::before($user->id, '-') }}
                                                @if ($user->id === auth()->id())
                                                    · {{ __('ui.bo.you') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $user->email ?: '—' }}
                                    @if ($user->phone)
                                        <span class="kh-bo__ref">{{ $user->phone }}</span>
                                    @endif
                                </td>

                                <td>{{ $primaryRole?->name_en ?? '—' }}</td>

                                <td>
                                    <div class="kh-bo__chips">
                                        @forelse ($user->roles->sortBy('sort_order') as $role)
                                            {{-- Amber marks the elevated role; red is reserved for bad states. --}}
                                            <span @class([
                                                'kh-bo__status',
                                                'kh-bo__status--pending' => $role->code === 'admin',
                                                'kh-bo__status--verified' => $role->code === 'employer',
                                            ])>{{ $role->name_en }}</span>
                                        @empty
                                            <span class="kh-bo__ref">{{ __('ui.bo.users.no_roles') }}</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $tones[$user->status] ?? 'pending' }}">
                                        {{ __('ui.bo.status.' . $user->status) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $user->created_at?->format('M j, Y') ?? '—' }}
                                    <span class="kh-bo__ref">{{ $user->created_at?->format('h:i A') }}</span>
                                </td>

                                <td>
                                    <div class="kh-bo__actions">
                                        <a class="kh-bo__action" href="{{ route('user.edit', $user) }}"
                                            title="{{ __('ui.bo.users.edit_user') }}" aria-label="{{ __('ui.bo.users.edit_aria', ['name' => $displayName]) }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                            </svg>
                                        </a>

                                        @if ($user->id === auth()->id())
                                            {{-- destroy() refuses this too; the button just says so first. --}}
                                            <button class="kh-bo__action" type="button" disabled
                                                title="{{ __('ui.bo.users.cannot_delete_self') }}"
                                                aria-label="{{ __('ui.bo.users.cannot_delete_self_aria') }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('user.destroy', $user) }}"
                                                onsubmit="return confirm('{{ addslashes(__('ui.bo.delete_confirm', ['name' => $displayName])) }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                    title="{{ __('ui.bo.users.delete_user') }}" aria-label="{{ __('ui.bo.users.delete_aria', ['name' => $displayName]) }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="kh-bo__empty">
                                        <strong>{{ __('ui.bo.users.empty_title') }}</strong>
                                        <span>
                                            @if ($isFiltered)
                                                {{ __('ui.bo.users.empty_filtered') }}
                                                <a href="{{ route('users') }}">{{ __('ui.bo.clear_it') }}</a> {{ __('ui.bo.to_see_everything') }}
                                            @else
                                                {{ __('ui.bo.users.empty_none') }}
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.kh-bo-pagination', ['paginator' => $users])
        </section>
    </div>
@endsection
