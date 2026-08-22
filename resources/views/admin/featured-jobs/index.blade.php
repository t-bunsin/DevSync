@extends('layouts.admin')

@section('title', 'Featured Jobs | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Featured Jobs</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>Featured Jobs</h1>
                <p>Pick which published roles are sorted to the top of the public jobs list. {{ $featuredCount }} currently featured.</p>
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

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>Published job posts</h2>
                    <p>{{ number_format($posts->total()) }} {{ \Illuminate\Support\Str::plural('post', $posts->total()) }}.</p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('featured-jobs') }}" role="search">
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
                            <th scope="col">Job</th>
                            <th scope="col">Posted</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">
                                            @if ($post->employer?->logoUrl())
                                                <img src="{{ $post->employer->logoUrl() }}" alt="">
                                            @elseif ($post->employer)
                                                {{ $post->employer->initials() }}
                                            @else
                                                {{ strtoupper(substr($post->company, 0, 2)) }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">
                                                <a class="kh-bo__name-link" href="{{ route('job-posts.show', $post) }}">{{ $post->title }}</a>
                                            </span>
                                            <span class="kh-bo__ref">{{ $post->company }} · {{ $post->location }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="kh-bo__nowrap">{{ ucfirst($post->postedAgo()) }}</td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $post->featured ? 'verified' : 'pending' }}">
                                        {{ $post->featured ? 'Featured' : 'Not featured' }}
                                    </span>
                                </td>

                                <td>
                                    <form method="POST" action="{{ route('featured-jobs.toggle', $post) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">
                                            {{ $post->featured ? 'Remove from featured' : 'Feature this post' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="kh-bo__empty">
                                        <strong>No published job posts</strong>
                                        <span>
                                            @if ($searchTerm)
                                                Nothing matches this search.
                                                <a href="{{ route('featured-jobs') }}">Clear it</a> to see everything.
                                            @else
                                                Featured placement only applies once a post is published.
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.kh-bo-pagination', ['paginator' => $posts])
        </section>
    </div>
@endsection
