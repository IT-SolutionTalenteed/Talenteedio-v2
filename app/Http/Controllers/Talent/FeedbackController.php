<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Entretien;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Liste tous les feedbacks du talent connecté.
     */
    public function index()
    {
        $feedbacks = Feedback::where('talent_id', auth()->id())
            ->with(['entretien.entreprise', 'entretien.evenement'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($feedbacks);
    }

    /**
     * Soumettre un feedback pour un entretien.
     */
    public function store(Request $request, Entretien $entretien)
    {
        // Vérifier que l'entretien appartient au talent connecté
        abort_if($entretien->talent_id !== auth()->id(), 403);

        // Seuls les entretiens confirmés peuvent recevoir un feedback
        if ($entretien->statut !== 'confirme') {
            return response()->json(['message' => "Vous ne pouvez laisser un feedback que pour un entretien confirmé."], 422);
        }

        // Vérifier qu'aucun feedback n'existe déjà
        if (Feedback::where('talent_id', auth()->id())->where('entretien_id', $entretien->id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà soumis un feedback pour cet entretien.'], 422);
        }

        $validated = $request->validate([
            'note'        => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:2000',
        ]);

        $feedback = Feedback::create([
            'talent_id'    => auth()->id(),
            'entretien_id' => $entretien->id,
            'note'         => $validated['note'],
            'commentaire'  => $validated['commentaire'] ?? null,
        ]);

        return response()->json($feedback->load(['entretien.entreprise', 'entretien.evenement']), 201);
    }

    /**
     * Modifier un feedback existant.
     */
    public function update(Request $request, Feedback $feedback)
    {
        abort_if($feedback->talent_id !== auth()->id(), 403);

        $validated = $request->validate([
            'note'        => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:2000',
        ]);

        $feedback->update($validated);

        return response()->json($feedback->load(['entretien.entreprise', 'entretien.evenement']));
    }

    /**
     * Supprimer un feedback.
     */
    public function destroy(Feedback $feedback)
    {
        abort_if($feedback->talent_id !== auth()->id(), 403);
        $feedback->delete();
        return response()->json(['message' => 'Feedback supprimé.']);
    }
}
