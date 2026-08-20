@extends('layouts.admin')

@section('title', 'Edit company | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('companies') }}">Companies</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $company->name }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
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

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('companies') }}">Back to companies</a>
        </header>

        <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.companies._form', ['company' => $company])
        </form>
    </div>
@endsection
