{{-- Shared by create and edit. $post is an empty model on create. --}}
@php
    $isEdit = $post->exists;
    $tabs = $post->tabs ?: [];

    // Bullet lists round-trip as one item per line.
    $listValue = function (string $key) use ($tabs) {
        return implode("\n", $tabs[$key]['list'] ?? []);
    };

    $panelFields = function (string $key, string $label) use ($tabs, $listValue) {
        return compact('key', 'label');
    };
@endphp

@if ($errors->any())
    <div class="kh-bo__errors" role="alert">
        <strong>Please check the highlighted fields.</strong>
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="kh-bo__form-card" data-bo-tabs>
    <div class="kh-bo__tabs" role="tablist" aria-label="Job post sections">
        <button class="kh-bo__tab" type="button" role="tab" data-bo-tab="overview"
            aria-selected="true" aria-controls="bo-panel-overview">Overview</button>
        <button class="kh-bo__tab" type="button" role="tab" data-bo-tab="description"
            aria-selected="false" aria-controls="bo-panel-description">Job description</button>
        <button class="kh-bo__tab" type="button" role="tab" data-bo-tab="requirements"
            aria-selected="false" aria-controls="bo-panel-requirements">Requirements</button>
    </div>

    {{-- Panels are hidden, never removed, so every field still submits. --}}
    <div class="kh-bo__panel" data-bo-panel="overview" id="bo-panel-overview" role="tabpanel">
        <div class="kh-bo__grid">

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="title">Job title <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('title')])
                id="title" name="title" type="text" maxlength="255" required
                value="{{ old('title', $post->title) }}" placeholder="e.g. Software Engineer">
            @error('title') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="company_id">Company <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('company_id')])
                id="company_id" name="company_id" required>
                <option value="">Choose a company…</option>
                @foreach ($companies as $option)
                    <option value="{{ $option->id }}" @selected((int) old('company_id', $post->company_id) === $option->id)>
                        {{ $option->name }}
                    </option>
                @endforeach
            </select>
            <span class="kh-bo__hint">
                Only approved companies appear here.
                <a href="{{ route('companies.create') }}">Add a company</a> if it is missing.
            </span>
            @error('company_id') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="location">Location <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('location')])
                id="location" name="location" type="text" maxlength="255" required
                value="{{ old('location', $post->location) }}" placeholder="e.g. Phnom Penh, Cambodia">
            @error('location') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="slug">URL slug</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('slug')])
                id="slug" name="slug" type="text" maxlength="255"
                value="{{ old('slug', $post->slug) }}" placeholder="software-engineer">
            <span class="kh-bo__hint">Leave blank to build it from the title. Lives at /jobs/&lt;slug&gt;.</span>
            @error('slug') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="type">Job type <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('type')]) id="type" name="type" required>
                @foreach (\App\Models\JobPost::types() as $type)
                    <option value="{{ $type }}" @selected(old('type', $post->type ?? 'Full-time') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="mode">Work mode <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('mode')]) id="mode" name="mode" required>
                @foreach (\App\Models\JobPost::modes() as $mode)
                    <option value="{{ $mode }}" @selected(old('mode', $post->mode ?? 'On-site') === $mode)>{{ $mode }}</option>
                @endforeach
            </select>
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="experience">Experience</label>
            <input class="kh-bo__control" id="experience" name="experience" type="text" maxlength="60"
                value="{{ old('experience', $post->experience) }}" placeholder="e.g. 3+ Years">
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="department">Department</label>
            <input class="kh-bo__control" id="department" name="department" type="text" maxlength="80"
                value="{{ old('department', $post->department) }}" placeholder="e.g. Engineering">
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="salary">Salary (full)</label>
            <input class="kh-bo__control" id="salary" name="salary" type="text" maxlength="255"
                value="{{ old('salary', $post->salary) }}" placeholder="$80,000 - $120,000 a year">
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="short_salary">Salary (short)</label>
            <input class="kh-bo__control" id="short_salary" name="short_salary" type="text" maxlength="255"
                value="{{ old('short_salary', $post->short_salary) }}" placeholder="$80k - $120k / year">
            <span class="kh-bo__hint">Used on the compact job cards.</span>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__label" for="summary">Summary</label>
            <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('summary')])
                id="summary" name="summary" maxlength="1000" style="min-height: 76px;"
                placeholder="One or two lines shown on the job cards.">{{ old('summary', $post->summary) }}</textarea>
            @error('summary') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="deadline">Application deadline</label>
            <input @class(['kh-bo__control', 'is-invalid' => $errors->has('deadline')])
                id="deadline" name="deadline" type="date"
                value="{{ old('deadline', $post->deadline?->format('Y-m-d')) }}">
            <span class="kh-bo__hint">Blank shows “Open until filled”.</span>
            @error('deadline') <span class="kh-bo__error">{{ $message }}</span> @enderror
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="applicants">Applicant count</label>
            <input class="kh-bo__control" id="applicants" name="applicants" type="number" min="0"
                value="{{ old('applicants', $post->applicants ?? 0) }}">
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="logo">Card artwork</label>
            <select class="kh-bo__control" id="logo" name="logo" required>
                @foreach (\App\Models\JobPost::logos() as $logo)
                    <option value="{{ $logo }}" @selected(old('logo', $post->logo ?? 'default') === $logo)>{{ ucfirst($logo) }}</option>
                @endforeach
            </select>
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="status">Status <span class="kh-bo__required" aria-hidden="true">*</span></label>
            <select @class(['kh-bo__control', 'is-invalid' => $errors->has('status')]) id="status" name="status" required>
                @foreach (\App\Models\JobPost::statuses() as $status)
                    <option value="{{ $status }}" @selected(old('status', $post->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <span class="kh-bo__hint">Only published posts appear on the public site.</span>
        </div>

        <div class="kh-bo__field kh-bo__field--wide">
            <label class="kh-bo__checkbox">
                <input type="checkbox" name="featured" value="1" @checked(old('featured', $post->featured))>
                Featured — sorted to the top of the jobs list
            </label>
            <label class="kh-bo__checkbox">
                <input type="checkbox" name="highlighted" value="1" @checked(old('highlighted', $post->highlighted))>
                Spotlight — the role the explorer opens on (only one post can hold this)
            </label>
        </div>
        </div>
    </div>

    <div class="kh-bo__panel" data-bo-panel="description" id="bo-panel-description" role="tabpanel" hidden>
        <div class="kh-bo__grid">
            <div class="kh-bo__section-label">
                Overview
                <span>The first tab on the public job page.</span>
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="tabs_description_title">Heading</label>
                <input class="kh-bo__control" id="tabs_description_title" name="tabs[description][title]" type="text"
                    value="{{ old("tabs.description.title", $tabs['description']['title'] ?? '') }}" placeholder="Section heading">
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="tabs_description_list_title">List heading</label>
                <input class="kh-bo__control" id="tabs_description_list_title" name="tabs[description][list_title]" type="text"
                    value="{{ old("tabs.description.list_title", $tabs['description']['list_title'] ?? '') }}" placeholder="e.g. What You Will Do">
            </div>

            <div class="kh-bo__field kh-bo__field--wide">
                <label class="kh-bo__label" for="tabs_description_body">Paragraph</label>
                <textarea class="kh-bo__control" id="tabs_description_body" name="tabs[description][body]"
                    placeholder="A short paragraph.">{{ old("tabs.description.body", $tabs['description']['body'] ?? '') }}</textarea>
            </div>

            <div class="kh-bo__field kh-bo__field--wide">
                <label class="kh-bo__label" for="tabs_description_list">Bullet points</label>
                <textarea class="kh-bo__control" id="tabs_description_list" name="tabs[description][list]"
                    placeholder="One per line.">{{ old("tabs.description.list", $listValue('description')) }}</textarea>
                <span class="kh-bo__hint">One bullet per line.</span>
            </div>

            <div class="kh-bo__section-label">
                Job description
                <span>Extra role copy, shown under the Requirements tab.</span>
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="tabs_job_description_title">Heading</label>
                <input class="kh-bo__control" id="tabs_job_description_title" name="tabs[job_description][title]" type="text"
                    value="{{ old("tabs.job_description.title", $tabs['job_description']['title'] ?? '') }}" placeholder="Section heading">
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="tabs_job_description_list_title">List heading</label>
                <input class="kh-bo__control" id="tabs_job_description_list_title" name="tabs[job_description][list_title]" type="text"
                    value="{{ old("tabs.job_description.list_title", $tabs['job_description']['list_title'] ?? '') }}" placeholder="e.g. What You Will Do">
            </div>

            <div class="kh-bo__field kh-bo__field--wide">
                <label class="kh-bo__label" for="tabs_job_description_body">Paragraph</label>
                <textarea class="kh-bo__control" id="tabs_job_description_body" name="tabs[job_description][body]"
                    placeholder="A short paragraph.">{{ old("tabs.job_description.body", $tabs['job_description']['body'] ?? '') }}</textarea>
            </div>

            <div class="kh-bo__field kh-bo__field--wide">
                <label class="kh-bo__label" for="tabs_job_description_list">Bullet points</label>
                <textarea class="kh-bo__control" id="tabs_job_description_list" name="tabs[job_description][list]"
                    placeholder="One per line.">{{ old("tabs.job_description.list", $listValue('job_description')) }}</textarea>
                <span class="kh-bo__hint">One bullet per line.</span>
            </div>
        </div>
    </div>

    <div class="kh-bo__panel" data-bo-panel="requirements" id="bo-panel-requirements" role="tabpanel" hidden>
        <div class="kh-bo__grid">
            <div class="kh-bo__section-label">
                Requirements
                <span>The second tab on the public job page.</span>
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="tabs_requirements_title">Heading</label>
                <input class="kh-bo__control" id="tabs_requirements_title" name="tabs[requirements][title]" type="text"
                    value="{{ old("tabs.requirements.title", $tabs['requirements']['title'] ?? '') }}" placeholder="Section heading">
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="tabs_requirements_list_title">List heading</label>
                <input class="kh-bo__control" id="tabs_requirements_list_title" name="tabs[requirements][list_title]" type="text"
                    value="{{ old("tabs.requirements.list_title", $tabs['requirements']['list_title'] ?? '') }}" placeholder="e.g. What You Will Do">
            </div>

            <div class="kh-bo__field kh-bo__field--wide">
                <label class="kh-bo__label" for="tabs_requirements_body">Paragraph</label>
                <textarea class="kh-bo__control" id="tabs_requirements_body" name="tabs[requirements][body]"
                    placeholder="A short paragraph.">{{ old("tabs.requirements.body", $tabs['requirements']['body'] ?? '') }}</textarea>
            </div>

            <div class="kh-bo__field kh-bo__field--wide">
                <label class="kh-bo__label" for="tabs_requirements_list">Bullet points</label>
                <textarea class="kh-bo__control" id="tabs_requirements_list" name="tabs[requirements][list]"
                    placeholder="One per line.">{{ old("tabs.requirements.list", $listValue('requirements')) }}</textarea>
                <span class="kh-bo__hint">One bullet per line.</span>
            </div>

            <div class="kh-bo__section-label">
                What we can offer
                <span>Shown as a three-column card on the job page. One item per line; blank columns are left out.</span>
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="benefits">Benefits</label>
                <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('benefits')])
                    id="benefits" name="benefits" maxlength="2000"
                    placeholder="Salary&#10;Lunch Allowance&#10;Seniority Payment">{{ old('benefits', $post->benefits) }}</textarea>
                @error('benefits') <span class="kh-bo__error">{{ $message }}</span> @enderror
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="highlights">Highlights</label>
                <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('highlights')])
                    id="highlights" name="highlights" maxlength="2000"
                    placeholder="An awesome company&#10;Join a winning team">{{ old('highlights', $post->highlights) }}</textarea>
                @error('highlights') <span class="kh-bo__error">{{ $message }}</span> @enderror
            </div>

            <div class="kh-bo__field">
                <label class="kh-bo__label" for="career_opportunities">Career Opportunities</label>
                <textarea @class(['kh-bo__control', 'is-invalid' => $errors->has('career_opportunities')])
                    id="career_opportunities" name="career_opportunities" maxlength="2000"
                    placeholder="Opportunities for promotion&#10;Possibility for job training">{{ old('career_opportunities', $post->career_opportunities) }}</textarea>
                @error('career_opportunities') <span class="kh-bo__error">{{ $message }}</span> @enderror
            </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="quick_apply_title">Apply box heading</label>
            <input class="kh-bo__control" id="quick_apply_title" name="quick_apply_title" type="text" maxlength="255"
                value="{{ old('quick_apply_title', $post->quick_apply_title) }}" placeholder="Quick apply">
        </div>

        <div class="kh-bo__field">
            <label class="kh-bo__label" for="quick_apply_text">Apply box text</label>
            <input class="kh-bo__control" id="quick_apply_text" name="quick_apply_text" type="text" maxlength="500"
                value="{{ old('quick_apply_text', $post->quick_apply_text) }}"
                placeholder="Applications are reviewed as they arrive.">
        </div>
        </div>
    </div>

    <div class="kh-bo__form-actions">
        <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">Cancel</a>
        <button class="kh-bo__btn" type="submit">{{ $isEdit ? 'Save changes' : 'Create job post' }}</button>
    </div>
</div>
