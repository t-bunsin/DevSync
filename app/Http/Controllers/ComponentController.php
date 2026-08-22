<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobPost;
use App\Models\JobType;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Master data behind the job post form's Location, Department and Job type
 * fields (see JobPost::locationOptions()/departmentOptions()/types()). The
 * three are structurally identical — a name and an active flag — so one
 * controller serves all of them, keyed by the {type} route segment, each
 * with its own directory/create/edit pages in the same shape as Companies.
 */
class ComponentController extends Controller
{
    private const TYPES = [
        'locations' => Location::class,
        'departments' => Department::class,
        'job-types' => JobType::class,
    ];

    /** The job_posts column each type's name is matched against, for the usage count shown per row. */
    private const COLUMNS = [
        'locations' => 'location',
        'departments' => 'department',
        'job-types' => 'type',
    ];

    private const LABELS = [
        'locations' => 'Location',
        'departments' => 'Department',
        'job-types' => 'Job type',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request, string $type)
    {
        $model = $this->modelFor($type);

        $records = $model::query()
            ->with('creator')
            ->when($request->query('q'), fn ($q) => $q->where('name', 'like', '%' . $request->query('q') . '%'))
            ->orderBy('name')
            ->get();

        $counts = JobPost::query()
            ->whereIn(self::COLUMNS[$type], $records->pluck('name'))
            ->select(self::COLUMNS[$type], DB::raw('count(*) as total'))
            ->groupBy(self::COLUMNS[$type])
            ->pluck('total', self::COLUMNS[$type]);

        $records->each(fn ($record) => $record->usage_count = $counts->get($record->name, 0));

        return view('admin.components.index', [
            'type' => $type,
            'label' => self::LABELS[$type],
            'records' => $records,
            'total' => $model::count(),
            'activeCount' => $model::where('is_active', true)->count(),
            'jobPostsTotal' => $counts->sum(),
            'searchTerm' => $request->query('q'),
        ]);
    }

    public function create(string $type)
    {
        $this->modelFor($type);

        return view('admin.components.create', [
            'type' => $type,
            'label' => self::LABELS[$type],
            'record' => new (self::TYPES[$type])(),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $model = $this->modelFor($type);

        $record = $model::create($this->validated($request, $model) + ['created_by' => $request->user()->id]);

        return redirect()
            ->route('components.index', $type)
            ->withSuccess("“{$record->name}” was added.");
    }

    public function edit(string $type, int $component)
    {
        $model = $this->modelFor($type);
        $record = $model::with('creator')->findOrFail($component);
        $record->usage_count = JobPost::where(self::COLUMNS[$type], $record->name)->count();

        return view('admin.components.edit', [
            'type' => $type,
            'label' => self::LABELS[$type],
            'record' => $record,
        ]);
    }

    public function update(Request $request, string $type, int $component): RedirectResponse
    {
        $model = $this->modelFor($type);
        $record = $model::findOrFail($component);

        $record->update($this->validated($request, $model, $record->id));

        return redirect()
            ->route('components.index', $type)
            ->withSuccess("“{$record->name}” was updated.");
    }

    public function destroy(string $type, int $component): RedirectResponse
    {
        $model = $this->modelFor($type);
        $record = $model::findOrFail($component);
        $name = $record->name;

        // Nothing stops this — job_posts keeps the name as plain text (see the
        // create_*_table migrations), so existing posts are unaffected either
        // way. Deleting just drops it from the option list going forward.
        $record->delete();

        return redirect()
            ->route('components.index', $type)
            ->withSuccess("“{$name}” was deleted.");
    }

    /** @return class-string<Model> */
    private function modelFor(string $type): string
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        return self::TYPES[$type];
    }

    /** @param class-string<Model> $model */
    private function validated(Request $request, string $model, ?int $ignoreId = null): array
    {
        $table = (new $model())->getTable();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique($table, 'name')->ignore($ignoreId)],
        ]);

        // An unchecked checkbox submits nothing at all, so this can't be a
        // validated 'boolean' rule — read it directly instead.
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
