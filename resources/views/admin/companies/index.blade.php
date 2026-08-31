@extends('layouts.admin')

@section('title', 'Companies | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $companies->count();
        $filters = ['' => __('ui.bo.all'), 'approved' => __('ui.bo.status.approved'), 'pending' => __('ui.bo.status.pending'), 'rejected' => __('ui.bo.status.rejected')];
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.companies.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.companies.title') }}</h1>
                <p>{{ __('ui.bo.companies.subtitle') }}</p>
            </div>

            @if (auth()->user()?->isAdmin())
                <a class="kh-bo__btn" href="{{ route('companies.create') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    {{ __('ui.bo.companies.add') }}
                </a>
            @endif
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

        <section class="kh-bo__tiles" aria-label="{{ __('ui.bo.companies.summary_label') }}">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18" /><path d="M5 21V7l7-4 7 4v14" /><path d="M9 9h2M9 13h2M9 17h2M13 9h2M13 13h2M13 17h2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>{{ __('ui.bo.companies.tile_companies') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('approved', 0)) }}</strong><span>{{ __('ui.bo.companies.tile_approved') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('pending', 0)) }}</strong><span>{{ __('ui.bo.companies.tile_awaiting') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('rejected', 0)) }}</strong><span>{{ __('ui.bo.companies.tile_rejected') }}</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>{{ __('ui.bo.companies.directory') }}</h2>
                    <p>{{ trans_choice('ui.bo.companies.companies_shown', $total, ['count' => $total]) }}</p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('companies') }}" role="search">
                        @include('partials.kh-bo-filter-select', [
                            'name' => 'status',
                            'options' => $filters,
                            'active' => $activeStatus,
                            'label' => __('ui.bo.companies.filter_status'),
                            'allLabel' => __('ui.bo.companies.all_statuses'),
                        ])

                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ __('ui.bo.companies.search_placeholder') }}" aria-label="{{ __('ui.bo.companies.search_aria') }}">
                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">{{ __('ui.bo.search') }}</button>
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.bo.companies.col_company') }}</th>
                            <th scope="col">{{ __('ui.bo.companies.col_contact') }}</th>
                            <th scope="col">{{ __('ui.bo.companies.col_status') }}</th>
                            <th scope="col">{{ __('ui.bo.companies.col_compliance') }}</th>
                            <th scope="col">{{ __('ui.bo.companies.col_job_posts') }}</th>
                            <th scope="col"><span class="visually-hidden">{{ __('ui.bo.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            @php
                                $verified = $company->complianceRecords
                                    ->where('status', \App\Models\Compliance::STATUS_VERIFIED)->count();
                            @endphp
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">
                                            @if ($company->logoUrl())
                                                <img src="{{ $company->logoUrl() }}" alt="">
                                            @else
                                                {{ $company->initials() }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">
                                                <a class="kh-bo__name-link" href="{{ route('companies.show', $company) }}">{{ $company->name }}</a>
                                                @if ($verified > 0)
                                                    <x-verified-badge :show-label="false" :size="16" />
                                                @endif
                                            </span>
                                            <span class="kh-bo__ref">{{ $company->registration_no ?: __('ui.bo.billing.no_registration') }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $company->email ?: '—' }}
                                    <span class="kh-bo__ref">{{ $company->phone ?: 'No phone' }}</span>
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $company->status === 'approved' ? 'verified' : ($company->status === 'pending' ? 'pending' : 'rejected') }}">
                                        {{ __('ui.bo.status.' . $company->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($company->compliance_records_count === 0)
                                        <span class="kh-bo__expiry-flag kh-bo__expiry-flag--past">{{ __('ui.bo.companies.none_on_file') }}</span>
                                    @else
                                        {{ __('ui.bo.companies.verified_count', ['verified' => $verified, 'total' => $company->compliance_records_count]) }}
                                        <span class="kh-bo__ref">
                                            <a href="{{ route('compliance.index', ['q' => $company->name]) }}">{{ __('ui.bo.companies.view_records') }}</a>
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $company->job_posts_count }}
                                    <span class="kh-bo__ref">
                                        <a href="{{ route('job-posts.index', ['q' => $company->name]) }}">{{ __('ui.bo.companies.view_posts') }}</a>
                                    </span>
                                </td>

                                <td>
                                    {{-- An employer sees the whole directory but may only act on
                                         the company they belong to; admin acts on any of them. --}}
                                    @php($canEdit = auth()->user()?->isAdmin() || $company->id === $ownCompanyId)
                                    @if ($canEdit)
                                        <div class="kh-bo__actions">
                                            <a class="kh-bo__action" href="{{ route('companies.edit', $company) }}"
                                                title="{{ auth()->user()?->isAdmin() ? 'Edit company' : 'Edit your company' }}"
                                                aria-label="{{ __('ui.bo.companies.edit_aria', ['name' => $company->name]) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                                </svg>
                                            </a>

                                            @if (auth()->user()?->isAdmin())
                                            <form method="POST" action="{{ route('companies.destroy', $company) }}"
                                                onsubmit="return confirm('{{ addslashes(__('ui.bo.delete_confirm', ['name' => $company->name])) }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                    title="{{ __('ui.bo.companies.delete_company') }}" aria-label="{{ __('ui.bo.companies.delete_aria', ['name' => $company->name]) }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                                    </svg>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="kh-bo__empty">
                                        <strong>{{ __('ui.bo.companies.empty_title') }}</strong>
                                        <span>
                                            @if ($activeStatus || $searchTerm)
                                                {{ __('ui.bo.companies.empty_filtered') }}
                                                <a href="{{ route('companies') }}">{{ __('ui.bo.clear_it') }}</a> {{ __('ui.bo.to_see_everything') }}
                                            @else
                                                {{ __('ui.bo.companies.empty_none') }}
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
