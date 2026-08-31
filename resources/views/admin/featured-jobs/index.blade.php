@extends('layouts.admin')

@section('title', 'Featured Jobs | ZIN-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.featured.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.featured.title') }}</h1>
                <p>{{ __('ui.bo.featured.subtitle', ['count' => $featuredCount]) }}</p>
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
                    <h2>{{ __('ui.bo.featured.published_posts') }}</h2>
                    <p>{{ trans_choice('ui.bo.featured.posts_count', $posts->total(), ['count' => number_format($posts->total())]) }}</p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('featured-jobs') }}" role="search">
                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ __('ui.bo.featured.search_placeholder') }}" aria-label="{{ __('ui.bo.featured.search_aria') }}">
                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">{{ __('ui.bo.search') }}</button>
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.bo.featured.col_job') }}</th>
                            <th scope="col">{{ __('ui.bo.featured.col_posted') }}</th>
                            <th scope="col">{{ __('ui.bo.featured.col_status') }}</th>
                            <th scope="col"><span class="visually-hidden">{{ __('ui.bo.actions') }}</span></th>
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
                                        {{ $post->featured ? __('ui.bo.featured.is_featured') : __('ui.bo.featured.not_featured') }}
                                    </span>
                                </td>

                                <td>
                                    <form method="POST" action="{{ route('featured-jobs.toggle', $post) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">
                                            {{ $post->featured ? __('ui.bo.featured.remove') : __('ui.bo.featured.feature') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="kh-bo__empty">
                                        <strong>{{ __('ui.bo.featured.empty_title') }}</strong>
                                        <span>
                                            @if ($searchTerm)
                                                {{ __('ui.bo.featured.empty_filtered') }}
                                                <a href="{{ route('featured-jobs') }}">{{ __('ui.bo.clear_it') }}</a> {{ __('ui.bo.to_see_everything') }}
                                            @else
                                                {{ __('ui.bo.featured.empty_none') }}
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
