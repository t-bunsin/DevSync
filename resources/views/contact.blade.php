@extends('layouts.master')

@section('title', 'Contact | ZIN-WORKS')
@section('meta-description', 'Talk to the ZIN-WORKS team about hiring, job applications, or partnerships. Phnom Penh office, phone, email, and a direct message form.')

@push('styles')
    {{-- jobs.css carries the frontend design tokens plus the shared header/footer theming. --}}
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}?v={{ filemtime(public_path('css/contact.css')) }}">
@endpush

@section('content')
    @php
        $channels = [
            [
                'icon' => 'fa-location-dot',
                'label' => 'Visit the office',
                'value' => $details['address'],
                'href' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($details['map_query']),
                'action' => 'Get directions',
                'external' => true,
            ],
            [
                'icon' => 'fa-phone',
                'label' => 'Call us',
                'value' => $details['phone'],
                'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $details['phone']),
                'action' => 'Start a call',
                'external' => false,
            ],
            [
                'icon' => 'fa-envelope',
                'label' => 'Email us',
                'value' => $details['email'],
                'href' => 'mailto:' . $details['email'],
                'action' => 'Write an email',
                'external' => false,
            ],
            [
                'icon' => 'fa-clock',
                'label' => 'Office hours',
                'value' => $details['hours'],
                'href' => null,
                'action' => null,
                'external' => false,
            ],
        ];

        $topics = [
            'hiring' => 'I want to hire on ZIN-WORKS',
            'job-seeking' => 'I am looking for a job',
            'partnership' => 'Partnership or media',
            'other' => 'Something else',
        ];
    @endphp

    <section class="jf-contact-hero" aria-labelledby="contact-title">
        <div class="jf-contact-hero__glow jf-contact-hero__glow--one" aria-hidden="true"></div>
        <div class="jf-contact-hero__glow jf-contact-hero__glow--two" aria-hidden="true"></div>

        <div class="jf-shell jf-contact-hero__inner">
            <span class="jf-contact-hero__eyebrow">Contact us</span>
            <h1 id="contact-title">Let’s get you to the <span>right person.</span></h1>
            <p>Hiring teams, candidates, and partners all reach us here. Tell us what you need and the right person on the team will pick it up.</p>

            <ul class="jf-contact-hero__chips">
                <li><i class="fas fa-bolt" aria-hidden="true"></i> Replies within 1 business day</li>
                <li><i class="fas fa-comments" aria-hidden="true"></i> Khmer &amp; English support</li>
                <li><i class="fas fa-building" aria-hidden="true"></i> Phnom Penh office</li>
            </ul>
        </div>
    </section>

    <section class="jf-contact" aria-label="Contact details and message form">
        <div class="jf-shell jf-contact__grid">
            <aside class="jf-contact__aside">
                <h2 class="jf-contact__aside-title">Our contact</h2>

                <ul class="jf-contact__channels">
                    @foreach ($channels as $channel)
                        <li class="jf-channel">
                            <span class="jf-channel__icon" aria-hidden="true"><i class="fas {{ $channel['icon'] }}"></i></span>
                            <div class="jf-channel__body">
                                <span class="jf-channel__label">{{ $channel['label'] }}</span>
                                <p class="jf-channel__value">{{ $channel['value'] }}</p>
                                @if ($channel['href'])
                                    <a class="jf-channel__action" href="{{ $channel['href'] }}"
                                        @if ($channel['external']) target="_blank" rel="noopener" @endif>
                                        {{ $channel['action'] }}
                                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <figure class="jf-contact__map">
                    <iframe
                        src="https://www.google.com/maps?q={{ urlencode($details['map_query']) }}&z=16&hl=en&output=embed"
                        title="Map showing the ZIN-WORKS office in Phnom Penh"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
                    <figcaption>
                        <span><i class="fas fa-map-pin" aria-hidden="true"></i> ZIN-WORKS office</span>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($details['map_query']) }}"
                            target="_blank" rel="noopener">
                            Open in Maps <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </a>
                    </figcaption>
                </figure>
            </aside>

            <div class="jf-contact__panel">
                <div class="jf-contact__panel-head">
                    <span class="jf-kicker">Send a message</span>
                    <h2>Tell us what you need</h2>
                    <p>Add a little context and we will route your message to the right team.</p>
                </div>

                @if (session('contact_sent'))
                    <div class="jf-contact__alert jf-contact__alert--success" role="status">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <div>
                            <strong>Thanks, {{ session('contact_sent') }}.</strong>
                            <span>Your message is with us — expect a reply within one business day.</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="jf-contact__alert jf-contact__alert--error" role="alert">
                        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong>Please check the form.</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form class="jf-contact__form" method="POST" action="{{ route('contact.send') }}" novalidate>
                    @csrf

                    <div class="jf-field">
                        <label for="contact-name">Full name</label>
                        <input id="contact-name" name="name" type="text" value="{{ old('name') }}"
                            placeholder="e.g. Sokha Chan" autocomplete="name" required
                            @error('name') aria-invalid="true" @enderror>
                        @error('name')<small class="jf-field__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-field">
                        <label for="contact-email">Email</label>
                        <input id="contact-email" name="email" type="email" value="{{ old('email') }}"
                            placeholder="you@company.com" autocomplete="email" required
                            @error('email') aria-invalid="true" @enderror>
                        @error('email')<small class="jf-field__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-field jf-field--wide">
                        <label for="contact-topic">What is this about?</label>
                        <div class="jf-field__select">
                            <select id="contact-topic" name="topic" required>
                                @foreach ($topics as $value => $label)
                                    <option value="{{ $value }}" @selected(old('topic') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        </div>
                        @error('topic')<small class="jf-field__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-field jf-field--wide">
                        <label for="contact-message">Message</label>
                        <textarea id="contact-message" name="message" rows="6"
                            placeholder="Share the role you are hiring for, the job you applied to, or the partnership you have in mind." required
                            @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                        @error('message')<small class="jf-field__error">{{ $message }}</small>@enderror
                    </div>

                    <div class="jf-contact__form-footer">
                        <button class="jf-btn jf-btn--accent" type="submit">
                            Send message
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        </button>
                        <p>Prefer chat? <a href="{{ $details['telegram'] }}" target="_blank" rel="noopener">Message us on Telegram</a>.</p>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
