@php($directoryStyle = $directoryStyle ?? false)

<section class="jf-search{{ $directoryStyle ? ' jf-search--directory' : ' jf-shell' }}" id="job-search"
    aria-labelledby="search-title" @if ($directoryStyle) role="search" @endif>
    @if ($directoryStyle)
        <div class="jf-search__directory-shell jf-shell">
    @endif
        <div class="jf-search__panel">
            @unless ($directoryStyle)
                <div class="jf-search__header">
                    <div>
                        <span class="jf-kicker">Find your fit</span>
                        <h2 id="search-title">Search opportunities</h2>
                    </div>
                    <p>Use a few details to narrow the list. You can combine multiple filters.</p>
                </div>
            @endunless

            <div class="jf-search__grid">
                <label class="jf-search__field jf-search__field--wide">
                    <span class="jf-search__label{{ $directoryStyle ? ' visually-hidden' : '' }}">Role or company</span>
                    <span class="jf-search__control">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input id="job-search-input" type="search"
                            placeholder="{{ $directoryStyle ? 'Search jobs by role or company' : 'e.g. Software engineer' }}"
                            autocomplete="off">
                    </span>
                </label>

                <label class="jf-search__field">
                    <span class="jf-search__label{{ $directoryStyle ? ' visually-hidden' : '' }}">Location</span>
                    <span class="jf-search__control">
                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                        <input id="job-location-input" type="search"
                            placeholder="{{ $directoryStyle ? 'All locations' : 'City or remote' }}"
                            autocomplete="off">
                    </span>
                </label>

                <label class="jf-search__field">
                    <span class="jf-search__label{{ $directoryStyle ? ' visually-hidden' : '' }}">Department</span>
                    <span class="jf-search__control jf-search__control--select">
                        <i class="fas fa-layer-group" aria-hidden="true"></i>
                        <select id="job-category-select" aria-label="Department">
                            <option value="all">All departments</option>
                            <option value="engineering">Engineering</option>
                            <option value="product design">Product Design</option>
                            <option value="retail banking">Retail Banking</option>
                        </select>
                        <i class="fas fa-chevron-down jf-search__chevron" aria-hidden="true"></i>
                    </span>
                </label>

                @if ($directoryStyle)
                    <label class="jf-search__field">
                        <span class="jf-search__label visually-hidden">Work mode</span>
                        <span class="jf-search__control jf-search__control--select">
                            <i class="fas fa-briefcase" aria-hidden="true"></i>
                            <select id="job-mode-select" aria-label="Work mode">
                                <option value="all">All work modes</option>
                                <option value="remote">Remote</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="on-site">On-site</option>
                            </select>
                            <i class="fas fa-chevron-down jf-search__chevron" aria-hidden="true"></i>
                        </span>
                    </label>

                    <div class="jf-search__directory-actions" role="group" aria-label="Search actions">
                        <button id="job-search-button" class="jf-search__directory-action is-primary" type="button"
                            aria-label="Search jobs" title="Search jobs">
                            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        </button>
                        <button class="jf-search__directory-action" id="jf-reset-search" type="button"
                            aria-label="Clear all filters" title="Clear all filters">
                            <i class="fas fa-rotate-left" aria-hidden="true"></i>
                        </button>
                    </div>
                @else
                    <button id="job-search-button" class="jf-btn jf-btn--search" type="button">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        Search jobs
                    </button>
                @endif
            </div>

            @unless ($directoryStyle)
                <div class="jf-search__footer">
                    <div class="jf-search__filters" aria-label="Quick filters">
                        <span>Popular:</span>
                        <button class="jf-search-chip" type="button" data-filter="remote" aria-pressed="false">Remote</button>
                        <button class="jf-search-chip" type="button" data-filter="full-time" aria-pressed="false">Full-time</button>
                        <button class="jf-search-chip" type="button" data-filter="entry-level" aria-pressed="false">Entry level</button>
                        <button class="jf-search-chip" type="button" data-filter="design" aria-pressed="false">Design</button>
                    </div>
                    <button class="jf-search__clear" id="jf-reset-search" type="button">
                        <i class="fas fa-rotate-left" aria-hidden="true"></i> Clear filters
                    </button>
                </div>
            @endunless
        </div>
    @if ($directoryStyle)
        </div>
    @endif
</section>
