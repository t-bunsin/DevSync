@extends('layouts.admin')

@section('title', 'Add compliance record | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('compliance.index') }}">{{ __('ui.bo.compliance.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.compliance.create_title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.compliance.create_title') }}</h1>
                <p>{{ __('ui.bo.compliance.create_subtitle') }}</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('compliance.index') }}">{{ __('ui.bo.compliance.back_to_register') }}</a>
        </header>

        <form method="POST" action="{{ route('compliance.store') }}" enctype="multipart/form-data">
            @csrf
            @include('compliance._form', ['compliance' => $compliance])
        </form>
    </div>
@endsection
