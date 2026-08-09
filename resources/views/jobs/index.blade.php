@extends('layouts.master')

@section('title', 'Search Jobs | KH-WORKS')
@section('meta-description', 'Search verified jobs from trusted employers in Cambodia and remote-first teams across Southeast Asia.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/job-directory.css') }}">
@endpush

@section('content')
    <section class="jf-directory-heading" aria-labelledby="search-title">
        <div class="jf-shell">
            <span class="jf-directory-heading__eyebrow">KH-WORKS Careers</span>
            <h1 id="search-title">Job Directory</h1>
            <p>Discover verified opportunities from trusted employers across Cambodia and remote-first teams.</p>
        </div>
    </section>

    <div class="jf-jobs-explorer--directory" data-jobs-explorer>
        @include('jobs.partials.search', ['directoryStyle' => true])
        @include('jobs.partials.catalog')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/jobs.js') }}"></script>
@endpush
