<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ObservatorySubmission;
use Illuminate\Http\Request;

class ObservatoryController extends Controller
{
    /**
     * Enregistre une soumission au formulaire Africa Talent Observatory.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prenom'                 => 'required|string|max:255',
            'email'                  => 'required|email|max:255',
            'pays_residence'         => 'required|string|max:255',
            'pays_origine'           => 'required|string|max:255',
            'secteur_activite'       => 'required|string|max:255',
            'lien_diaspora'          => 'required|string|max:255',
            'consent_data'           => 'accepted',
            'consent_communications' => 'nullable|boolean',
        ]);

        $validated['consent_data'] = true;
        $validated['consent_communications'] = (bool) ($request->input('consent_communications', false));

        ObservatorySubmission::create($validated);

        return response()->json([
            'message' => 'Merci ! Votre participation à Africa Talent Observatory a bien été enregistrée.',
        ], 201);
    }
}
