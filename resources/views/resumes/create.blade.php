@extends('layouts.admin')

@section('title', 'Add resume | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('resumes.index') }}">Resumes</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Add resume</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
                <h1>Add resume</h1>
                <p>Register a candidate CV so it can be searched and shortlisted.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.index') }}">Back to register</a>
        </header>

        <form method="POST" action="{{ route('resumes.store') }}" enctype="multipart/form-data">
            @csrf
            @include('resumes._form', ['resume' => $resume])
        </form>
    </div>
@endsection
