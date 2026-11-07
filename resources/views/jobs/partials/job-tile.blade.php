@php
    // The landing page's job card: one link to the job page, five facts, no
    // controls. The /jobs board lists the same jobs as rows (job-row.blade.php).
    [$tileAmount, $tileUnit] = array_pad(array_map('trim', explode('/', $job['short_salary'], 2)), 2, null);

    $tilePosted = match (true) {
        (int) ($job['posted_days'] ?? 99) <= 1 => 'New',
        default => (int) $job['posted_days'] . 'd ago',
    };
@endphp

<article @class(['jf-tile', 'is-featured' => $job['featured']])>
    <div class="jf-tile__head">
        <div @class([
            'jf-logo',
            'jf-tile__logo',
            'jf-logo--' . $job['logo'],
            'jf-logo--photo' => ! empty($job['company_logo_url']),
        ]) aria-hidden="true">
            @if (! empty($job['company_logo_url']))
                <img src="{{ $job['company_logo_url'] }}" alt="">
            @elseif ($job['logo'] === 'aba')
                <span>ABA</span><small>BANK</small>
            @elseif ($job['logo'] === 'tech')
                <i class="fas fa-wifi"></i>
            @else
                <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($job['company'], 0, 1)) }}</span>
            @endif
        </div>

        <div class="jf-tile__who">
            <p class="jf-tile__company">
                <span>{{ $job['company'] }}</span>
                @if (! empty($job['company_verified']))
                    <x-verified-badge :show-label="false" :size="14" label="Verified employer" />
                @endif
            </p>
            <p class="jf-tile__place">{{ $job['location'] }}</p>
        </div>

        @if ($job['featured'])
            <span class="jf-tile__tag jf-tile__tag--hot"><i class="fas fa-bolt" aria-hidden="true"></i> Featured</span>
        @else
            <span class="jf-tile__tag">{{ $tilePosted }}</span>
        @endif
    </div>

    {{-- The title's link stretches over the whole card, so the card is one
         click target without wrapping block content in an <a>. --}}
    <h3 class="jf-tile__title">
        <a class="jf-tile__link" href="{{ route('jobs.show', $job['id']) }}">{{ $job['title'] }}</a>
    </h3>
    <p class="jf-tile__summary">{{ $job['summary'] }}</p>

    <ul class="jf-tile__facts" aria-label="Job details">
        <li>{{ $job['type'] }}</li>
        <li>{{ $job['mode'] }}</li>
        <li>{{ $job['experience'] }}</li>
    </ul>

    <div class="jf-tile__foot">
        <p class="jf-tile__pay">
            <strong>{{ $tileAmount }}</strong>
            @if ($tileUnit)
                <span>/ {{ $tileUnit }}</span>
            @endif
        </p>
        <span class="jf-tile__go" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
    </div>
</article>
