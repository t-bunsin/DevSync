@extends('layouts.admin')

@section('title', 'Add resume | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
                <h1>Add resume</h1>
                <p>Register a candidate CV so it can be searched and shortlisted.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.index') }}">Back to register</a>
        </header>

        <form method="POST" action="{{ route('resumes.store') }}">
            @csrf
            @include('resumes._form', ['resume' => $resume])
        </form>
    </div>
@endsection
