<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieEvenement;
use App\Models\Temoignage;
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
        $this->syncTemoignages($categorie, $request);

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
        $this->syncTemoignages($categorieEvenement, $request);

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

    /**
     * Synchronise les témoignages inline soumis dans le formulaire.
     * Format FormData :
     *   temoignages[i][auteur], [poste], [contenu], [id] (optionnel)
     *   temoignages_avatars[i] (fichier, optionnel)
     */
    private function syncTemoignages(CategorieEvenement $categorie, Request $request): void
    {
        $temoignagesData = $request->input('temoignages', []);
        if (empty($temoignagesData)) {
            $categorie->temoignages()->sync([]);
            return;
        }

        $avatarFiles = $request->file('temoignages_avatars', []);
        $ids = [];

        foreach ($temoignagesData as $i => $data) {
            $auteur  = trim($data['auteur'] ?? '');
            $contenu = trim($data['contenu'] ?? '');
            if (!$auteur || !$contenu) continue;

            $tem = !empty($data['id']) ? Temoignage::find((int) $data['id']) : null;

            if ($tem) {
                $tem->auteur = $auteur;
                $tem->poste  = trim($data['poste'] ?? '') ?: null;
                $tem->contenu = $contenu;
                if (!empty($avatarFiles[$i])) {
                    if ($tem->avatar) Storage::disk('public')->delete($tem->avatar);
                    $tem->avatar = $avatarFiles[$i]->store('evenements/temoignages', 'public');
                }
                $tem->save();
            } else {
                $avatarPath = !empty($avatarFiles[$i])
                    ? $avatarFiles[$i]->store('evenements/temoignages', 'public')
                    : null;
                $tem = Temoignage::create([
                    'auteur'  => $auteur,
                    'poste'   => trim($data['poste'] ?? '') ?: null,
                    'contenu' => $contenu,
                    'avatar'  => $avatarPath,
                ]);
            }
            $ids[] = $tem->id;
        }

        $categorie->temoignages()->sync($ids);
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
