<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('contact', [
            'details' => config('contact'),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'topic' => ['required', 'string', 'in:hiring,job-seeking,partnership,other'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        // No mailer is configured yet, so the enquiry is written to the log for now.
        Log::info('Contact enquiry received', $data);

        return redirect()
            ->route('contact')
            ->with('contact_sent', $data['name']);
    }
}
