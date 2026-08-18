<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
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
        $from = $this->dateInput($request->query('from'));
        $to = $this->dateInput($request->query('to'));

        // A backwards range would silently return nothing, so read it as given.
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $resumes = Resume::query()
            ->with('author')
            ->status($request->query('status'))
            ->search($request->query('q'))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->get();

        // Counted off the whole table, not the filtered set, so the tiles do
        // not change meaning when a filter is applied.
        $counts = Resume::query()
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
        ]);
    }

    public function create()
    {
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
        $resume = new Resume($this->validated($request));

        $this->fillSections($resume, $request);
        $resume->created_by = Auth::id();
        $resume->save();

        return redirect()
            ->route('resumes.index')
            ->withSuccess("The resume for {$resume->full_name} was registered.");
    }

    public function show(Resume $resume)
    {
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
        $filename = Str::slug($resume->full_name) ?: 'resume';

        return Pdf::loadView('resumes.pdf', ['resume' => $resume])
            ->setPaper('a4')
            ->download("{$filename}-resume.pdf");
    }

    public function edit(Resume $resume)
    {
        return view('resumes.edit', [
            'resume' => $resume,
        ]);
    }

    public function update(Request $request, Resume $resume)
    {
        $resume->fill($this->validated($request));

        $this->fillSections($resume, $request);
        $resume->save();

        return redirect()
            ->route('resumes.index')
            ->withSuccess("The resume for {$resume->full_name} was updated.");
    }

    public function destroy(Resume $resume)
    {
        $name = $resume->full_name;

        $resume->delete();

        return redirect()
            ->route('resumes.index')
            ->withSuccess("The resume for {$name} was deleted.");
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

        return $request->validate($rules, [
            'full_name.required' => 'Enter the name this resume belongs to.',
            'work_history.*.started_on.regex' => 'Enter work history dates as a month and year.',
            'work_history.*.ended_on.regex' => 'Enter work history dates as a month and year.',
            'education.*.graduated_on.regex' => 'Enter the graduation date as a month and year.',
        ]);
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
