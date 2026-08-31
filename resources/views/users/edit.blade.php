@extends('layouts.admin')

@section('title', __('ui.bo.users.form.edit_title') . ' | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php($displayName = $user->displayName())

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('users') }}">{{ __('ui.bo.users.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $displayName }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.users.form.edit_title') }}</h1>
                <p>
                    {{ __('ui.bo.users.form.edit_lead_before') }} <strong>{{ $displayName }}</strong> {{ __('ui.bo.users.form.edit_lead_after') }}
                    @if ($user->created_at)
                        · {{ __('ui.bo.users.col_joined') }} {{ $user->created_at->translatedFormat('M d, Y') }}
                    @endif
                </p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('users') }}">{{ __('ui.bo.users.form.back_to_list') }}</a>
        </header>

        <form method="POST" action="{{ route('user.update', $user) }}">
            @csrf
            @method('PUT')
            @include('users._form', ['user' => $user])
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/users-create.js') }}?v={{ filemtime(public_path('js/users-create.js')) }}"></script>
@endpush
