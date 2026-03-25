<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieEvenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CategorieEvenementController extends Controller
{
    public function index()
    {
        return response()->json(CategorieEvenement::with('temoignages')->orderBy('titre')->get());
    }

    public function store(Request $request)
    {
        $this->checkUploadErrors($request);

        $validated = $request->validate([
            'titre'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video'             => 'nullable|file|max:512000',
            'galerie'           => 'nullable|array',
            'galerie.*'         => 'file|max:102400',
            'liste_details'     => 'nullable|array',
            'liste_details.*'   => 'string',
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

        return response()->json($categorie->load('temoignages'), 201);
    }

    public function show(CategorieEvenement $categorieEvenement)
    {
        return response()->json($categorieEvenement);
    }

    public function update(Request $request, CategorieEvenement $categorieEvenement)
    {
        $this->checkUploadErrors($request);

        $validated = $request->validate([
            'titre'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video'             => 'nullable|file|max:512000',
            'galerie'           => 'nullable|array',
            'galerie.*'         => 'file|max:102400',
            'liste_details'     => 'nullable|array',
            'liste_details.*'   => 'string',
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

        return response()->json($categorieEvenement->load('temoignages'));
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

    private function checkUploadErrors(Request $request): void
    {
        $fields = ['image', 'video'];
        $errors = [];

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $errCode = $file->getError();
                Log::debug("Upload [$field]: error_code=$errCode, size={$file->getSize()}, mime={$file->getMimeType()}, original={$file->getClientOriginalName()}");
                if ($errCode !== UPLOAD_ERR_OK) {
                    $errors[$field] = ['Le fichier ' . $field . ' est trop volumineux ou n\'a pas pu être uploadé. (code: ' . $errCode . ')'];
                }
            }
        }

        if ($request->hasFile('galerie')) {
            foreach ($request->file('galerie') as $i => $file) {
                $errCode = $file->getError();
                Log::debug("Upload [galerie.$i]: error_code=$errCode, size={$file->getSize()}, mime={$file->getMimeType()}");
                if ($errCode !== UPLOAD_ERR_OK) {
                    $errors["galerie.$i"] = ['Le fichier galerie[' . $i . '] est trop volumineux ou n\'a pas pu être uploadé. (code: ' . $errCode . ')'];
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
