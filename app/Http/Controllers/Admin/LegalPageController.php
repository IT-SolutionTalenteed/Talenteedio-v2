<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use Illuminate\Http\Request;

class LegalPageController extends Controller
{
    public function index()
    {
        return response()->json(LegalPage::orderBy('title')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'nullable|string|in:terms,privacy|unique:legal_pages,type',
        ]);

        return response()->json(LegalPage::create($validated), 201);
    }

    public function show(LegalPage $legalPage)
    {
        return response()->json($legalPage);
    }

    public function showBySlug(string $slug)
    {
        $page = LegalPage::where('slug', $slug)->firstOrFail();
        return response()->json($page);
    }

    public function showByType(string $type)
    {
        $page = LegalPage::where('type', $type)->firstOrFail();
        return response()->json($page);
    }
    public function update(Request $request, LegalPage $legalPage)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'type'        => 'nullable|string|in:terms,privacy|unique:legal_pages,type,' . $legalPage->id,
        ]);

        $legalPage->update($validated);

        return response()->json($legalPage);
    }

    public function destroy(LegalPage $legalPage)
    {
        $legalPage->delete();

        return response()->json(['message' => 'Page légale supprimée avec succès']);
    }
}
