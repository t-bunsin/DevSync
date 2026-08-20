<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPost;
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

    public function index(Request $request, JobPost $jobPost): View
    {
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
            ->get();

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
     * Serves the CV the candidate uploaded.
     *
     * The file lives on the private disk, so this route is the only way to it —
     * which is the point: it is served to signed-in back office users, not to
     * anyone who guesses the path.
     */
    public function downloadCv(JobApplication $application): StreamedResponse
    {
        abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404);

        return Storage::disk('local')->download(
            $application->cv_path,
            $application->cv_name ?: 'cv'
        );
    }

    /** Moves one application along the pipeline, and keeps the private note. */
    public function update(Request $request, JobApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(JobApplication::statuses())],
            'note' => 'nullable|string|max:2000',
        ]);

        $application->update($validated);

        return redirect()
            ->route('job-posts.applications', $application->job_post_id)
            ->withSuccess("{$application->full_name} was moved to “{$application->status}”.");
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        $postId = $application->job_post_id;
        $name = $application->full_name;

        $application->delete();

        return redirect()
            ->route('job-posts.applications', $postId)
            ->withSuccess("The application from {$name} was deleted.");
    }
}
