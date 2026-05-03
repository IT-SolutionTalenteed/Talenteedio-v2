<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivitySector;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivitySectorController extends Controller
{
    public function index()
    {
        return response()->json(ActivitySector::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:activity_sectors,name',
        ]);

        return response()->json(ActivitySector::create($validated), 201);
    }

    public function show(ActivitySector $activitySector)
    {
        return response()->json($activitySector);
    }

    public function update(Request $request, ActivitySector $activitySector)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('activity_sectors', 'name')->ignore($activitySector->id)],
        ]);

        $activitySector->update($validated);

        return response()->json($activitySector);
    }

    public function destroy(ActivitySector $activitySector)
    {
        $activitySector->delete();

        return response()->json(['message' => "Secteur d'activité supprimé avec succès"]);
    }
}