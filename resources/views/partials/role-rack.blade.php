@php
    // Hanger styling cycles by position: resting tilt (deg), string length (px)
    // staggered so the cards hang at different heights, and card colour.
    $rackHangers = [
        ['tilt' => -2.4, 'drop' => 30, 'tone' => 'ink'],
        ['tilt' => 0.6, 'drop' => 104, 'tone' => 'slate'],
        ['tilt' => 2.8, 'drop' => 52, 'tone' => 'sky'],
        ['tilt' => -1, 'drop' => 136, 'tone' => 'ink'],
    ];

    $rackJobs = array_slice($spotlightJobs, 0, count($rackHangers));

    // With fewer jobs than hangers, the next free hanger carries a card to the
    // full board rather than leaving the rail looking unfinished.
    $rackShowsAll = count($rackJobs) < count($rackHangers);

    $postedBadge = fn (array $job) => match (true) {
        (int) ($job['posted_days'] ?? 99) <= 1 => 'New',
        default => (int) $job['posted_days'] . 'd ago',
    };
@endphp

<div class="jf-rack" data-rack>
    {{-- Arm the opening sequence before first paint, so the cards never flash
         in place and then vanish while role-rack.js loads. If that script
         never arrives (it marks the rack ready), the timeout shows the rack
         anyway; otherwise the script plays the intro once it is on screen. --}}
    <script>
        (() => {
            const rack = document.currentScript.parentElement;

            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                rack.classList.add('is-armed');
                window.setTimeout(() => {
                    if (!rack.dataset.rackReady) {
                        rack.classList.add('is-in', 'is-settled');
                    }
                }, 5000);
            }
        })();
    </script>
    <span class="jf-rack__rail" aria-hidden="true"></span>

    <ul class="jf-rack__list" aria-label="Featured jobs">
        @foreach ($rackJobs as $index => $job)
            @php
                $hanger = $rackHangers[$index];
                $applied = ! empty($job['already_applied']);
            @endphp

            <li class="jf-rack__item" style="--jf-rack-i: {{ $index }}; --jf-rack-tilt: {{ $hanger['tilt'] }}deg; --jf-rack-drop: {{ $hanger['drop'] }}px">
                <div class="jf-rack__swing" data-rack-swing data-tilt="{{ $hanger['tilt'] }}">
                    <span class="jf-rack__string" aria-hidden="true"></span>

                    <a class="jf-rack__card jf-rack__card--{{ $hanger['tone'] }}" href="{{ route('jobs.show', $job['id']) }}">
                        <span class="jf-rack__top">
                            <span @class(['jf-rack__logo', 'jf-rack__logo--photo' => ! empty($job['company_logo_url'])]) aria-hidden="true">
                                @if (! empty($job['company_logo_url']))
                                    <img src="{{ $job['company_logo_url'] }}" alt="">
                                @else
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($job['company'], 0, 1)) }}
                                @endif
                            </span>

                            @if ($job['featured'])
                                <span class="jf-rack__badge jf-rack__badge--hot"><i class="fas fa-bolt" aria-hidden="true"></i> Featured</span>
                            @else
                                <span class="jf-rack__badge">{{ $postedBadge($job) }}</span>
                            @endif
                        </span>

                        <span class="jf-rack__company">
                            {{ $job['company'] }}
                            @if (! empty($job['company_verified']))
                                <i class="fas fa-circle-check" aria-label="Verified employer"></i>
                            @endif
                        </span>
                        <span class="jf-rack__title">{{ $job['title'] }}</span>
                        <span class="jf-rack__meta">{{ $job['type'] }} · {{ $job['mode'] }}</span>
                        <span class="jf-rack__salary">{{ $job['short_salary'] }}</span>

                        @if ($applied)
                            <span class="jf-rack__cta jf-rack__cta--accent jf-rack__cta--done">
                                <i class="fas fa-check" aria-hidden="true"></i> Applied
                            </span>
                        @else
                            <span class="jf-rack__cta jf-rack__cta--{{ $hanger['tone'] === 'sky' ? 'dark' : 'light' }}">
                                Apply
                                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </span>
                        @endif
                    </a>
                </div>
            </li>
        @endforeach

        @if ($rackShowsAll)
            @php
                $index = count($rackJobs);
                $hanger = $rackHangers[$index];
            @endphp

            <li class="jf-rack__item" style="--jf-rack-i: {{ $index }}; --jf-rack-tilt: {{ $hanger['tilt'] }}deg; --jf-rack-drop: {{ $hanger['drop'] }}px">
                <div class="jf-rack__swing" data-rack-swing data-tilt="{{ $hanger['tilt'] }}">
                    <span class="jf-rack__string" aria-hidden="true"></span>

                    <a class="jf-rack__card jf-rack__card--more" href="{{ route('jobs.index') }}">
                        <span class="jf-rack__top">
                            <span class="jf-rack__logo jf-rack__logo--plus" aria-hidden="true"><i class="fas fa-plus"></i></span>
                        </span>

                        <span class="jf-rack__company">The full board</span>
                        <span class="jf-rack__title">All jobs</span>
                        <span class="jf-rack__meta">{{ count($jobs) }} open {{ \Illuminate\Support\Str::plural('role', count($jobs)) }}</span>

                        <span class="jf-rack__cta jf-rack__cta--light">
                            Browse
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
            </li>
        @endif
    </ul>

    <p class="jf-rack__hint" aria-hidden="true">
        <i class="fas fa-hand-pointer"></i> Swipe across the rail
    </p>
</div>
