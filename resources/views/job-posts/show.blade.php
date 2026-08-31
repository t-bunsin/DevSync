@extends('layouts.admin')

@section('title', $post->title . ' | ZIN-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        // The same array the public job pages render from, so this page shows
        // what a visitor actually sees — fallbacks and all — not just the raw
        // columns.
        $job = $post->toCatalogArray();
        $employer = $post->employer;
        $statusTone = $post->status === 'published' ? 'verified' : ($post->status === 'draft' ? 'pending' : 'rejected');
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('job-posts.index') }}">{{ __('ui.bo.job_posts.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $post->title }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ $post->title }}</h1>
                <p>
                    {{ $post->company }}
                    @if ($job['company_verified'])
                        <x-verified-badge :show-label="false" :size="15" />
                    @endif
                    · {{ $post->location }}
                    @if ($post->isPublished())
                        · {{ __('ui.bo.job_detail.live_at') }} <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">/jobs/{{ $post->slug }}</a>
                    @else
                        · not published
                    @endif
                </p>
            </div>

            <div class="kh-bo__head-actions">
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">{{ __('ui.bo.job_detail.back_to_posts') }}</a>

                @if ($post->isPublished())
                    <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('jobs.show', $post->slug) }}"
                        target="_blank" rel="noopener">{{ __('ui.bo.job_detail.view_on_site') }}</a>
                @endif

                <a class="kh-bo__btn" href="{{ route('job-posts.edit', $post) }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                    </svg>
                    {{ __('ui.bo.job_detail.edit_post') }}
                </a>
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

        <div class="kh-bo__detail">
            <div class="kh-bo__detail-main">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.job_detail.summary') }}</h2>
                            <p>{{ __('ui.bo.job_detail.summary_hint') }}</p>
                        </div>

                        <div class="kh-bo__chips">
                            <span class="kh-bo__status kh-bo__status--{{ $statusTone }}">{{ ucfirst($post->status) }}</span>
                            @if ($post->featured)
                                <span class="kh-bo__status kh-bo__status--verified">{{ __('ui.bo.job_posts.featured') }}</span>
                            @endif
                            @if ($post->highlighted)
                                <span class="kh-bo__status kh-bo__status--pending">{{ __('ui.bo.job_posts.spotlight') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="kh-bo__card-body">
                        @if (filled($post->summary))
                            <p class="kh-bo__prose">{{ $post->summary }}</p>
                        @else
                            <p class="kh-bo__muted">{{ __('ui.bo.job_detail.no_summary') }}</p>
                        @endif

                        <dl class="kh-bo__facts">
                            @foreach ($job['detail_items'] as $item)
                                <div>
                                    <dt>{{ $item['label'] }}</dt>
                                    <dd>{{ $item['value'] }}</dd>
                                </div>
                            @endforeach

                            <div>
                                <dt>{{ __('ui.bo.job_detail.salary') }}<dt>
                                <dd>{{ $job['salary'] }}</dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.job_detail.applicants') }}<dt>
                                <dd>
                                    {{ $job['applicants'] }}
                                    {{-- The recorded applications, which is what the figure
                                         above counts once anyone has actually applied. --}}
                                    @if (auth()->user()?->hasPermission(\App\Models\Permission::APPLICATION_VIEW))
                                    <a class="kh-bo__ref" href="{{ route('job-posts.applications', $post) }}">
                                        @if ($post->hasApplications())
                                            View {{ number_format($post->applicantCount()) }}
                                            {{ trans_choice('ui.bo.job_posts.candidates_applied', $post->applicantCount()) }}
                                        @else
                                            No applications recorded yet
                                        @endif
                                    </a>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.job_detail.page_content') }}</h2>
                            <p>{{ __('ui.bo.job_detail.page_content_hint') }}</p>
                        </div>
                    </div>

                    <div class="kh-bo__card-body kh-bo__card-body--stacked">
                        @foreach ($job['tabs'] as $key => $panel)
                            <article class="kh-bo__panel-view">
                                <span class="kh-bo__eyebrow">{{ str_replace('_', ' ', $key) }}</span>
                                <h3>{{ $panel['title'] }}</h3>

                                @if (filled($panel['body']))
                                    <p class="kh-bo__prose">{{ $panel['body'] }}</p>
                                @else
                                    <p class="kh-bo__muted">{{ __('ui.bo.job_detail.no_body') }}</p>
                                @endif

                                @if ($panel['list'] !== [])
                                    <h4>{{ $panel['list_title'] }}</h4>
                                    <ul class="kh-bo__bullets">
                                        @foreach ($panel['list'] as $line)
                                            <li>{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>

                @if ($job['offer'] !== [])
                    <section class="kh-bo__card">
                        <div class="kh-bo__card-head">
                            <div>
                                <h2>{{ __('ui.bo.job_detail.offer') }}</h2>
                                <p>{{ count($job['offer']) }} of 3 columns filled in.</p>
                            </div>
                        </div>

                        <div class="kh-bo__card-body">
                            <div class="kh-bo__offer">
                                @foreach ($job['offer'] as $column)
                                    <article>
                                        <h3>{{ $column['title'] }}</h3>
                                        <ul class="kh-bo__bullets">
                                            @foreach ($column['items'] as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="kh-bo__detail-side">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div><h2>{{ __('ui.bo.job_detail.dates') }}</h2></div>
                    </div>

                    <div class="kh-bo__card-body">
                        <dl class="kh-bo__facts kh-bo__facts--rows">
                            <div>
                                <dt>{{ __('ui.bo.job_detail.posted') }}<dt>
                                <dd>
                                    {{ optional($post->published_at)->format('d M Y') ?: 'Not published' }}
                                    <span class="kh-bo__ref">{{ $post->isPublished() ? $post->postedLabel() : __('ui.bo.job_posts.not_live') }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.job_detail.deadline') }}<dt>
                                <dd>
                                    {{ optional($post->deadline)->format('d M Y') ?: 'No end date' }}
                                    <span class="kh-bo__ref">{{ $post->deadlineLabel() }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.job_detail.registered') }}<dt>
                                <dd>
                                    {{ optional($post->created_at)->format('d M Y, H:i') ?: '—' }}
                                    <span class="kh-bo__ref">Post by {{ $post->authorLabel() }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.job_detail.last_updated') }}<dt>
                                <dd>{{ optional($post->updated_at)->format('d M Y, H:i') ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div><h2>{{ __('ui.bo.job_detail.employer') }}</h2></div>
                    </div>

                    <div class="kh-bo__card-body">
                        <div class="kh-bo__identity">
                            <span class="kh-bo__logo" aria-hidden="true">
                                @if ($employer?->logoUrl())
                                    <img src="{{ $employer->logoUrl() }}" alt="">
                                @elseif ($employer)
                                    {{ $employer->initials() }}
                                @else
                                    {{ strtoupper(substr($post->company, 0, 2)) }}
                                @endif
                            </span>
                            <div>
                                <span class="kh-bo__name">
                                    {{ $post->company }}
                                    @if ($job['company_verified'])
                                        <x-verified-badge :show-label="false" :size="16" />
                                    @endif
                                </span>
                                <span class="kh-bo__ref">{{ $employer?->industry ?: 'No linked company profile' }}</span>
                            </div>
                        </div>

                        @if ($employer)
                            <dl class="kh-bo__facts kh-bo__facts--rows">
                                @foreach ($employer->employerDetails() as $label => $value)
                                    <div>
                                        <dt>{{ $label }}</dt>
                                        <dd>{{ $value }}</dd>
                                    </div>
                                @endforeach

                                @if ($employer->website)
                                    <div>
                                        <dt>{{ __('ui.bo.job_detail.website') }}<dt>
                                        <dd><a href="{{ $employer->website }}" target="_blank" rel="noopener">{{ $employer->website }}</a></dd>
                                    </div>
                                @endif

                                @if ($employer->address)
                                    <div>
                                        <dt>{{ __('ui.bo.job_detail.address') }}<dt>
                                        <dd>{{ $employer->address }}</dd>
                                    </div>
                                @endif
                            </dl>

                            @if (auth()->user()?->isAdmin())
                                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('companies.edit', $employer) }}">{{ __('ui.bo.job_detail.open_company') }}</a>
                            @endif
                        @endif
                    </div>
                </section>

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div><h2>{{ __('ui.bo.job_detail.publishing') }}</h2></div>
                    </div>

                    <div class="kh-bo__card-body">
                        <dl class="kh-bo__facts kh-bo__facts--rows">
                            <div>
                                <dt>{{ __('ui.bo.job_detail.status') }}<dt>
                                <dd><span class="kh-bo__status kh-bo__status--{{ $statusTone }}">{{ ucfirst($post->status) }}</span></dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.job_detail.public_url') }}<dt>
                                <dd>
                                    @if ($post->isPublished())
                                        <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">/jobs/{{ $post->slug }}</a>
                                    @else
                                        /jobs/{{ $post->slug }}
                                        <span class="kh-bo__ref">{{ __('ui.bo.job_detail.reachable_once_published') }}</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>{{ __('ui.bo.job_detail.card_artwork') }}<dt>
                                <dd>{{ ucfirst($job['logo']) }}</dd>
                            </div>
                        </dl>

                        <form method="POST" action="{{ route('job-posts.destroy', $post) }}"
                            onsubmit="return confirm('Delete “{{ addslashes($post->title) }}”? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button class="kh-bo__btn kh-bo__btn--ghost kh-bo__btn--danger" type="submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                </svg>
                                {{ __('ui.bo.job_detail.delete_post') }}
                            </button>
                        </form>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
