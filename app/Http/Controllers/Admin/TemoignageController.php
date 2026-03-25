<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorieEvenement;
use App\Models\Temoignage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemoignageController extends Controller
{
    /**
     * Liste tous les témoignages (réutilisables).
     */
    public function index()
    {
        return response()->json(Temoignage::orderBy('auteur')->get());
    }

    /**
     * Créer un témoignage standalone.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'auteur'  => 'required|string|max:255',
            'poste'   => 'nullable|string|max:255',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'contenu' => 'required|string',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('temoignages/avatars', 'public');
        }

        return response()->json(Temoignage::create($validated), 201);
    }

    /**
     * Modifier un témoignage.
     */
    public function update(Request $request, Temoignage $temoignage)
    {
        $validated = $request->validate([
            'auteur'  => 'required|string|max:255',
            'poste'   => 'nullable|string|max:255',
            'avatar'  => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'contenu' => 'required|string',
        ]);

        if ($request->hasFile('avatar')) {
            if ($temoignage->avatar) Storage::disk('public')->delete($temoignage->avatar);
            $validated['avatar'] = $request->file('avatar')->store('temoignages/avatars', 'public');
        }

        $temoignage->update($validated);
        return response()->json($temoignage);
    }

    /**
     * Supprimer un témoignage (le retire aussi de toutes les catégories).
     */
    public function destroy(Temoignage $temoignage)
    {
        if ($temoignage->avatar) Storage::disk('public')->delete($temoignage->avatar);
        $temoignage->delete();
        return response()->json(['message' => 'Témoignage supprimé.']);
    }

    /**
     * Attacher un témoignage existant à une catégorie d'événement.
     */
    public function attach(Request $request, CategorieEvenement $categorieEvenement)
    {
        $request->validate(['temoignage_id' => 'required|integer|exists:temoignages,id']);
        $categorieEvenement->temoignages()->syncWithoutDetaching([$request->temoignage_id]);
        return response()->json($categorieEvenement->load('temoignages'));
    }

    /**
     * Détacher un témoignage d'une catégorie d'événement.
     */
    public function detach(CategorieEvenement $categorieEvenement, Temoignage $temoignage)
    {
        $categorieEvenement->temoignages()->detach($temoignage->id);
        return response()->json(['message' => 'Témoignage retiré de la catégorie.']);
    }
}
