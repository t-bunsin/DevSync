{{--
    A page-number strip in the kh-bo idiom (pill buttons, ghost styling)
    instead of Laravel's default Tailwind/Bootstrap markup, which this admin
    shell doesn't load. Expects $paginator, a LengthAwarePaginator.
--}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $window = 1;

        // First, last, current ± $window, and a "…" wherever that leaves a gap.
        $pages = collect(range(1, $last))->filter(
            fn ($page) => $page === 1 || $page === $last || abs($page - $current) <= $window
        )->values();
    @endphp

    <nav class="kh-bo__pagination" aria-label="Pagination">
        <a class="kh-bo__pagination-btn{{ $paginator->onFirstPage() ? ' is-disabled' : '' }}"
            @if (! $paginator->onFirstPage()) href="{{ $paginator->previousPageUrl() }}" @endif
            aria-label="Previous page" @if ($paginator->onFirstPage()) aria-disabled="true" @endif>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M15 6l-6 6 6 6" />
            </svg>
        </a>

        <div class="kh-bo__pagination-pages">
            @php $previous = 0; @endphp
            @foreach ($pages as $page)
                @if ($page - $previous > 1)
                    <span class="kh-bo__pagination-ellipsis" aria-hidden="true">&hellip;</span>
                @endif

                <a class="kh-bo__pagination-page{{ $page === $current ? ' is-active' : '' }}"
                    href="{{ $paginator->url($page) }}"
                    @if ($page === $current) aria-current="page" @endif>
                    {{ $page }}
                </a>

                @php $previous = $page; @endphp
            @endforeach
        </div>

        <a class="kh-bo__pagination-btn{{ $paginator->hasMorePages() ? '' : ' is-disabled' }}"
            @if ($paginator->hasMorePages()) href="{{ $paginator->nextPageUrl() }}" @endif
            aria-label="Next page" @if (! $paginator->hasMorePages()) aria-disabled="true" @endif>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 6l6 6-6 6" />
            </svg>
        </a>

        <span class="kh-bo__pagination-summary">
            {{ number_format($paginator->total()) }} {{ \Illuminate\Support\Str::plural('result', $paginator->total()) }}
        </span>
    </nav>
@endif
