@extends('layouts.admin')

@section('title', 'Job Posts | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $posts->count();
        $filters = ['' => 'All', 'published' => 'Published', 'draft' => 'Draft', 'closed' => 'Closed'];
    @endphp

    <div class="kh-bo">
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
                <h1>Job posts</h1>
                <p>Write the roles that appear on the public jobs pages.</p>
            </div>

            <a class="kh-bo__btn" href="{{ route('job-posts.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Add job post
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

        @if ($counts->get('published', 0) === 0)
            <div class="kh-bo__errors" role="status">
                <strong>No published posts yet.</strong>
                The public jobs pages are still showing the built-in demo roles. Publish a post here and it replaces them.
            </div>
        @endif

        <section class="kh-bo__tiles" aria-label="Job post summary">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>Job posts</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('published', 0)) }}</strong><span>Published</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('draft', 0)) }}</strong><span>Drafts</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2" /><path d="M7 11V7a5 5 0 0110 0v4" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('closed', 0)) }}</strong><span>Closed</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>All job posts</h2>
                    <p>{{ $total }} {{ \Illuminate\Support\Str::plural('post', $total) }} shown.</p>
                </div>

                <div class="kh-bo__tools">
                    <div class="kh-bo__filters">
                        @foreach ($filters as $value => $label)
                            <a class="kh-bo__filter{{ (string) $activeStatus === (string) $value ? ' is-active' : '' }}"
                                href="{{ route('job-posts.index', array_filter(['status' => $value, 'q' => $searchTerm])) }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form class="kh-bo__search" method="GET" action="{{ route('job-posts.index') }}" role="search">
                        @if ($activeStatus)
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="Search title or company" aria-label="Search job posts">
                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">Search</button>
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">Role</th>
                            <th scope="col">Location</th>
                            <th scope="col">Type</th>
                            <th scope="col">Status</th>
                            <th scope="col">Posted</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">{{ strtoupper(substr($post->company, 0, 2)) }}</span>
                                        <div>
                                            <span class="kh-bo__name">
                                                {{ $post->title }}
                                                @if ($post->featured)
                                                    <span class="kh-bo__status kh-bo__status--verified">Featured</span>
                                                @endif
                                                @if ($post->highlighted)
                                                    <span class="kh-bo__status kh-bo__status--pending">Spotlight</span>
                                                @endif
                                            </span>
                                            <span class="kh-bo__ref">{{ $post->company }} · /jobs/{{ $post->slug }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $post->location }}</td>
                                <td>{{ $post->type }} · {{ $post->mode }}</td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $post->status === 'published' ? 'verified' : ($post->status === 'draft' ? 'pending' : 'rejected') }}">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($post->isPublished())
                                        {{ $post->postedLabel() }}
                                        <span class="kh-bo__ref">{{ $post->deadlineLabel() }}</span>
                                    @else
                                        <span class="kh-bo__ref">Not live</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="kh-bo__actions">
                                        @if ($post->isPublished())
                                            <a class="kh-bo__action" href="{{ route('jobs.show', $post->slug) }}"
                                                target="_blank" rel="noopener"
                                                title="View on the site" aria-label="View {{ $post->title }} on the public site">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" /><path d="M15 3h6v6" /><path d="M10 14L21 3" />
                                                </svg>
                                            </a>
                                        @endif

                                        <a class="kh-bo__action" href="{{ route('job-posts.edit', $post) }}"
                                            title="Edit post" aria-label="Edit {{ $post->title }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('job-posts.destroy', $post) }}"
                                            onsubmit="return confirm('Delete “{{ addslashes($post->title) }}”? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                title="Delete post" aria-label="Delete {{ $post->title }}">
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
                                        <strong>No job posts</strong>
                                        <span>
                                            @if ($activeStatus || $searchTerm)
                                                Nothing matches this filter.
                                                <a href="{{ route('job-posts.index') }}">Clear it</a> to see everything.
                                            @else
                                                Add the first job post to replace the demo roles on the public site.
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
