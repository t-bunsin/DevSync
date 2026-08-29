@extends('layouts.admin')

@section('title', $label . ' | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
@php
    // Singular/plural forms for this component type, so the sentences below
    // stay one translatable unit instead of "Add " . strtolower($label).
    $item = __('ui.bo.components.types.' . $type . '.one');
    $items = __('ui.bo.components.types.' . $type . '.many');
    $typeTitle = __('ui.bo.components.types.' . $type . '.title');
@endphp
    @php($plural = \Illuminate\Support\Str::plural($label))

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span>{{ __('ui.bo.components.breadcrumb') }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $typeTitle }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ $typeTitle }}</h1>
                <p>{{ __('ui.bo.components.subtitle', ['item' => $item]) }}</p>
            </div>

            <a class="kh-bo__btn" href="{{ route('components.create', $type) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                {{ __('ui.bo.components.add', ['item' => $item]) }}
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

        @if ($errors->any())
            <div class="kh-bo__errors" role="alert">
                @foreach ($errors->all() as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endif

        <section class="kh-bo__tiles" aria-label="{{ $plural }} summary">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18" /><path d="M5 21V7l7-4 7 4v14" /><path d="M9 9h2M9 13h2M9 17h2M13 9h2M13 13h2M13 17h2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($total) }}</strong><span>{{ $plural }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
                <div><strong>{{ number_format($activeCount) }}</strong><span>{{ __('ui.bo.components.active') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($total - $activeCount) }}</strong><span>{{ __('ui.bo.components.inactive') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 007.07 0l2.83-2.83a5 5 0 00-7.07-7.07l-1.5 1.5" /><path d="M14 11a5 5 0 00-7.07 0L4.1 13.83a5 5 0 007.07 7.07l1.5-1.5" />
                    </svg>
                </span>
                <div><strong>{{ number_format($jobPostsTotal) }}</strong><span>{{ __('ui.bo.components.job_posts') }}</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>{{ $plural }}</h2>
                    <p>{{ $records->count() }} {{ \Illuminate\Support\Str::plural('entry', $records->count()) }} shown.</p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('components.index', $type) }}" role="search">
                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ __('ui.bo.components.search', ['items' => $items]) }}" aria-label="{{ __('ui.bo.components.search', ['items' => $items]) }}">
                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">{{ __('ui.bo.search') }}</button>
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.bo.components.col_name') }}</th>
                            <th scope="col">{{ __('ui.bo.components.col_status') }}</th>
                            <th scope="col">{{ __('ui.bo.components.col_job_posts') }}</th>
                            <th scope="col">{{ __('ui.bo.components.col_registered') }}</th>
                            <th scope="col">{{ __('ui.bo.components.col_registered_by') }}</th>
                            <th scope="col"><span class="visually-hidden">{{ __('ui.bo.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>{{ $record->name }}</td>
                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $record->is_active ? 'verified' : 'rejected' }}">
                                        {{ $record->is_active ? __('ui.bo.components.active') : __('ui.bo.components.inactive') }}
                                    </span>
                                </td>
                                <td>{{ $record->usage_count }}</td>
                                <td>{{ $record->created_at?->format('d M Y') ?? '—' }}</td>
                                <td>{{ $record->registeredByLabel() }}</td>
                                <td>
                                    <div class="kh-bo__actions">
                                        <a class="kh-bo__action" href="{{ route('components.edit', [$type, $record]) }}"
                                            title="{{ __('ui.bo.components.edit_title', ['item' => $item]) }}" aria-label="{{ __('ui.bo.components.edit_aria', ['name' => $record->name]) }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('components.destroy', [$type, $record]) }}"
                                            onsubmit="return confirm('{{ addslashes(__('ui.bo.delete_confirm', ['name' => $record->name])) }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                title="{{ __('ui.bo.components.delete_title', ['item' => $item]) }}" aria-label="{{ __('ui.bo.components.delete_aria', ['name' => $record->name]) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="kh-bo__empty">
                                        <strong>{{ __('ui.bo.components.empty_title', ['items' => $items]) }}</strong>
                                        <span>
                                            @if ($searchTerm)
                                                {{ __('ui.bo.components.empty_filtered') }}
                                                <a href="{{ route('components.index', $type) }}">{{ __('ui.bo.clear_it') }}</a> {{ __('ui.bo.to_see_everything') }}
                                            @else
                                                {{ __('ui.bo.components.empty_none') }}
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
