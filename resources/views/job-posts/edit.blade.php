@extends('layouts.admin')

@section('title', __('ui.bo.job_posts.edit_title') . ' | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
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
                    {{ $post->company }} · {{ $post->location }}
                    @if ($post->isPublished())
                        · {{ __('ui.bo.job_posts.live_at') }} <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">/jobs/{{ $post->slug }}</a>
                    @else
                        · {{ __('ui.bo.job_posts.not_published') }}
                    @endif
                </p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">{{ __('ui.bo.job_posts.back_to_index') }}</a>
        </header>

        <form method="POST" action="{{ route('job-posts.update', $post) }}">
            @csrf
            @method('PUT')
            @include('job-posts._form', ['post' => $post])
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/backoffice-tabs.js') }}?v={{ filemtime(public_path('js/backoffice-tabs.js')) }}"></script>
@endpush
