<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Curates which published job posts show as "Featured" on the public site.
 * The employer-facing job post form no longer exposes this flag (see
 * JobPostController::validated()) — promotion is an admin call, not
 * something an employer can grant themselves.
 */
class FeaturedJobController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $posts = JobPost::query()
            ->with('employer')
            ->published()
            ->search($request->query('q'))
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.featured-jobs.index', [
            'posts' => $posts,
            'featuredCount' => JobPost::published()->where('featured', true)->count(),
            'searchTerm' => $request->query('q'),
        ]);
    }

    public function toggle(JobPost $jobPost): RedirectResponse
    {
        $jobPost->update(['featured' => ! $jobPost->featured]);

        return back()->withSuccess(
            $jobPost->featured
                ? "“{$jobPost->title}” is now featured."
                : "“{$jobPost->title}” is no longer featured."
        );
    }
}
