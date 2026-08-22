@extends('layouts.admin')

@section('title', 'Edit ' . strtolower($label) . ' | KH-WORKS Admin')

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
            <span aria-current="page">{{ $record->name }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ $record->name }}</h1>
                <p>
                    {{ $record->usage_count ?? 0 }} job {{ \Illuminate\Support\Str::plural('post', $record->usage_count ?? 0) }} currently use this {{ strtolower($label) }}
                    · Registered {{ $record->created_at?->format('d M Y') ?? '—' }} by {{ $record->registeredByLabel() }}
                </p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('components.index', $type) }}">Back to {{ strtolower($plural) }}</a>
        </header>

        <form method="POST" action="{{ route('components.update', [$type, $record]) }}">
            @csrf
            @method('PUT')
            @include('admin.components._form', ['record' => $record, 'type' => $type, 'label' => $label])
        </form>
    </div>
@endsection
