<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The candidate's own view of the same JobApplication rows the employer-side
 * JobApplicationController manages — scoped by who applied, not by post, and
 * read-only: no status changes, no employer note.
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
        $applications = JobApplication::query()
            ->where('user_id', $request->user()->id)
            ->with('jobPost')
            ->orderByDesc('applied_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('applications.mine', ['applications' => $applications]);
    }
}
