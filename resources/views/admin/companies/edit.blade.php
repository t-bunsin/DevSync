@extends('layouts.admin')

@section('title', 'Edit company | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('companies') }}">{{ __('ui.bo.companies.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $company->name }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>
                    {{ $company->name }}
                    @if ($company->hasVerifiedCompliance())
                        <x-verified-badge />
                    @endif
                </h1>
                <p>
                    {{ $company->job_posts_count }} job {{ \Illuminate\Support\Str::plural('post', $company->job_posts_count) }}
                    · {{ $company->compliance_records_count }} compliance
                    {{ \Illuminate\Support\Str::plural('record', $company->compliance_records_count) }}
                </p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('companies') }}">{{ __('ui.bo.companies.back_to_list') }}</a>
        </header>


        @if (session('success'))
            <div class="kh-bo__flash" role="status">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="kh-bo__errors" role="alert">
                @foreach ($errors->all() as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.companies._form', ['company' => $company])
        </form>
    </div>
@endsection
