<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ComplianceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $records = Compliance::query()
            ->with('verifier')
            ->status($request->query('status'))
            ->search($request->query('q'))
            ->orderByDesc('created_at')
            ->get();

        // Counted off the whole table, not the filtered set, so the tiles do
        // not change meaning when a filter is applied.
        $counts = Compliance::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('compliance.index', [
            'records' => $records,
            'counts' => $counts,
            'activeStatus' => $request->query('status'),
            'searchTerm' => $request->query('q'),
        ]);
    }

    public function create()
    {
        return view('compliance.create', ['compliance' => new Compliance()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $compliance = new Compliance($validated);

        if ($request->hasFile('logo')) {
            $compliance->logo = $request->file('logo')->store('compliance-logos', 'public');
        }

        $this->syncVerification($compliance);
        $compliance->save();

        return redirect()
            ->route('compliance.index')
            ->withSuccess("Compliance record for {$compliance->name} was created.");
    }

    public function show(Compliance $compliance)
    {
        return redirect()->route('compliance.edit', $compliance);
    }

    public function edit(Compliance $compliance)
    {
        return view('compliance.edit', compact('compliance'));
    }

    public function update(Request $request, Compliance $compliance)
    {
        $validated = $this->validated($request);

        $compliance->fill($validated);

        if ($request->hasFile('logo')) {
            $this->deleteLogo($compliance);
            $compliance->logo = $request->file('logo')->store('compliance-logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteLogo($compliance);
            $compliance->logo = null;
        }

        $this->syncVerification($compliance);
        $compliance->save();

        return redirect()
            ->route('compliance.index')
            ->withSuccess("Compliance record for {$compliance->name} was updated.");
    }

    /**
     * The blue-badge action: flips a record to verified, stamping who did it
     * and when. Sending it again un-verifies, so a mistake is reversible.
     */
    public function verify(Compliance $compliance)
    {
        if ($compliance->isVerified()) {
            $compliance->forceFill([
                'status' => Compliance::STATUS_PENDING,
                'verified_at' => null,
                'verified_by' => null,
            ])->save();

            return back()->withSuccess("Verification removed from {$compliance->name}.");
        }

        $compliance->forceFill([
            'status' => Compliance::STATUS_VERIFIED,
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ])->save();

        return back()->withSuccess("{$compliance->name} is now verified.");
    }

    public function destroy(Compliance $compliance)
    {
        $name = $compliance->name;

        $this->deleteLogo($compliance);
        $compliance->delete();

        return redirect()
            ->route('compliance.index')
            ->withSuccess("Compliance record for {$name} was deleted.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'category' => ['required', Rule::in(Compliance::categories())],
            'reference' => 'nullable|string|max:120',
            'status' => ['required', Rule::in(Compliance::statuses())],
            'issued_on' => 'nullable|date',
            'expires_on' => 'nullable|date|after_or_equal:issued_on',
            'notes' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ], [
            'expires_on.after_or_equal' => 'The expiry date cannot be before the issue date.',
            'logo.max' => 'The logo must be 2 MB or smaller.',
        ]);
    }

    /**
     * Keeps the sign-off columns honest whichever way the status was set.
     * Setting verified through the form stamps the current admin; moving away
     * from verified clears the stamp, so a record can never claim a sign-off
     * it no longer has. An already-verified record keeps its original signer.
     */
    private function syncVerification(Compliance $compliance): void
    {
        if (! $compliance->isVerified()) {
            $compliance->verified_at = null;
            $compliance->verified_by = null;

            return;
        }

        if ($compliance->verified_at === null) {
            $compliance->verified_at = now();
            $compliance->verified_by = Auth::id();
        }
    }

    private function deleteLogo(Compliance $compliance): void
    {
        if ($compliance->logo) {
            Storage::disk('public')->delete($compliance->logo);
        }
    }
}
