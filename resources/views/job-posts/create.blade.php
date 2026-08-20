@extends('layouts.admin')

@section('title', 'Add job post | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('job-posts.index') }}">Job posts</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Add job post</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>Add job post</h1>
                <p>Publish it and it appears on the homepage explorer and the jobs directory.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">Back to job posts</a>
        </header>

        <form method="POST" action="{{ route('job-posts.store') }}">
            @csrf
            @include('job-posts._form', ['post' => $post])
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/backoffice-tabs.js') }}?v={{ filemtime(public_path('js/backoffice-tabs.js')) }}"></script>
@endpush
