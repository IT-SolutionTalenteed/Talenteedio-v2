<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function index()
    {
        return response()->json(Experience::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:experiences,name',
        ]);

        return response()->json(Experience::create($validated), 201);
    }

    public function show(Experience $experience)
    {
        return response()->json($experience);
    }

    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:experiences,name,' . $experience->id,
        ]);

        $experience->update($validated);

        return response()->json($experience);
    }

    public function destroy(Experience $experience)
    {
        $experience->delete();

        return response()->json(['message' => 'Expérience supprimé(e) avec succès']);
    }
}