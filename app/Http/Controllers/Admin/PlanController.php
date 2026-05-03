<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        return response()->json(Plan::orderBy('price')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                         => 'required|string|max:255',
            'description'                  => 'nullable|string',
            'price'                        => 'required|numeric|min:0',
            'max_offres'                   => 'nullable|integer|min:0',
            'max_articles'                 => 'nullable|integer|min:0',
            'max_evenements'               => 'nullable|integer|min:0',
            'max_entretiens_par_evenement' => 'nullable|integer|min:0',
            'max_candidatures_par_offre'   => 'nullable|integer|min:0',
            'is_active'                    => 'boolean',
            'duration_days'                => 'required|integer|min:1',
        ]);

        return response()->json(Plan::create($validated), 201);
    }

    public function show(Plan $plan)
    {
        return response()->json($plan);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'                         => 'required|string|max:255',
            'description'                  => 'nullable|string',
            'price'                        => 'required|numeric|min:0',
            'max_offres'                   => 'nullable|integer|min:0',
            'max_articles'                 => 'nullable|integer|min:0',
            'max_evenements'               => 'nullable|integer|min:0',
            'max_entretiens_par_evenement' => 'nullable|integer|min:0',
            'max_candidatures_par_offre'   => 'nullable|integer|min:0',
            'is_active'                    => 'boolean',
            'duration_days'                => 'required|integer|min:1',
        ]);

        $plan->update($validated);

        return response()->json($plan);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->json(['message' => 'Plan supprimé avec succès']);
    }
}
