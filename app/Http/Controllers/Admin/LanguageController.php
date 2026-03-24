<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index()
    {
        return response()->json(Language::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:languages,name',
        ]);

        return response()->json(Language::create($validated), 201);
    }

    public function show(Language $language)
    {
        return response()->json($language);
    }

    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:languages,name,' . $language->id,
        ]);

        $language->update($validated);

        return response()->json($language);
    }

    public function destroy(Language $language)
    {
        $language->delete();

        return response()->json(['message' => 'Langue supprimé(e) avec succès']);
    }
}