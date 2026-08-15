@extends('layouts.admin')

@section('title', 'Add job post | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
                <h1>Add job post</h1>
                <p>Publish it and it appears on the homepage explorer and the jobs directory.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">Back to job posts</a>
        </header>

        <form method="POST" action="{{ route('job-posts.store') }}">
            @csrf
            @include('job-posts._form', ['post' => $post])
        </form>
    </div>
@endsection
