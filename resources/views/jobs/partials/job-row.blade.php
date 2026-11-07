@php
    // A row on the /jobs board: one job in three short lines beside the
    // preview panel. The title is a button that previews the role in the
    // panel (jobs.js) and stretches over the whole row; save and the link to
    // the full job page sit above it on the right. The data attributes are
    // what jobs.js filters and sorts on.
    [$rowAmount, $rowUnit] = array_pad(array_map('trim', explode('/', $job['short_salary'], 2)), 2, null);

    // Everything the board's keyword search should match on.
    $rowSearch = array_merge([
        $job['title'], $job['company'], $job['location'], $job['department'],
        $job['type'], $job['mode'], $job['experience'], $job['summary'],
    ], $job['badges']);

    foreach ($job['tabs'] as $tab) {
        $rowSearch[] = $tab['body'];
        $rowSearch = array_merge($rowSearch, $tab['list']);
    }

    $rowActive = ! empty($job['highlighted']);
    $rowNew = (int) ($job['posted_days'] ?? 99) <= 1;
@endphp

<article
    @class(['jf-row', 'is-featured' => $job['featured'], 'is-active' => $rowActive])
    data-job-card
    data-job-id="{{ $job['id'] }}"
    data-title="{{ strtolower($job['title']) }}"
    data-company="{{ strtolower($job['company']) }}"
    data-location="{{ strtolower($job['location']) }}"
    data-type="{{ strtolower($job['type']) }}"
    data-department="{{ strtolower($job['department']) }}"
    data-mode="{{ strtolower($job['mode']) }}"
    data-experience="{{ strtolower($job['experience']) }}"
    data-posted-days="{{ $job['posted_days'] }}"
    data-featured="{{ $job['featured'] ? 1 : 0 }}"
    data-salary-rank="{{ preg_replace('/[^0-9]/', '', $job['salary']) }}"
    data-search="{{ strtolower(implode(' ', $rowSearch)) }}"
>
    <div @class([
        'jf-logo',
        'jf-row__logo',
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

    <div class="jf-row__main">
        <h3 class="jf-row__title">
            <button class="jf-row__link" type="button"
                data-preview-job="{{ $job['id'] }}"
                aria-controls="detail-panel-content"
                aria-pressed="{{ $rowActive ? 'true' : 'false' }}">{{ $job['title'] }}</button>
            @if ($job['featured'])
                <span class="jf-row__hot" title="Featured"><i class="fas fa-bolt" aria-hidden="true"></i><span class="visually-hidden">Featured</span></span>
            @elseif ($rowNew)
                <span class="jf-row__new">New</span>
            @endif
        </h3>

        <p class="jf-row__company">
            <span class="jf-row__name">{{ $job['company'] }}</span>
            @if (! empty($job['company_verified']))
                <x-verified-badge :show-label="false" :size="13" label="Verified employer" />
            @endif
            <span class="jf-row__place">{{ $job['location'] }}</span>
        </p>

        <p class="jf-row__meta">
            <span class="jf-row__pay"><strong>{{ $rowAmount }}</strong>@if ($rowUnit)<small>/{{ $rowUnit }}</small>@endif</span>
            <span>{{ $job['type'] }}</span>
            <span class="jf-row__mode">{{ $job['mode'] }}</span>
        </p>
    </div>

    <div class="jf-row__side">
        <button class="jf-bookmark" type="button" data-job-id="{{ $job['id'] }}"
            aria-label="Save {{ $job['title'] }}" aria-pressed="false">
            <i class="far fa-bookmark" aria-hidden="true"></i>
        </button>
        {{-- A real link, so every role on the board stays reachable without
             scripts (and by crawlers); the row itself previews. --}}
        <a class="jf-row__open" href="{{ route('jobs.show', $job['id']) }}"
            aria-label="Open the full {{ $job['title'] }} job page">
            <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
        </a>
    </div>
</article>
