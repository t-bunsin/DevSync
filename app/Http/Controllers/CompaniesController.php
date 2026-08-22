<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\EmployerProfile;
use Illuminate\Database\Eloquent\Builder;
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
        $user = $request->user();

        // Admin browses the whole directory; an employer only ever sees the
        // one company tied to their account (see User::ownCompany()).
        $ownCompanyId = $user->isAdmin() ? null : $user->ownCompany()?->id;

        $companies = Company::query()
            ->withCount(['jobPosts', 'complianceRecords'])
            ->with(['complianceRecords' => fn ($q) => $q->select('id', 'company_id', 'status')])
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where('id', $ownCompanyId ?? 0))
            ->search($request->query('q'))
            ->when(
                in_array($request->query('status'), Company::statuses(), true),
                fn ($query) => $query->where('status', $request->query('status'))
            )
            ->orderBy('name')
            ->get();

        $counts = Company::query()
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where('id', $ownCompanyId ?? 0))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.companies.index', [
            'companies' => $companies,
            'counts' => $counts,
            'activeStatus' => $request->query('status'),
            'searchTerm' => $request->query('q'),
            // Decides which rows offer an Edit action; null for an admin, who
            // may edit every row regardless.
            'ownCompanyId' => $ownCompanyId,
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
        $this->authorizeManage($company);

        $company->loadCount(['jobPosts', 'complianceRecords']);

        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorizeManage($company);

        // Captured before fill(), so the rename below can find the profiles
        // that still point at the old name.
        $originalName = $company->name;

        // validated() drops `status` for an employer, so fill() cannot set it
        // even if the field is posted by hand.
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

        // Three tables keep a copy of the name, so a rename has to be pushed out
        // to all of them. employer_profiles is the load-bearing one: it is not
        // display text but the only link between an employer and their company
        // (User::ownCompany() matches on it), so leaving it stale locks the
        // employer out of the record they just renamed.
        $company->jobPosts()->update(['company' => $company->name]);
        $company->complianceRecords()->update(['name' => $company->name]);

        if ($originalName !== $company->name) {
            EmployerProfile::whereRaw('LOWER(company_name) = ?', [mb_strtolower($originalName)])
                ->update(['company_name' => $company->name]);
        }

        return redirect()
            ->route($request->user()->isAdmin() ? 'companies' : 'companies.edit', $request->user()->isAdmin() ? [] : $company)
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

    /**
     * An employer may edit the company they belong to, and nothing else. There
     * is no FK between the two — User::ownCompany() matches the free-text name
     * from registration — so the comparison goes through that lookup rather
     * than a column on the row.
     */
    private function authorizeManage(Company $company): void
    {
        $user = request()->user();

        abort_unless($user->isAdmin() || $company->is($user->ownCompany()), 403);
    }

    /**
     * `status` is the platform's verification decision, not the company's own
     * claim about itself, so the rule — and therefore the field — exists only
     * for an admin. Without it in the returned array, fill() leaves the column
     * alone however the form was submitted.
     */
    private function validated(Request $request): array
    {
        $statusRule = $request->user()->isAdmin()
            ? ['status' => ['required', Rule::in(Company::statuses())]]
            : [];

        return $request->validate($statusRule + [
            'name' => 'required|string|max:255',
            'registration_no' => 'nullable|string|max:120',
            'employer_type' => ['nullable', Rule::in(Company::employerTypes())],
            'industry' => ['nullable', Rule::in(Company::industries())],
            'employee_count' => ['nullable', Rule::in(Company::employeeRanges())],
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
