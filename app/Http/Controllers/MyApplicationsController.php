<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The candidate's own view of the same JobApplication rows the employer-side
 * JobApplicationController manages — scoped by who applied, not by post.
 *
 * Read-only on everything the employer owns (status, their private note); the
 * one write a candidate has is withdrawing an application the employer has
 * not looked at yet.
 */
class MyApplicationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private const PER_PAGE = 10;

    public function index(Request $request): View
    {
        $from = $this->dateInput($request->query('from'));
        $to = $this->dateInput($request->query('to'));

        // A backwards range would silently return nothing, so read it as given —
        // the same call the employer-side inbox makes.
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $searchTerm = trim((string) $request->query('q'));

        $applications = JobApplication::query()
            ->where('user_id', $request->user()->id)
            ->with('jobPost')
            ->searchJob($searchTerm)
            ->appliedBetween($from, $to)
            ->orderByDesc('applied_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('applications.mine', [
            'applications' => $applications,
            'searchTerm' => $searchTerm,
            'fromDate' => $from,
            'toDate' => $to,
            'isFiltered' => $searchTerm !== '' || $from || $to,
        ]);
    }

    public function show(Request $request, JobApplication $application): View
    {
        $this->authorizeOwner($request, $application);

        $application->load(['jobPost.employer', 'resume']);

        return view('applications.show', ['application' => $application]);
    }

    /**
     * Withdraws the application. Deleting the row is the whole operation —
     * the model's deleting hook takes the uploaded CV with it — so this is
     * refused outright once the employer has moved the row along.
     */
    public function destroy(Request $request, JobApplication $application): RedirectResponse
    {
        $this->authorizeOwner($request, $application);

        abort_unless($application->isCancellable(), 403, 'This application has already been reviewed.');

        $title = $application->jobPost?->title ?? 'the role';

        $application->delete();

        return redirect()
            ->route('my-applications')
            ->withSuccess("Your application for “{$title}” was withdrawn.");
    }

    /** Every row on this screen is the signed-in candidate's own. */
    private function authorizeOwner(Request $request, JobApplication $application): void
    {
        abort_unless($application->user_id === $request->user()->id, 403);
    }
}
