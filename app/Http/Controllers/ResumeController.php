<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Resume;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResumeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $candidate = $request->user()->isCandidateOnly();

        if (! $candidate) {
            $this->authorizeView();
        }

        $from = $this->dateInput($request->query('from'));
        $to = $this->dateInput($request->query('to'));

        // A backwards range would silently return nothing, so read it as given.
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $resumes = Resume::query()
            ->with('author')
            ->when($candidate, fn ($query) => $query->where('created_by', $request->user()->id))
            ->status($request->query('status'))
            ->search($request->query('q'))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->get();

        // Counted off the whole table, not the filtered set, so the tiles do
        // not change meaning when a filter is applied.
        $counts = Resume::query()
            ->when($candidate, fn ($query) => $query->where('created_by', $request->user()->id))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('resumes.index', [
            'resumes' => $resumes,
            'counts' => $counts,
            'activeStatus' => $request->query('status'),
            'searchTerm' => $request->query('q'),
            'fromDate' => $from,
            'toDate' => $to,
            'isCandidate' => $candidate,
            // What the row actions may offer. A candidate's list holds only
            // their own rows, so ownership is already true for every row here.
            'can' => [
                'create' => $candidate || $request->user()->hasPermission(Permission::RESUME_CREATE),
                'edit' => $candidate || $request->user()->hasPermission(Permission::RESUME_EDIT),
                'download' => $candidate || $request->user()->hasPermission(Permission::RESUME_DOWNLOAD),
                'delete' => ! $candidate && $request->user()->hasPermission(Permission::RESUME_DELETE),
            ],
        ]);
    }

    public function create()
    {
        $this->authorizeAction(Permission::RESUME_CREATE);

        return view('resumes.create', [
            'resume' => new Resume(),
        ]);
    }

    /**
     * Registers a resume: the header fields come off the request as one flat
     * set, while the repeating sections are rebuilt from scratch so only known
     * keys reach the JSON columns.
     */
    public function store(Request $request)
    {
        $this->authorizeAction(Permission::RESUME_CREATE);

        $resume = new Resume($this->validated($request));

        $this->fillSections($resume, $request);
        $this->syncPhoto($resume, $request);
        $resume->created_by = Auth::id();
        $resume->save();

        return redirect()
            ->route('resumes.index')
            ->withSuccess("The resume for {$resume->full_name} was registered.");
    }

    public function show(Resume $resume)
    {
        $this->authorizeAction(Permission::RESUME_VIEW, $resume);

        return view('resumes.show', [
            'resume' => $resume->load('author'),
        ]);
    }

    /**
     * Renders the resume as a PDF attachment. A separate template rather than
     * the preview view: dompdf renders CSS 2.1, so the preview's grid, columns
     * and color-mix() would silently collapse into an unstyled page.
     */
    public function download(Resume $resume)
    {
        $this->authorizeAction(Permission::RESUME_DOWNLOAD, $resume);

        $filename = Str::slug($resume->full_name) ?: 'resume';

        return Pdf::loadView('resumes.pdf', ['resume' => $resume])
            ->setPaper('a4')
            ->download("{$filename}-resume.pdf");
    }

    public function edit(Resume $resume)
    {
        $this->authorizeAction(Permission::RESUME_EDIT, $resume);

        return view('resumes.edit', [
            'resume' => $resume,
        ]);
    }

    public function update(Request $request, Resume $resume)
    {
        $this->authorizeAction(Permission::RESUME_EDIT, $resume);

        $resume->fill($this->validated($request));

        $this->fillSections($resume, $request);
        $this->syncPhoto($resume, $request);
        $resume->save();

        return redirect()
            ->route('resumes.index')
            ->withSuccess("The resume for {$resume->full_name} was updated.");
    }

    public function destroy(Resume $resume)
    {
        $this->authorizeAction(Permission::RESUME_DELETE, $resume);

        $name = $resume->full_name;

        $this->deletePhoto($resume);
        $resume->delete();

        return redirect()
            ->route('resumes.index')
            ->withSuccess("The resume for {$name} was deleted.");
    }

    /**
     * Browsing the register is its own grant. It used to be implied by holding
     * any one resume.* code, which meant "download only" still handed over the
     * whole list; resume.view now says so explicitly.
     */
    private function authorizeView(): void
    {
        abort_unless(request()->user()->hasPermission(Permission::RESUME_VIEW), 403);
    }

    /**
     * Two ways in, and only two: a job seeker acting on a resume they wrote,
     * or anyone else holding the permission that action needs. Passing no
     * $resume asks about the area rather than a row — create, for instance,
     * where a candidate is always writing their own.
     */
    private function authorizeAction(string $permission, ?Resume $resume = null): void
    {
        $user = request()->user();

        if ($user->isCandidateOnly()) {
            abort_unless($resume === null || $resume->created_by === $user->id, 403);

            // Deleting is not an owner action here: the register keeps the row.
            abort_if($permission === Permission::RESUME_DELETE, 403);

            return;
        }

        abort_unless($user->hasPermission($permission), 403);
    }

    /**
     * Header fields are validated strictly. The repeating sections are only
     * bounded here — a row is not required to be complete, because a resume is
     * usually typed up over several passes; fillSections() drops whatever is
     * still blank on save.
     */
    private function validated(Request $request): array
    {
        $rules = [
            'full_name' => 'required|string|max:255',
            'headline' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:40',
            'location' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:2000',
            'status' => ['required', Rule::in(Resume::statuses())],
            'skills' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];

        foreach (Resume::SECTIONS as $section => $fields) {
            $rules[$section] = 'nullable|array|max:40';

            foreach ($fields as $field) {
                $rules["{$section}.*.{$field}"] = in_array($field, Resume::LIST_FIELDS, true)
                    ? 'nullable|string|max:2000'
                    : 'nullable|string|max:255';
            }
        }

        // The month inputs post "YYYY-MM"; anything else is a hand-edited form.
        foreach (['work_history.*.started_on', 'work_history.*.ended_on', 'education.*.graduated_on'] as $key) {
            $rules[$key] = 'nullable|regex:/^\d{4}-\d{2}$/';
        }

        $validated = $request->validate($rules, [
            'full_name.required' => 'Enter the name this resume belongs to.',
            'photo.image' => 'The photo must be an image file.',
            'photo.max' => 'The photo must be 2 MB or smaller.',
            'work_history.*.started_on.regex' => 'Enter work history dates as a month and year.',
            'work_history.*.ended_on.regex' => 'Enter work history dates as a month and year.',
            'education.*.graduated_on.regex' => 'Enter the graduation date as a month and year.',
        ]);

        // syncPhoto() stores the upload and writes the path; mass assigning the
        // UploadedFile itself would put an object where a path belongs.
        unset($validated['photo']);

        return $validated;
    }

    /**
     * Applies a new upload, or clears the current photo when the remove box is
     * ticked. Replacing always deletes the old file first, so swapping a photo
     * repeatedly does not leave orphans behind on disk.
     */
    private function syncPhoto(Resume $resume, Request $request): void
    {
        if ($request->hasFile('photo')) {
            $this->deletePhoto($resume);
            $resume->photo = $request->file('photo')->store('resume-photos', 'public');

            return;
        }

        if ($request->boolean('remove_photo')) {
            $this->deletePhoto($resume);
            $resume->photo = null;
        }
    }

    private function deletePhoto(Resume $resume): void
    {
        if ($resume->photo) {
            Storage::disk('public')->delete($resume->photo);
        }
    }

    /**
     * Rebuilds every JSON column from the request. Rows are reindexed so the
     * stored order matches the form, and a row left entirely blank is dropped
     * rather than saved as an empty entry that would print as a gap.
     */
    private function fillSections(Resume $resume, Request $request): void
    {
        foreach (Resume::SECTIONS as $section => $fields) {
            $rows = [];

            foreach ((array) $request->input($section, []) as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $clean = [];

                foreach ($fields as $field) {
                    $value = $row[$field] ?? null;

                    $clean[$field] = in_array($field, Resume::LIST_FIELDS, true)
                        ? $this->lines($value)
                        : (trim((string) $value) ?: null);
                }

                $clean = array_filter($clean, fn ($value) => $value !== null && $value !== []);

                if ($clean !== []) {
                    $rows[] = $clean;
                }
            }

            $resume->{$section} = $rows;
        }

        $resume->skills = $this->lines($request->input('skills'));
    }

    /** A textarea holding one item per line, blank lines discarded. */
    private function lines(?string $value): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', (string) $value) ?: []),
            fn (string $line) => $line !== ''
        ));
    }
}
