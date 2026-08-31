@extends('layouts.admin')

@section('title', $company->name . ' | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $statusTone = $company->status === 'approved' ? 'verified' : ($company->status === 'pending' ? 'pending' : 'rejected');
        $isAdmin = auth()->user()?->isAdmin();
        // The same rule the directory applies to its Edit action: an employer
        // may act on their own record only, an admin on any of them.
        $canEdit = $isAdmin || $company->id === auth()->user()?->ownCompany()?->id;
        $sections = $company->profileSections();
        // Each row carries how it is reached, so the card offers the action
        // rather than a string to copy: mailto:, tel:, and a map lookup.
        $contact = collect([
            ['key' => 'email', 'label' => __('ui.bo.company_detail.email'), 'value' => $company->email, 'href' => $company->email ? 'mailto:' . $company->email : null],
            ['key' => 'phone', 'label' => __('ui.bo.company_detail.phone'), 'value' => $company->phone, 'href' => $company->phone ? 'tel:' . preg_replace('/[^+0-9]/', '', $company->phone) : null],
            ['key' => 'address', 'label' => __('ui.bo.company_detail.address'), 'value' => $company->address, 'href' => null],
        ])->filter(fn (array $row) => filled($row['value']));
        $links = collect($company->socialLinks());
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('companies') }}">{{ __('ui.bo.companies.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $company->name }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>
                    {{ $company->name }}
                    @if ($verified > 0)
                        <x-verified-badge :show-label="false" :size="18" />
                    @endif
                </h1>
                <p>
                    {{ $company->registration_no ?: __('ui.bo.billing.no_registration') }}
                    @if ($company->industry)
                        · {{ $company->industry }}
                    @endif
                </p>
            </div>

            <div class="kh-bo__head-actions">
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('companies') }}">{{ __('ui.bo.companies.back_to_list') }}</a>

                @if ($canEdit)
                    <a class="kh-bo__btn" href="{{ route('companies.edit', $company) }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                        </svg>
                        {{ __('ui.bo.company_detail.edit_company') }}
                    </a>
                @endif
            </div>
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

        <div class="kh-bo__detail">
            <div class="kh-bo__detail-main">
                <section class="kh-bo__card">
                    @if ($company->coverUrl())
                        <img src="{{ $company->coverUrl() }}" alt=""
                            style="width: 100%; max-height: 180px; object-fit: cover; border-bottom: 1px solid var(--kh-line);">
                    @endif

                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.company_detail.profile') }}</h2>
                            <p>{{ __('ui.bo.company_detail.profile_hint') }}</p>
                        </div>

                        <div class="kh-bo__chips">
                            <span class="kh-bo__status kh-bo__status--{{ $statusTone }}">{{ __('ui.bo.status.' . $company->status) }}</span>
                        </div>
                    </div>

                    <div class="kh-bo__card-body">
                        <div class="kh-bo__identity">
                            <span class="kh-bo__logo" aria-hidden="true">
                                @if ($company->logoUrl())
                                    <img src="{{ $company->logoUrl() }}" alt="">
                                @else
                                    {{ $company->initials() }}
                                @endif
                            </span>
                            <div>
                                <span class="kh-bo__name">{{ $company->name }}</span>
                                <span class="kh-bo__ref">{{ $company->employer_type ?: __('ui.bo.companies.form.not_specified') }}</span>
                            </div>
                        </div>

                        @if (filled($company->description))
                            <p class="kh-bo__prose">{{ $company->description }}</p>
                        @else
                            <p class="kh-bo__muted">{{ __('ui.bo.company_detail.no_description') }}</p>
                        @endif

                        @if ($company->employerDetails() !== [])
                            <dl class="kh-bo__facts">
                                @foreach ($company->employerDetails() as $label => $value)
                                    <div>
                                        <dt>{{ $label }}</dt>
                                        <dd>{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </section>

                @if ($sections !== [])
                    <section class="kh-bo__card">
                        <div class="kh-bo__card-head">
                            <div>
                                <h2>{{ __('ui.bo.company_detail.page_content') }}</h2>
                                <p>{{ __('ui.bo.company_detail.page_content_hint') }}</p>
                            </div>
                        </div>

                        <div class="kh-bo__card-body kh-bo__card-body--stacked">
                            @foreach ($sections as $section)
                                <article class="kh-bo__panel-view">
                                    <h3>{{ $section['title'] }}</h3>
                                    <p class="kh-bo__prose">{{ $section['body'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.company_detail.compliance') }}</h2>
                            <p>{{ __('ui.bo.companies.verified_count', ['verified' => $verified, 'total' => $company->compliance_records_count]) }}</p>
                        </div>

                        @if ($isAdmin)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('compliance.index', ['q' => $company->name]) }}">{{ __('ui.bo.companies.view_records') }}</a>
                        @endif
                    </div>

                    <div class="kh-bo__table-wrap">
                        <table class="kh-bo__table">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('ui.bo.company_detail.col_record') }}</th>
                                    <th scope="col">{{ __('ui.bo.company_detail.col_category') }}</th>
                                    <th scope="col">{{ __('ui.bo.companies.col_status') }}</th>
                                    <th scope="col">{{ __('ui.bo.company_detail.col_expires') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $record)
                                    <tr>
                                        <td>
                                            <span class="kh-bo__name">
                                                {{ $record->name }}
                                                @if ($record->isVerified())
                                                    <x-verified-badge :show-label="false" :size="16" />
                                                @endif
                                            </span>
                                            <span class="kh-bo__ref">{{ $record->reference ?: __('ui.bo.company_detail.no_reference') }}</span>
                                        </td>

                                        <td>{{ __('ui.bo.options.compliance_category.' . $record->category) }}</td>

                                        <td>
                                            <span class="kh-bo__status kh-bo__status--{{ $record->status }}">
                                                {{ __('ui.bo.status.' . $record->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            @if ($record->expires_on)
                                                {{ $record->expires_on->format('M j, Y') }}
                                                @if ($record->hasExpired())
                                                    <span class="kh-bo__expiry-flag kh-bo__expiry-flag--past">{{ __('ui.bo.compliance.expired') }}</span>
                                                @elseif ($record->expiresSoon())
                                                    <span class="kh-bo__expiry-flag">{{ __('ui.bo.compliance.expiring_soon') }}</span>
                                                @endif
                                            @else
                                                <span class="kh-bo__ref">{{ __('ui.bo.compliance.no_expiry') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="kh-bo__empty">
                                                <strong>{{ __('ui.bo.companies.none_on_file') }}</strong>
                                                <span>{{ __('ui.bo.company_detail.no_records') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.companies.col_job_posts') }}</h2>
                            <p>{{ __('ui.bo.company_detail.job_posts_hint') }}</p>
                        </div>

                        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index', ['q' => $company->name]) }}">{{ __('ui.bo.companies.view_posts') }}</a>
                    </div>

                    <div class="kh-bo__table-wrap">
                        <table class="kh-bo__table">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('ui.bo.company_detail.col_title') }}</th>
                                    <th scope="col">{{ __('ui.bo.companies.col_status') }}</th>
                                    <th scope="col">{{ __('ui.bo.company_detail.col_posted') }}</th>
                                    <th scope="col">{{ __('ui.bo.company_detail.col_applicants') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobPosts as $post)
                                    <tr>
                                        <td>
                                            <span class="kh-bo__name">
                                                <a class="kh-bo__name-link" href="{{ route('job-posts.show', $post) }}">{{ $post->title }}</a>
                                            </span>
                                            <span class="kh-bo__ref">{{ $post->location }}</span>
                                        </td>

                                        <td>
                                            <span class="kh-bo__status kh-bo__status--{{ $post->status === 'published' ? 'verified' : ($post->status === 'draft' ? 'pending' : 'rejected') }}">
                                                {{ ucfirst($post->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ optional($post->published_at)->format('M j, Y') ?: '—' }}
                                            <span class="kh-bo__ref">{{ $post->isPublished() ? $post->postedLabel() : __('ui.bo.job_posts.not_live') }}</span>
                                        </td>

                                        <td>{{ number_format($post->applications_count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="kh-bo__empty">
                                                <strong>{{ __('ui.bo.company_detail.no_posts_title') }}</strong>
                                                <span>{{ __('ui.bo.company_detail.no_posts') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="kh-bo__detail-side">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div><h2>{{ __('ui.bo.company_detail.overview') }}</h2></div>
                    </div>

                    <div class="kh-bo__card-body">
                        <dl class="kh-bo__facts kh-bo__facts--rows">
                            <div>
                                <dt>{{ __('ui.bo.companies.col_status') }}</dt>
                                <dd><span class="kh-bo__status kh-bo__status--{{ $statusTone }}">{{ __('ui.bo.status.' . $company->status) }}</span></dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.companies.form.registration') }}</dt>
                                <dd>{{ $company->registration_no ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.companies.col_compliance') }}</dt>
                                <dd>
                                    {{ __('ui.bo.companies.verified_count', ['verified' => $verified, 'total' => $company->compliance_records_count]) }}
                                    @if ($isAdmin)
                                        <span class="kh-bo__ref">
                                            <a href="{{ route('compliance.index', ['q' => $company->name]) }}">{{ __('ui.bo.companies.view_records') }}</a>
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.companies.col_job_posts') }}</dt>
                                <dd>
                                    {{ number_format($company->job_posts_count) }}
                                    <span class="kh-bo__ref">
                                        <a href="{{ route('job-posts.index', ['q' => $company->name]) }}">{{ __('ui.bo.companies.view_posts') }}</a>
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.company_detail.registered') }}</dt>
                                <dd>{{ optional($company->created_at)->format('d M Y, H:i') ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.company_detail.last_updated') }}</dt>
                                <dd>{{ optional($company->updated_at)->format('d M Y, H:i') ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                @if ($contact->isNotEmpty() || $links->isNotEmpty())
                    <section class="kh-bo__card">
                        <div class="kh-bo__card-head">
                            <div><h2>{{ __('ui.bo.companies.col_contact') }}</h2></div>
                        </div>

                        <div class="kh-bo__card-body">
                            <ul class="kh-bo__contactlist">
                                @foreach ($contact as $row)
                                    @php($tag = $row['href'] ? 'a' : 'div')
                                    <li>
                                        <{{ $tag }} class="kh-bo__contactrow kh-bo__contactrow--{{ $row['key'] }}"
                                            @if ($row['href']) href="{{ $row['href'] }}" @endif>
                                            <span class="kh-bo__contactrow-icon" aria-hidden="true">
                                                @switch ($row['key'])
                                                    @case ('email')
                                                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2.5" /><path d="M3.5 7.5l8.5 6 8.5-6" /></svg>
                                                        @break
                                                    @case ('phone')
                                                        <svg viewBox="0 0 24 24"><path d="M6 3h3.5l1.8 4.5-2.4 1.7a12.5 12.5 0 005.9 5.9l1.7-2.4L21 14.5V18a2.5 2.5 0 01-2.7 2.5A16.5 16.5 0 013.5 5.7 2.5 2.5 0 016 3z" /></svg>
                                                        @break
                                                    @default
                                                        <svg viewBox="0 0 24 24"><path d="M12 21.5s7-5.6 7-11.5a7 7 0 10-14 0c0 5.9 7 11.5 7 11.5z" /><circle cx="12" cy="10" r="2.6" /></svg>
                                                @endswitch
                                            </span>

                                            <span class="kh-bo__contactrow-body">
                                                <span class="kh-bo__contactrow-label">{{ $row['label'] }}</span>
                                                <span class="kh-bo__contactrow-value">{{ $row['value'] }}</span>
                                            </span>

                                            @if ($row['href'])
                                                <svg class="kh-bo__contactrow-go" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M5 12h13" /><path d="M13 6l6 6-6 6" />
                                                </svg>
                                            @endif
                                        </{{ $tag }}>
                                    </li>
                                @endforeach
                            </ul>

                            @if ($links->isNotEmpty())
                                <div class="kh-bo__contactlinks">
                                    <span class="kh-bo__contactlinks-label">{{ __('ui.bo.company_detail.profiles') }}</span>

                                    <div class="kh-bo__chipwrap">
                                        @foreach ($links as $link)
                                            <a class="kh-bo__linkchip kh-bo__linkchip--{{ $link['key'] }}"
                                                href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                                title="{{ $link['url'] }}"
                                                aria-label="{{ $link['label'] }}: {{ $link['handle'] }}">
                                                <i class="{{ $link['icon'] }}" aria-hidden="true"></i>
                                                <span>{{ $link['handle'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                @if ($isAdmin)
                    <section class="kh-bo__card">
                        <div class="kh-bo__card-head">
                            <div><h2>{{ __('ui.bo.actions') }}</h2></div>
                        </div>

                        <div class="kh-bo__card-body">
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('compliance.create', ['company_id' => $company->id]) }}">{{ __('ui.admin.nav.add_record') }}</a>

                            {{-- Refused server-side while job posts or compliance records
                                 still point at the company; see CompaniesController::destroy(). --}}
                            <form method="POST" action="{{ route('companies.destroy', $company) }}"
                                onsubmit="return confirm('{{ addslashes(__('ui.bo.delete_confirm', ['name' => $company->name])) }}');">
                                @csrf
                                @method('DELETE')
                                <button class="kh-bo__btn kh-bo__btn--ghost kh-bo__btn--danger" type="submit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                    </svg>
                                    {{ __('ui.bo.companies.delete_company') }}
                                </button>
                            </form>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
@endsection
