<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieEvenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategorieEvenementController extends Controller
{
    public function index()
    {
        return response()->json(CategorieEvenement::orderBy('titre')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video'             => 'nullable|file|mimes:mp4,mov,avi,webm|max:102400',
            'galerie'           => 'nullable|array',
            'galerie.*'         => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:102400',
            'liste_details'     => 'nullable|array',
            'liste_details.*'   => 'string',
            'liste_temoignages' => 'nullable|array',
            'liste_temoignages.*' => 'string',
            'liste_faqs'        => 'nullable|array',
            'liste_faqs.*.question' => 'required_with:liste_faqs|string',
            'liste_faqs.*.reponse'  => 'required_with:liste_faqs|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('evenements/categories', 'public');
        }
        if ($request->hasFile('video')) {
            $validated['video'] = $request->file('video')->store('evenements/categories/videos', 'public');
        }

        $galeriePaths = [];
        if ($request->hasFile('galerie')) {
            foreach ($request->file('galerie') as $file) {
                $galeriePaths[] = $file->store('evenements/categories/galerie', 'public');
            }
        }
        $validated['galerie'] = $galeriePaths ?: null;

        $categorie = CategorieEvenement::create($validated);

        return response()->json($categorie, 201);
    }

    public function show(CategorieEvenement $categorieEvenement)
    {
        return response()->json($categorieEvenement);
    }

    public function update(Request $request, CategorieEvenement $categorieEvenement)
    {
        $validated = $request->validate([
            'titre'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video'             => 'nullable|file|mimes:mp4,mov,avi,webm|max:102400',
            'galerie'           => 'nullable|array',
            'galerie.*'         => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:102400',
            'liste_details'     => 'nullable|array',
            'liste_details.*'   => 'string',
            'liste_temoignages' => 'nullable|array',
            'liste_temoignages.*' => 'string',
            'liste_faqs'        => 'nullable|array',
            'liste_faqs.*.question' => 'required_with:liste_faqs|string',
            'liste_faqs.*.reponse'  => 'required_with:liste_faqs|string',
        ]);

        if ($request->hasFile('image')) {
            if ($categorieEvenement->image) Storage::disk('public')->delete($categorieEvenement->image);
            $validated['image'] = $request->file('image')->store('evenements/categories', 'public');
        }
        if ($request->hasFile('video')) {
            if ($categorieEvenement->video) Storage::disk('public')->delete($categorieEvenement->video);
            $validated['video'] = $request->file('video')->store('evenements/categories/videos', 'public');
        }
        if ($request->hasFile('galerie')) {
            $existing = $categorieEvenement->galerie ?? [];
            foreach ($request->file('galerie') as $file) {
                $existing[] = $file->store('evenements/categories/galerie', 'public');
            }
            $validated['galerie'] = $existing;
        }

        $categorieEvenement->update($validated);

        return response()->json($categorieEvenement);
    }

    public function destroy(CategorieEvenement $categorieEvenement)
    {
        if ($categorieEvenement->image) Storage::disk('public')->delete($categorieEvenement->image);
        if ($categorieEvenement->video) Storage::disk('public')->delete($categorieEvenement->video);
        foreach ($categorieEvenement->galerie ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $categorieEvenement->delete();

        return response()->json(['message' => 'Catégorie supprimée avec succès']);
    }

    public function removeGalerieItem(Request $request, CategorieEvenement $categorieEvenement)
    {
        $path = $request->validate(['path' => 'required|string'])['path'];
        Storage::disk('public')->delete($path);
        $galerie = array_values(array_filter($categorieEvenement->galerie ?? [], fn($p) => $p !== $path));
        $categorieEvenement->update(['galerie' => $galerie ?: null]);

        return response()->json($categorieEvenement);
    }
}
