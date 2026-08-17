@extends('layouts.admin')

@section('title', $post->title . ' | KH-WORKS Admin')

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
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office · Job post</span>
                <h1>{{ $post->title }}</h1>
                <p>
                    {{ $post->company }}
                    @if ($job['company_verified'])
                        <x-verified-badge :show-label="false" :size="15" />
                    @endif
                    · {{ $post->location }}
                    @if ($post->isPublished())
                        · live at <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">/jobs/{{ $post->slug }}</a>
                    @else
                        · not published
                    @endif
                </p>
            </div>

            <div class="kh-bo__head-actions">
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">Back to job posts</a>

                @if ($post->isPublished())
                    <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('jobs.show', $post->slug) }}"
                        target="_blank" rel="noopener">View on site</a>
                @endif

                <a class="kh-bo__btn" href="{{ route('job-posts.edit', $post) }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                    </svg>
                    Edit post
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
                            <h2>Summary</h2>
                            <p>The short pitch shown on the job card and search results.</p>
                        </div>

                        <div class="kh-bo__chips">
                            <span class="kh-bo__status kh-bo__status--{{ $statusTone }}">{{ ucfirst($post->status) }}</span>
                            @if ($post->featured)
                                <span class="kh-bo__status kh-bo__status--verified">Featured</span>
                            @endif
                            @if ($post->highlighted)
                                <span class="kh-bo__status kh-bo__status--pending">Spotlight</span>
                            @endif
                        </div>
                    </div>

                    <div class="kh-bo__card-body">
                        @if (filled($post->summary))
                            <p class="kh-bo__prose">{{ $post->summary }}</p>
                        @else
                            <p class="kh-bo__muted">No summary written yet.</p>
                        @endif

                        <dl class="kh-bo__facts">
                            @foreach ($job['detail_items'] as $item)
                                <div>
                                    <dt>{{ $item['label'] }}</dt>
                                    <dd>{{ $item['value'] }}</dd>
                                </div>
                            @endforeach

                            <div>
                                <dt>Salary</dt>
                                <dd>{{ $job['salary'] }}</dd>
                            </div>

                            <div>
                                <dt>Applicants</dt>
                                <dd>{{ $job['applicants'] }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>Page content</h2>
                            <p>Exactly what the three panels render on the public job page.</p>
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
                                    <p class="kh-bo__muted">No body copy.</p>
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
                                <h2>What we can offer</h2>
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
                        <div><h2>Dates</h2></div>
                    </div>

                    <div class="kh-bo__card-body">
                        <dl class="kh-bo__facts kh-bo__facts--rows">
                            <div>
                                <dt>Posted</dt>
                                <dd>
                                    {{ optional($post->published_at)->format('d M Y') ?: 'Not published' }}
                                    <span class="kh-bo__ref">{{ $post->isPublished() ? $post->postedLabel() : 'Not live' }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt>Deadline</dt>
                                <dd>
                                    {{ optional($post->deadline)->format('d M Y') ?: 'No end date' }}
                                    <span class="kh-bo__ref">{{ $post->deadlineLabel() }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt>Registered</dt>
                                <dd>
                                    {{ optional($post->created_at)->format('d M Y, H:i') ?: '—' }}
                                    <span class="kh-bo__ref">Post by {{ $post->authorLabel() }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt>Last updated</dt>
                                <dd>{{ optional($post->updated_at)->format('d M Y, H:i') ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div><h2>Employer</h2></div>
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
                                        <dt>Website</dt>
                                        <dd><a href="{{ $employer->website }}" target="_blank" rel="noopener">{{ $employer->website }}</a></dd>
                                    </div>
                                @endif

                                @if ($employer->address)
                                    <div>
                                        <dt>Address</dt>
                                        <dd>{{ $employer->address }}</dd>
                                    </div>
                                @endif
                            </dl>

                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('companies.edit', $employer) }}">Open company profile</a>
                        @endif
                    </div>
                </section>

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div><h2>Publishing</h2></div>
                    </div>

                    <div class="kh-bo__card-body">
                        <dl class="kh-bo__facts kh-bo__facts--rows">
                            <div>
                                <dt>Status</dt>
                                <dd><span class="kh-bo__status kh-bo__status--{{ $statusTone }}">{{ ucfirst($post->status) }}</span></dd>
                            </div>

                            <div>
                                <dt>Public URL</dt>
                                <dd>
                                    @if ($post->isPublished())
                                        <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">/jobs/{{ $post->slug }}</a>
                                    @else
                                        /jobs/{{ $post->slug }}
                                        <span class="kh-bo__ref">Reachable once published</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt>Card artwork</dt>
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
                                Delete this post
                            </button>
                        </form>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
