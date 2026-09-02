<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetrics;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(DashboardMetrics $metrics)
    {
        $cards = $metrics->cards();

        return view('home', [
            'cards' => $cards,
            'command' => $metrics->command(),
            // The sidebar's user count would otherwise be recounted by the
            // layouts.admin composer; hand it the figure already in hand.
            'widget' => ['users' => $cards['users']['value']],
        ]);
    }
}
