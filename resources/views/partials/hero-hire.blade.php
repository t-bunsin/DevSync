@php
    // Real role titles from the board when there are enough, so the post card
    // rolls through the kind of jobs that actually get listed here.
    $hireTitles = collect($spotlightJobs ?? [])->pluck('title')->filter()->unique()->take(3)->values()->all();

    if (count($hireTitles) < 3) {
        $hireTitles = ['Sales lead', 'Backend developer', 'Accountant'];
    }

    // The pipeline cast. `lane` and `row` are where each chip starts; the
    // moving ones are choreographed in hero-slides.css by their modifier.
    $hireChips = [
        ['name' => 'Sokha', 'role' => '5 yrs exp.', 'tone' => 'teal', 'mod' => 'stay'],
        ['name' => 'Dara', 'role' => 'Top match', 'tone' => 'gold', 'mod' => 'hire'],
        ['name' => 'Vanna', 'role' => 'Just applied', 'tone' => 'coral', 'mod' => 'new'],
        ['name' => 'Lina', 'role' => 'Interview', 'tone' => 'sky', 'mod' => 'short'],
    ];
@endphp

{{-- The employer's view of the same marketplace: a post goes live, then
     applicants flow across a three-lane board until one is hired. Purely
     decorative; the slide copy carries the message. --}}
<div class="jf-hire" aria-hidden="true">
    <article class="jf-hire__post">
        <span class="jf-hire__pin"></span>

        <span class="jf-hire__post-top">
            <span class="jf-hire__post-logo"><i class="fas fa-building"></i></span>
            <span class="jf-hire__post-state">
                <span class="jf-hire__post-draft">Draft</span>
                <span class="jf-hire__post-live"><i class="fas fa-bolt"></i> Live</span>
            </span>
        </span>

        <span class="jf-hire__post-company">Your company</span>
        {{-- The first title repeats at the end so the reel can loop without
             a visible jump back to the top. --}}
        <span class="jf-hire__post-roll">
            <span class="jf-hire__post-reel">
                @foreach ([...$hireTitles, $hireTitles[0]] as $title)
                    <span class="jf-hire__post-title">{{ $title }}</span>
                @endforeach
            </span>
        </span>
        <span class="jf-hire__post-meta">Full-time · Phnom Penh</span>

        <span class="jf-hire__post-cta">
            <span class="jf-hire__post-publish">Publish</span>
            <span class="jf-hire__post-done"><i class="fas fa-check"></i> Published</span>
        </span>
    </article>

    <div class="jf-hire__board">
        <div class="jf-hire__head">
            <span class="jf-hire__live"><i></i> Hiring board</span>
            <span class="jf-hire__views"><i class="fas fa-eye"></i> 1.4k views</span>
        </div>

        <div class="jf-hire__lanes">
            <div class="jf-hire__lane">
                <span class="jf-hire__lane-name">Applied</span>
                <span class="jf-hire__lane-count">12<b>+1</b></span>
            </div>
            <div class="jf-hire__lane">
                <span class="jf-hire__lane-name">Shortlist</span>
                <span class="jf-hire__lane-count">4</span>
            </div>
            <div class="jf-hire__lane jf-hire__lane--hired">
                <span class="jf-hire__lane-name">Hired</span>
                <span class="jf-hire__lane-count"><i class="fas fa-trophy"></i></span>
            </div>
        </div>

        <div class="jf-hire__field">
            <span class="jf-hire__slot">Your next hire</span>

            @foreach ($hireChips as $chip)
                <span class="jf-hire__chip jf-hire__chip--{{ $chip['mod'] }}">
                    <i class="jf-hire__avatar jf-hire__avatar--{{ $chip['tone'] }}">{{ \Illuminate\Support\Str::substr($chip['name'], 0, 1) }}</i>
                    <span class="jf-hire__who">
                        <b>{{ $chip['name'] }}</b>
                        <small>{{ $chip['role'] }}</small>
                    </span>
                    @if ($chip['mod'] === 'hire')
                        <span class="jf-hire__tick"><i class="fas fa-check"></i></span>
                    @endif
                </span>
            @endforeach

            <span class="jf-hire__burst">
                @for ($i = 0; $i < 8; $i++)
                    <i style="--jf-burst-i: {{ $i }}"></i>
                @endfor
            </span>
        </div>
    </div>

    <div class="jf-hire__toast jf-hire__toast--applied">
        <span class="jf-hire__toast-icon"><i class="fas fa-bell"></i></span>
        <span><b>New applicant</b><small>Vanna applied · just now</small></span>
    </div>

    <div class="jf-hire__toast jf-hire__toast--hired">
        <span class="jf-hire__toast-icon"><i class="fas fa-handshake"></i></span>
        <span><b>Offer accepted</b><small>Dara starts Monday</small></span>
    </div>
</div>
