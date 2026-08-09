@extends('layouts.master')

@section('title', 'KH-WORKS | Find Your Next Opportunity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}">
@endpush

@section('content')
    <section class="jf-hero" aria-labelledby="hero-title">
        <div class="jf-hero__glow jf-hero__glow--one" aria-hidden="true"></div>
        <div class="jf-hero__glow jf-hero__glow--two" aria-hidden="true"></div>

        <div class="jf-shell jf-hero__inner">
            <div class="jf-hero__copy">
                <span class="jf-hero__eyebrow">
                    <i class="fas fa-sparkles" aria-hidden="true"></i>
                    Cambodia’s career marketplace
                </span>
                <h1 id="hero-title">Find work that moves <span>you forward.</span></h1>
                <p>Explore verified opportunities from trusted teams in Cambodia and remote-first companies across Southeast Asia.</p>

                <div class="jf-hero__actions">
                    <a class="jf-btn jf-btn--accent" href="#job-search">
                        Explore jobs
                        <i class="fas fa-arrow-down" aria-hidden="true"></i>
                    </a>
                    <a class="jf-btn jf-btn--hero-ghost" href="{{ route('register') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Post a job
                    </a>
                </div>

                <dl class="jf-hero__metrics" aria-label="Platform statistics">
                    <div><dt>1,200+</dt><dd>Active roles</dd></div>
                    <div><dt>320+</dt><dd>Hiring teams</dd></div>
                    <div><dt>3 days</dt><dd>Avg. response</dd></div>
                </dl>
            </div>

            <aside class="jf-spotlight" aria-label="Featured opportunity">
                <div class="jf-spotlight__topline">
                    <span><i class="fas fa-bolt" aria-hidden="true"></i> Featured this week</span>
                    <span class="jf-spotlight__status">Actively hiring</span>
                </div>

                <div class="jf-spotlight__company">
                    <div class="jf-logo jf-logo--tech"><i class="fas fa-wifi" aria-hidden="true"></i></div>
                    <div>
                        <span>Tech Corp</span>
                        <strong>Software Engineer</strong>
                    </div>
                </div>

                <div class="jf-spotlight__meta">
                    <span><i class="fas fa-location-dot" aria-hidden="true"></i> Remote</span>
                    <span><i class="fas fa-clock" aria-hidden="true"></i> Full-time</span>
                    <span><i class="fas fa-code" aria-hidden="true"></i> Engineering</span>
                </div>

                <div class="jf-spotlight__footer">
                    <div><small>Salary range</small><strong>$80k–$120k</strong></div>
                    <a href="#jobs" data-view-job="software-engineer">View role <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </aside>
        </div>
    </section>

    <section class="jf-job-ticker" aria-label="Popular job categories">
        <p class="visually-hidden">Popular job categories include software engineering, product design, retail banking, data and analytics, customer support, marketing, finance, and remote work.</p>
        <div class="jf-job-ticker__viewport" aria-hidden="true">
            <div class="jf-job-ticker__track">
                @for ($tickerCopy = 0; $tickerCopy < 2; $tickerCopy++)
                    <div class="jf-job-ticker__group">
                        <span>Software Engineering</span>
                        <span>Product Design</span>
                        <span>Retail Banking</span>
                        <span>Data &amp; Analytics</span>
                        <span>Customer Support</span>
                        <span>Digital Marketing</span>
                        <span>Finance</span>
                        <span>Remote Roles</span>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <div data-jobs-explorer>
        @include('jobs.partials.search')

        <section class="jf-trusted" id="companies" aria-labelledby="companies-title">
            <div class="jf-shell jf-trusted__inner">
                <p id="companies-title">Opportunities from teams shaping Cambodia</p>
                <div class="jf-trusted__logos" aria-label="Featured employers">
                    <span><b>ABA</b> Bank</span>
                    <span><i class="fas fa-signal" aria-hidden="true"></i> Smart</span>
                    <span><i class="fas fa-circle-nodes" aria-hidden="true"></i> Wing</span>
                    <span><i class="fas fa-building" aria-hidden="true"></i> Chip Mong</span>
                    <span><i class="fas fa-shield-heart" aria-hidden="true"></i> AIA</span>
                </div>
            </div>
        </section>

        @include('jobs.partials.catalog')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/jobs.js') }}"></script>
@endpush
