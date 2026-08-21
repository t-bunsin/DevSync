@extends('layouts.admin')

@section('title', 'My Applications | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $applications->count();
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">My Applications</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>My Applications</h1>
                <p>The jobs you've applied to, and where each one stands.</p>
            </div>
        </header>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>Applications</h2>
                    <p>{{ $total }} {{ \Illuminate\Support\Str::plural('application', $total) }} shown.</p>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table kh-bo__table--dense">
                    <thead>
                        <tr>
                            <th scope="col">Job</th>
                            <th scope="col">Applied</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            @php
                                $post = $application->jobPost;
                                $appliedAt = $application->applied_at ?? $application->created_at;
                            @endphp
                            <tr>
                                <td>
                                    <div>
                                        <span class="kh-bo__name">
                                            @if ($post)
                                                <a class="kh-bo__name-link" href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">{{ $post->title }}</a>
                                            @else
                                                <span class="kh-bo__ref">Job post removed</span>
                                            @endif
                                        </span>
                                        @if ($post)
                                            <span class="kh-bo__ref">{{ $post->company }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="kh-bo__posted">
                                    {{ ucfirst($application->appliedAgo()) }}
                                    <span class="kh-bo__ref">{{ $appliedAt?->format('d M Y') ?: '—' }}</span>
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $application->statusTone() }}">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="kh-bo__empty">
                                        <strong>No applications yet</strong>
                                        <span>
                                            Browse <a href="{{ route('jobs.index') }}">open roles</a> and apply — they'll show up here.
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.kh-bo-pagination', ['paginator' => $applications])
        </section>
    </div>
@endsection
