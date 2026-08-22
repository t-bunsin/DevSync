<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The employer-side inbox: every candidate who applied to one job post.
 *
 * Scoped to a post rather than listed globally, because that is the only way
 * the count in the job post table can be clicked through to the rows behind it.
 */
class JobApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Rows per page on the applications list, matching the job posts list. */
    private const PER_PAGE = 5;

    public function index(Request $request, JobPost $jobPost): View
    {
        abort_unless($request->user()->isAdmin() || $jobPost->created_by === $request->user()->id, 403);
        abort_unless($request->user()->hasPermission(Permission::APPLICATION_VIEW), 403);

        $jobPost->load('employer');

        $from = $this->dateInput($request->query('from'));
        $to = $this->dateInput($request->query('to'));

        // A backwards range would silently return nothing, so read it as given —
        // the same call JobPostController::index() makes.
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $applications = $jobPost->applications()
            ->with(['candidate', 'resume'])
            ->status($request->query('status'))
            ->search($request->query('q'))
            ->appliedBetween($from, $to)
            ->orderByDesc('applied_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // Off every application for this post, so the tiles keep their meaning
        // under a filter — the same call the job post list makes.
        $counts = $jobPost->applications()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('job-posts.applications', [
            'post' => $jobPost,
            'applications' => $applications,
            'counts' => $counts,
            'activeStatus' => $request->query('status'),
            'searchTerm' => $request->query('q'),
            'fromDate' => $from,
            'toDate' => $to,
        ]);
    }

    /**
     * One candidate, full page. Replaces the per-row dialog the list used to
     * carry: the review form lives here now, so there is one place where a
     * status, an internal note and the candidate's message are written.
     */
    public function show(Request $request, JobApplication $application): View
    {
        $this->authorizeOwner($application, Permission::APPLICATION_VIEW);

        $application->load(['jobPost.employer', 'candidate', 'resume']);

        return view('job-posts.application', [
            'application' => $application,
            'post' => $application->jobPost,
        ]);
    }

    /**
     * Serves the CV the candidate uploaded.
     *
     * The file lives on the private disk, so this route is the only way to it —
     * which is the point: it is served to signed-in back office users, not to
     * anyone who guesses the path.
     */
    public function downloadCv(JobApplication $application): StreamedResponse
    {
        $this->authorizeOwner($application, Permission::APPLICATION_DOWNLOAD);

        abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404);

        return Storage::disk('local')->download(
            $application->cv_path,
            $application->cv_name ?: 'cv'
        );
    }

    /** Moves one application along the pipeline, and keeps the private note. */
    public function update(Request $request, JobApplication $application): RedirectResponse
    {
        $this->authorizeOwner($application);

        $validated = $request->validate([
            'status' => ['required', Rule::in(JobApplication::statuses())],
            'note' => 'nullable|string|max:2000',
            'candidate_message' => 'nullable|string|max:2000',
        ]);

        /* Stamp the move, not the edit: this form also saves the private note,
           and a note edit is not a decision the candidate should see re-dated.
           Back to 'new' clears it — there is no decision to date any more. */
        if ($validated['status'] !== $application->status) {
            $validated['status_changed_at'] = $validated['status'] === JobApplication::STATUS_NEW
                ? null
                : now();
        }

        $application->forceFill($validated)->save();

        return redirect()
            ->route('job-applications.show', $application)
            ->withSuccess("{$application->full_name} was moved to “{$application->status}”.");
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        $this->authorizeOwner($application);

        $postId = $application->job_post_id;
        $name = $application->full_name;

        $application->delete();

        return redirect()
            ->route('job-posts.applications', $postId)
            ->withSuccess("The application from {$name} was deleted.");
    }

    /**
     * Admin may touch any application; everyone else only their own post's.
     * Ownership first, then the permission the action needs — the same two
     * gates in the same order as JobPostController::authorizeOwner(). Status
     * changes and deletes take no permission yet, so they pass null.
     */
    private function authorizeOwner(JobApplication $application, ?string $permission = null): void
    {
        $viewer = request()->user();

        abort_unless($viewer->isAdmin() || $application->jobPost->created_by === $viewer->id, 403);
        abort_if($permission && ! $viewer->hasPermission($permission), 403);
    }
}
