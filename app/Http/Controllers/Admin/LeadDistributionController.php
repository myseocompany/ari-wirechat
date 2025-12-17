<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadDistributionController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('status_id', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'assignable', 'last_assigned']);

        $total = $users->sum(fn ($user) => (int) ($user->assignable ?? 0));

        return view('admin.leads-distribution', [
            'users' => $users,
            'total' => $total,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weights' => ['required', 'array'],
            'weights.*' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        $weights = collect($validated['weights'] ?? [])
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value >= 0);

        $total = $weights->sum();

        if ($total !== 100) {
            return back()
                ->withErrors(['weights' => "La suma debe ser 100%. Actualmente es {$total}%."])
                ->withInput();
        }

        $users = User::whereIn('id', $weights->keys())->get(['id']);

        foreach ($users as $user) {
            $user->assignable = $weights[$user->id] ?? 0;
            $user->save();
        }

        return redirect()
            ->route('admin.leads-distribution.index')
            ->with('status', 'Distribución actualizada correctamente.');
    }
}
