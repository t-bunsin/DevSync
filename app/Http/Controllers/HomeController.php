<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\User;

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
    public function index()
    {
        $widget = [
            'users' => User::count(),
            'open_roles' => JobPost::published()->count(),
        ];

        return view('home', compact('widget'));
    }
}
