@extends('layouts.admin')

@section('title', 'Edit resume | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
                <h1>{{ $resume->full_name }}</h1>
                <p>
                    {{ $resume->headline ?: 'No headline' }}
                    @if ($resume->created_at)
                        · registered {{ $resume->created_at->format('M j, Y') }}
                    @endif
                    @if ($resume->author)
                        by {{ $resume->author->displayName() }}
                    @endif
                </p>
            </div>

            <div class="kh-bo__actions">
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.download', $resume) }}">Download PDF</a>
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.show', $resume) }}">Preview</a>
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.index') }}">Back to register</a>
            </div>
        </header>

        <form method="POST" action="{{ route('resumes.update', $resume) }}">
            @csrf
            @method('PUT')
            @include('resumes._form', ['resume' => $resume])
        </form>
    </div>
@endsection
