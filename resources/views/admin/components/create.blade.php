@extends('layouts.admin')

@section('title', 'Add ' . strtolower($label) . ' | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php($plural = \Illuminate\Support\Str::plural($label))

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('components.index', $type) }}">{{ $plural }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Add {{ strtolower($label) }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>Add {{ strtolower($label) }}</h1>
                <p>It becomes selectable on the job post form as soon as it's saved.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('components.index', $type) }}">Back to {{ strtolower($plural) }}</a>
        </header>

        <form method="POST" action="{{ route('components.store', $type) }}">
            @csrf
            @include('admin.components._form', ['record' => $record, 'type' => $type, 'label' => $label])
        </form>
    </div>
@endsection
