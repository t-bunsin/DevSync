@extends('layouts.admin')

@section('title', 'Add ' . strtolower($label) . ' | ZIN-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
@php
    // Singular/plural forms for this component type, so the sentences below
    // stay one translatable unit instead of "Add " . strtolower($label).
    $item = __('ui.bo.components.types.' . $type . '.one');
    $items = __('ui.bo.components.types.' . $type . '.many');
    $typeTitle = __('ui.bo.components.types.' . $type . '.title');
@endphp
    @php($plural = \Illuminate\Support\Str::plural($label))

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('components.index', $type) }}">{{ $typeTitle }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.components.add', ['item' => $item]) }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.components.add', ['item' => $item]) }}</h1>
                <p>{{ __('ui.bo.components.create_subtitle') }}</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('components.index', $type) }}">{{ __('ui.bo.components.back_to', ['items' => $items]) }}</a>
        </header>

        <form method="POST" action="{{ route('components.store', $type) }}">
            @csrf
            @include('admin.components._form', ['record' => $record, 'type' => $type, 'label' => $label])
        </form>
    </div>
@endsection
