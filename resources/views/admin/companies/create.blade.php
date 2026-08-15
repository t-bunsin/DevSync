@extends('layouts.admin')

@section('title', 'Add company | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
                <h1>Add company</h1>
                <p>Register an employer so job posts and compliance records can be filed against it.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('companies') }}">Back to companies</a>
        </header>

        <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.companies._form', ['company' => $company])
        </form>
    </div>
@endsection
