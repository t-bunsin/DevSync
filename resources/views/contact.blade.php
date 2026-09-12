@extends('layouts.master')

@section('title', 'Contact | ZIN-WORKS')
@section('meta-description', 'Talk to the ZIN-WORKS team about hiring, job applications, or partnerships. Phnom Penh office, phone, email, and a direct message form.')

@push('styles')
    {{-- jobs.css carries the frontend design tokens plus the shared header/footer
         theming. companies.css comes along for the form controls and the pill
         button this page borrows from the employer directory. --}}
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}?v={{ filemtime(public_path('css/companies.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}?v={{ filemtime(public_path('css/contact.css')) }}">
@endpush

@section('content')
    @php
        /*
         | Every value here comes from config/contact.php, so the office details
         | are edited in one place rather than in the markup.
         */
        $mapUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($details['map_query']);

        $channels = [
            [
                'icon' => 'fa-location-dot',
                'tone' => 'blue',
                'label' => 'Visit the office',
                'value' => $details['address'],
                'href' => $mapUrl,
                'action' => 'Get directions',
                'external' => true,
            ],
            [
                'icon' => 'fa-phone',
                'tone' => 'green',
                'label' => 'Call us',
                'value' => $details['phone'],
                'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $details['phone']),
                'action' => 'Start a call',
                'external' => false,
            ],
            [
                'icon' => 'fa-envelope',
                'tone' => 'purple',
                'label' => 'Email us',
                'value' => $details['email'],
                'href' => 'mailto:' . $details['email'],
                'action' => 'Write an email',
                'external' => false,
            ],
            [
                'icon' => 'fa-clock',
                'tone' => 'amber',
                'label' => 'Office hours',
                'value' => $details['hours'],
                'href' => null,
                'action' => null,
                'external' => false,
            ],
        ];

        // Keys are what ContactController::send() validates against; only the
        // label is presentation.
        $topics = [
            'hiring' => 'I want to hire on ZIN-WORKS',
            'job-seeking' => 'I am looking for a job',
            'partnership' => 'Partnership or media',
            'other' => 'Something else',
        ];

        $promises = [
            ['icon' => 'fa-bolt', 'text' => 'Replies within 1 business day'],
            ['icon' => 'fa-comments', 'text' => 'Khmer & English support'],
        ];
    @endphp

    {{-- One split panel instead of a hero stacked over cards: the details sit on
         the dark half, the form on the light half, and the two scroll as a pair.
         The page's own tokens throughout, so it still belongs to the site. --}}
    <section class="jf-ctc" aria-labelledby="contact-title">
        <div class="jf-shell jf-ctc__split">
            <aside class="jf-ctc-aside">
                <span class="jf-ctc-aside__curve" aria-hidden="true"></span>
                <span class="jf-ctc-aside__glow" aria-hidden="true"></span>

                <div class="jf-ctc-aside__top">
                    <span class="jf-ctc-aside__eyebrow">
                        <span class="jf-ctc-aside__eyebrow-dot" aria-hidden="true"></span>
                        Contact us
                    </span>

                    <h1 id="contact-title">Let’s get you to the <em>right person</em>.</h1>

                    <p>Hiring teams, candidates and partners all reach us here. Tell us what you need and the right person on the team will pick it up.</p>
                </div>

                <ul class="jf-ctc-channels">
                    @foreach ($channels as $channel)
                        <li class="jf-ctc-channel jf-ctc-channel--{{ $channel['tone'] }}">
                            {{-- The hours have nowhere to link to, so they render as
                                 the same row without the affordances of one. --}}
                            <{{ $channel['href'] ? 'a' : 'div' }}
                                class="jf-ctc-channel__face"
                                @if ($channel['href'])
                                    href="{{ $channel['href'] }}"
                                    @if ($channel['external']) target="_blank" rel="noopener" @endif
                                @endif>
                                <span class="jf-ctc-channel__icon" aria-hidden="true">
                                    <i class="fas {{ $channel['icon'] }}"></i>
                                </span>

                                <span class="jf-ctc-channel__text">
                                    <span class="jf-ctc-channel__label">{{ $channel['label'] }}</span>
                                    <span class="jf-ctc-channel__value">{{ $channel['value'] }}</span>
                                </span>

                                @if ($channel['href'])
                                    {{-- Names the link's purpose for a screen reader,
                                         which otherwise hears only label and value. --}}
                                    <span class="visually-hidden">— {{ $channel['action'] }}</span>
                                    <i class="fas fa-arrow-right jf-ctc-channel__go" aria-hidden="true"></i>
                                @endif
                            </{{ $channel['href'] ? 'a' : 'div' }}>
                        </li>
                    @endforeach
                </ul>

                <a class="jf-ctc-aside__telegram" href="{{ $details['telegram'] }}" target="_blank" rel="noopener">
                    <i class="fab fa-telegram" aria-hidden="true"></i>
                    <span>
                        <strong>Prefer to chat?</strong>
                        Message us on Telegram
                    </span>
                    <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                </a>
            </aside>

            <div class="jf-ctc-panel">
                <div class="jf-ctc-panel__head">
                    <div>
                        <span class="jf-kicker">Send a message</span>
                        <h2>Tell us what you need</h2>
                    </div>

                    <ul class="jf-ctc-promises">
                        @foreach ($promises as $promise)
                            <li><i class="fas {{ $promise['icon'] }}" aria-hidden="true"></i> {{ $promise['text'] }}</li>
                        @endforeach
                    </ul>
                </div>

                @if (session('contact_sent'))
                    <div class="jf-ctc-alert jf-ctc-alert--success" role="status">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <div>
                            <strong>Thanks, {{ session('contact_sent') }}.</strong>
                            <span>Your message is with us — expect a reply within one business day.</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="jf-ctc-alert jf-ctc-alert--error" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong>Please check the form.</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form class="jf-ctc-form" method="POST" action="{{ route('contact.send') }}" novalidate>
                    @csrf

                    <div class="jf-ctc-field">
                        <label for="contact-name">Full name</label>
                        <span class="jf-cdir-control">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            <input id="contact-name" name="name" type="text" value="{{ old('name') }}"
                                placeholder="e.g. Sokha Chan" autocomplete="name" required
                                @error('name') aria-invalid="true" @enderror>
                        </span>
                        @error('name')<small class="jf-ctc-field__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-ctc-field">
                        <label for="contact-email">Email</label>
                        <span class="jf-cdir-control">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <input id="contact-email" name="email" type="email" value="{{ old('email') }}"
                                placeholder="you@company.com" autocomplete="email" required
                                @error('email') aria-invalid="true" @enderror>
                        </span>
                        @error('email')<small class="jf-ctc-field__error">{{ $message }}</small>@enderror
                    </div>

                    {{-- The directory's select control, so this field matches the
                         two above it. With no old value the browser selects the
                         first option, which is the same default the radio set had. --}}
                    <div class="jf-ctc-field jf-ctc-field--wide">
                        <label for="contact-topic">What is this about?</label>
                        <span class="jf-cdir-control jf-cdir-control--select">
                            <i class="fas fa-tag" aria-hidden="true"></i>
                            <select id="contact-topic" name="topic" required
                                @error('topic') aria-invalid="true" @enderror>
                                @foreach ($topics as $value => $label)
                                    <option value="{{ $value }}" @selected(old('topic') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down jf-cdir-chevron" aria-hidden="true"></i>
                        </span>
                        @error('topic')<small class="jf-ctc-field__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-ctc-field jf-ctc-field--wide">
                        <label for="contact-message">Message</label>
                        <span class="jf-cdir-control jf-cdir-control--area">
                            <textarea id="contact-message" name="message" rows="6"
                                placeholder="Share the role you are hiring for, the job you applied to, or the partnership you have in mind." required
                                @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                        </span>
                        @error('message')<small class="jf-ctc-field__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-ctc-form__footer">
                        <button class="jf-cdir-submit" type="submit">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                            Send message
                        </button>
                        <p>We only use your details to answer this enquiry.</p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- "Find us": the map beside the office details.

         The map panel is deliberately capped at 680px. The Google embed only
         paints tiles across a frame up to roughly 700px wide — past that it
         leaves most of the frame grey — so a full-bleed band is not an option
         here however well it would suit the page. --}}
    <section class="jf-ctc-find" aria-labelledby="contact-find-title">
        <div class="jf-shell jf-ctc-find__grid">
            <figure class="jf-ctc-find__map">
                <iframe
                    src="https://www.google.com/maps?q={{ urlencode($details['map_query']) }}&z=16&hl=en&output=embed"
                    title="Map showing the ZIN-WORKS office in Phnom Penh"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
            </figure>

            <div class="jf-ctc-office">
                <span class="jf-ctc-office__pin" aria-hidden="true">
                    <i class="fas fa-map-pin"></i>
                </span>

                <span class="jf-kicker">Find us</span>
                <h2 id="contact-find-title">ZIN-WORKS office</h2>
                <p>{{ $details['address'] }}</p>

                <div class="jf-ctc-office__meta">
                    <span><i class="fas fa-clock" aria-hidden="true"></i> {{ $details['hours'] }}</span>
                    <span><i class="fas fa-phone" aria-hidden="true"></i> {{ $details['phone'] }}</span>
                </div>

                <a class="jf-cdir-submit jf-ctc-office__cta" href="{{ $mapUrl }}" target="_blank" rel="noopener">
                    <i class="fas fa-diamond-turn-right" aria-hidden="true"></i>
                    Get directions
                </a>
            </div>
        </div>
    </section>
@endsection
