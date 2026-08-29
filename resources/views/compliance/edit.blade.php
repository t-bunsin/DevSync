@extends('layouts.admin')

@section('title', 'Edit compliance record | KH-WORKS Admin')

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
            <span aria-current="page">{{ $compliance->name }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>
                    {{ $compliance->name }}
                    @if ($compliance->isVerified())
                        <x-verified-badge />
                    @endif
                </h1>
                <p>
                    {{ $compliance->category }}
                    @if ($compliance->isVerified() && $compliance->verified_at)
                        · verified {{ $compliance->verified_at->format('M j, Y') }}
                        @if ($compliance->verifier) by {{ $compliance->verifier->displayName() }} @endif
                    @endif
                </p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('compliance.index') }}">{{ __('ui.bo.compliance.back_to_register') }}</a>
        </header>

        <form method="POST" action="{{ route('compliance.update', $compliance) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('compliance._form', ['compliance' => $compliance])
        </form>
    </div>
@endsection
