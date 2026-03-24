<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index()
    {
        return response()->json(Skill::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name',
        ]);

        return response()->json(Skill::create($validated), 201);
    }

    public function show(Skill $skill)
    {
        return response()->json($skill);
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name,' . $skill->id,
        ]);

        $skill->update($validated);

        return response()->json($skill);
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return response()->json(['message' => 'Compétence supprimé(e) avec succès']);
    }
}