@php($directoryStyle = $directoryStyle ?? false)
@php($showDepartment = $showDepartment ?? $directoryStyle)
@php($compact = $compact ?? false)
@php($hideLabels = $directoryStyle || $compact)

<section class="jf-search{{ $directoryStyle ? ' jf-search--directory' : ' jf-shell' }}{{ $compact ? ' jf-search--compact' : '' }}"
    id="job-search"
    @if ($compact) aria-label="Search jobs" @else aria-labelledby="search-title" @endif
    @if ($hideLabels) role="search" @endif>
    @if ($directoryStyle)
        <div class="jf-search__directory-shell jf-shell">
    @endif
        <div class="jf-search__panel">
            @unless ($hideLabels)
                <div class="jf-search__header">
                    <div>
                        <span class="jf-kicker">Find your fit</span>
                        <h2 id="search-title">Search opportunities</h2>
                    </div>
                    <p>Use a few details to narrow the list. You can combine multiple filters.</p>
                </div>
            @endunless

            <div class="jf-search__grid{{ $showDepartment ? ' jf-search__grid--with-department' : '' }}">
                <label class="jf-search__field jf-search__field--wide">
                    <span class="jf-search__label{{ $hideLabels ? ' visually-hidden' : '' }}">Role or company</span>
                    <span class="jf-search__control">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input id="job-search-input" type="search"
                            placeholder="{{ $hideLabels ? 'Search jobs by role or company' : 'e.g. Software engineer' }}"
                            autocomplete="off">
                    </span>
                </label>

                <label class="jf-search__field">
                    <span class="jf-search__label{{ $hideLabels ? ' visually-hidden' : '' }}">Job type</span>
                    <span class="jf-search__control jf-search__control--select">
                        <i class="fas fa-briefcase" aria-hidden="true"></i>
                        <select id="{{ $directoryStyle ? 'job-mode-select' : 'job-type-select' }}" aria-label="Job type">
                            <option value="all">All job types</option>
                            @foreach (\App\Models\JobPost::types() as $jobType)
                                <option value="{{ $jobType }}">{{ $jobType }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down jf-search__chevron" aria-hidden="true"></i>
                    </span>
                </label>

                <label class="jf-search__field">
                    <span class="jf-search__label{{ $hideLabels ? ' visually-hidden' : '' }}">Location</span>
                    <span class="jf-search__control jf-search__control--select">
                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                        <select id="job-location-input" aria-label="Location">
                            <option value="all">All locations</option>
                            @foreach (\App\Models\JobPost::locationOptions() as $jobLocation)
                                <option value="{{ $jobLocation }}">{{ $jobLocation }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down jf-search__chevron" aria-hidden="true"></i>
                    </span>
                </label>

                @if ($showDepartment)
                    <label class="jf-search__field">
                        <span class="jf-search__label{{ $hideLabels ? ' visually-hidden' : '' }}">Department</span>
                        <span class="jf-search__control jf-search__control--select">
                            <i class="fas fa-layer-group" aria-hidden="true"></i>
                            <select id="job-category-select" aria-label="Department">
                                <option value="all">All departments</option>
                                @foreach (\App\Models\JobPost::departmentOptions() as $jobDepartment)
                                    <option value="{{ $jobDepartment }}">{{ $jobDepartment }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down jf-search__chevron" aria-hidden="true"></i>
                        </span>
                    </label>
                @endif

                @if ($directoryStyle)
                    <button id="job-search-button" class="jf-search__directory-search" type="button">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        Search
                    </button>

                    <div class="jf-search__directory-actions" role="group" aria-label="Job directory view">
                        <button class="jf-search__directory-action is-primary" type="button"
                            aria-label="Show job cards" title="Show job cards" aria-pressed="true" data-job-view="grid">
                            <i class="fas fa-table-cells-large" aria-hidden="true"></i>
                        </button>
                        <button class="jf-search__directory-action" type="button"
                            aria-label="Show compact job list" title="Show compact job list" aria-pressed="false" data-job-view="list">
                            <i class="fas fa-list" aria-hidden="true"></i>
                        </button>
                    </div>

                    <button class="jf-search__directory-action" type="button" data-reset-search
                        aria-label="Clear all filters" title="Clear all filters">
                        <i class="fas fa-rotate-left" aria-hidden="true"></i>
                    </button>
                @elseif (! $compact)
                    <button id="job-search-button" class="jf-btn jf-btn--search" type="button">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        Search jobs
                    </button>
                @endif
            </div>

            @unless ($hideLabels)
                <div class="jf-search__footer">
                    <div class="jf-search__filters" aria-label="Quick filters">
                        <span>Popular:</span>
                        <button class="jf-search-chip" type="button" data-filter="remote" aria-pressed="false">Remote</button>
                        <button class="jf-search-chip" type="button" data-filter="full-time" aria-pressed="false">Full-time</button>
                        <button class="jf-search-chip" type="button" data-filter="entry-level" aria-pressed="false">Entry level</button>
                        <button class="jf-search-chip" type="button" data-filter="design" aria-pressed="false">Design</button>
                    </div>
                    <button class="jf-search__clear" id="jf-reset-search" type="button" data-reset-search>
                        <i class="fas fa-rotate-left" aria-hidden="true"></i> Clear filters
                    </button>
                </div>
            @endunless
        </div>
    @if ($directoryStyle)
        </div>
    @endif
</section>
