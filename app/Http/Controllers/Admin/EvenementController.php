<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\CategorieEvenement;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvenementController extends Controller
{
    public function index()
    {
        return response()->json(
            Evenement::with(['categorie', 'entreprises'])
                ->orderBy('date_debut', 'desc')
                ->paginate(15)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'                   => 'required|string|max:255',
            'image_mise_en_avant'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'description'             => 'nullable|string',
            'details_supplementaires' => 'nullable|string',
            'date_debut'              => 'required|date',
            'date_fin'                => 'required|date|after_or_equal:date_debut',
            'heure_debut_journee'     => 'required|date_format:H:i',
            'heure_fin_journee'       => 'required|date_format:H:i',
            'categorie_evenement_id'  => 'nullable|exists:categorie_evenements,id',
            'pays'                    => 'nullable|string|max:255',
            'ville'                   => 'nullable|string|max:255',
            'adresse'                 => 'nullable|string|max:255',
            'is_featured'             => 'boolean',
            'entreprise_ids'          => 'array',
            'entreprise_ids.*'        => 'exists:entreprises,id',
        ]);

        if ($request->hasFile('image_mise_en_avant')) {
            $validated['image_mise_en_avant'] = $request->file('image_mise_en_avant')
                ->store('evenements', 'public');
        }

        $evenement = Evenement::create($validated);
        $evenement->entreprises()->sync($validated['entreprise_ids'] ?? []);

        return response()->json($evenement->load(['categorie', 'entreprises']), 201);
    }

    public function show(Evenement $evenement)
    {
        return response()->json($evenement->load(['categorie', 'entreprises']));
    }

    public function update(Request $request, Evenement $evenement)
    {
        $validated = $request->validate([
            'titre'                   => 'required|string|max:255',
            'image_mise_en_avant'     => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'description'             => 'nullable|string',
            'details_supplementaires' => 'nullable|string',
            'date_debut'              => 'required|date',
            'date_fin'                => 'required|date|after_or_equal:date_debut',
            'heure_debut_journee'     => 'required|date_format:H:i',
            'heure_fin_journee'       => 'required|date_format:H:i',
            'categorie_evenement_id'  => 'nullable|exists:categorie_evenements,id',
            'pays'                    => 'nullable|string|max:255',
            'ville'                   => 'nullable|string|max:255',
            'adresse'                 => 'nullable|string|max:255',
            'is_featured'             => 'boolean',
            'entreprise_ids'          => 'array',
            'entreprise_ids.*'        => 'exists:entreprises,id',
        ]);

        if ($request->hasFile('image_mise_en_avant')) {
            if ($evenement->image_mise_en_avant) {
                Storage::disk('public')->delete($evenement->image_mise_en_avant);
            }
            $validated['image_mise_en_avant'] = $request->file('image_mise_en_avant')
                ->store('evenements', 'public');
        }

        $evenement->update($validated);
        $evenement->entreprises()->sync($validated['entreprise_ids'] ?? []);

        return response()->json($evenement->load(['categorie', 'entreprises']));
    }

    public function destroy(Evenement $evenement)
    {
        if ($evenement->image_mise_en_avant) {
            Storage::disk('public')->delete($evenement->image_mise_en_avant);
        }
        $evenement->delete();

        return response()->json(['message' => 'Événement supprimé avec succès']);
    }

    public function toggleFeatured(Evenement $evenement)
    {
        $evenement->update(['is_featured' => !$evenement->is_featured]);

        return response()->json($evenement);
    }

    public function referentiels()
    {
        return response()->json([
            'categories'  => CategorieEvenement::orderBy('titre')->get(),
            'entreprises' => Entreprise::orderBy('nom')->get(),
        ]);
    }
}
