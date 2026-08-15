@extends('layouts.admin')

@section('title', 'Edit compliance record | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/compliance.css') }}?v={{ filemtime(public_path('css/compliance.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-comp">
        <header class="kh-comp__head">
            <div>
                <span class="kh-comp__kicker">Back office</span>
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

            <a class="kh-comp__btn kh-comp__btn--ghost" href="{{ route('compliance.index') }}">Back to register</a>
        </header>

        <form method="POST" action="{{ route('compliance.update', $compliance) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('compliance._form', ['compliance' => $compliance])
        </form>
    </div>
@endsection
