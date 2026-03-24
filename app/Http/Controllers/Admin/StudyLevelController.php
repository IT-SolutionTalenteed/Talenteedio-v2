<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudyLevel;
use Illuminate\Http\Request;

class StudyLevelController extends Controller
{
    public function index()
    {
        return response()->json(StudyLevel::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:study_levels,name',
        ]);

        return response()->json(StudyLevel::create($validated), 201);
    }

    public function show(StudyLevel $studyLevel)
    {
        return response()->json($studyLevel);
    }

    public function update(Request $request, StudyLevel $studyLevel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:study_levels,name,' . $studyLevel->id,
        ]);

        $studyLevel->update($validated);

        return response()->json($studyLevel);
    }

    public function destroy(StudyLevel $studyLevel)
    {
        $studyLevel->delete();

        return response()->json(['message' => "Niveau d'étude supprimé avec succès"]);
    }
}