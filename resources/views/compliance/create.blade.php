@extends('layouts.admin')

@section('title', 'Add compliance record | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('compliance.index') }}">Compliance</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Add compliance record</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>Add compliance record</h1>
                <p>Register a licence or certificate so it can be reviewed and signed off.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('compliance.index') }}">Back to register</a>
        </header>

        <form method="POST" action="{{ route('compliance.store') }}" enctype="multipart/form-data">
            @csrf
            @include('compliance._form', ['compliance' => $compliance])
        </form>
    </div>
@endsection
