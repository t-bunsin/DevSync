@extends('layouts.admin')

@section('title', 'Add compliance record | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
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
