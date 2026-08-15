<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompaniesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companies = Company::query()
            ->withCount(['jobPosts', 'complianceRecords'])
            ->with(['complianceRecords' => fn ($q) => $q->select('id', 'company_id', 'status')])
            ->search($request->query('q'))
            ->when(
                in_array($request->query('status'), Company::statuses(), true),
                fn ($query) => $query->where('status', $request->query('status'))
            )
            ->orderBy('name')
            ->get();

        $counts = Company::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.companies.index', [
            'companies' => $companies,
            'counts' => $counts,
            'activeStatus' => $request->query('status'),
            'searchTerm' => $request->query('q'),
        ]);
    }

    public function create()
    {
        return view('admin.companies.create', ['company' => new Company()]);
    }

    public function store(Request $request)
    {
        $company = new Company($this->validated($request));

        $company->slug = Company::makeSlug($request->input('name'));

        if ($request->hasFile('logo')) {
            $company->logo = $request->file('logo')->store('company-logos', 'public');
        }

        if ($request->hasFile('cover')) {
            $company->cover = $request->file('cover')->store('company-covers', 'public');
        }

        $company->save();

        return redirect()
            ->route('companies')
            ->withSuccess("{$company->name} was added.");
    }

    public function show(Company $company)
    {
        return redirect()->route('companies.edit', $company);
    }

    public function edit(Company $company)
    {
        $company->loadCount(['jobPosts', 'complianceRecords']);

        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $company->fill($this->validated($request));
        $company->slug = Company::makeSlug($request->input('name'), $company->id);

        if ($request->hasFile('logo')) {
            $this->deleteLogo($company);
            $company->logo = $request->file('logo')->store('company-logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteLogo($company);
            $company->logo = null;
        }

        if ($request->hasFile('cover')) {
            $this->deleteCover($company);
            $company->cover = $request->file('cover')->store('company-covers', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $this->deleteCover($company);
            $company->cover = null;
        }

        $company->save();

        // Both tables keep a denormalised copy of the name for display, so a
        // rename has to be pushed out to them.
        $company->jobPosts()->update(['company' => $company->name]);
        $company->complianceRecords()->update(['name' => $company->name]);

        return redirect()
            ->route('companies')
            ->withSuccess("{$company->name} was updated.");
    }

    public function destroy(Company $company)
    {
        $posts = $company->jobPosts()->count();
        $records = $company->complianceRecords()->count();

        // The foreign keys restrict this anyway; refusing here turns a database
        // error into something the admin can act on.
        if ($posts || $records) {
            $blockers = [];

            if ($posts) {
                $blockers[] = $posts . ' job ' . Str::plural('post', $posts);
            }

            if ($records) {
                $blockers[] = $records . ' compliance ' . Str::plural('record', $records);
            }

            return back()->withErrors([
                'company' => "{$company->name} still has " . implode(' and ', $blockers)
                    . '. Remove or reassign those first.',
            ]);
        }

        $name = $company->name;
        $this->deleteLogo($company);
        $this->deleteCover($company);
        $company->delete();

        return redirect()
            ->route('companies')
            ->withSuccess("{$name} was deleted.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'registration_no' => 'nullable|string|max:120',
            'employer_type' => ['nullable', Rule::in(Company::employerTypes())],
            'industry' => ['nullable', Rule::in(Company::industries())],
            'employee_count' => ['nullable', Rule::in(Company::employeeRanges())],
            'status' => ['required', Rule::in(Company::statuses())],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'vision_mission' => 'nullable|string|max:6000',
            'what_we_do' => 'nullable|string|max:6000',
            'why_join_us' => 'nullable|string|max:6000',
            'workplace_culture' => 'nullable|string|max:6000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'website.url' => 'The website must be a full URL, including https://.',
            'logo.max' => 'The logo must be 2 MB or smaller.',
            'cover.max' => 'The cover image must be 4 MB or smaller.',
        ]);
    }

    private function deleteLogo(Company $company): void
    {
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }
    }

    private function deleteCover(Company $company): void
    {
        if ($company->cover) {
            Storage::disk('public')->delete($company->cover);
        }
    }
}
