<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivitySector;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class ATSRegistrationController extends Controller
{
    /**
     * Enregistrement d'un TALENT
     * POST /api/public/ats/register
     */
    public function registerTalent(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'secteur_souhaite_id' => 'required|integer|exists:activity_sectors,id',
            'experience_id' => 'required|integer|exists:experiences,id',
            'ville' => 'nullable|string|max:255',
            'pays' => 'required|string|max:255',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5 Mo max
            'titre_poste' => 'nullable|string|max:255',
            'competences' => 'nullable|string',
            'pays_souhaites' => 'nullable|array',
            'villes_souhaitees' => 'nullable|array',
        ]);

        try {
            // Upload du CV si présent
            $cvPath = null;
            if ($request->hasFile('cv')) {
                $cvPath = $request->file('cv')->store('cvs', 'public');
            }

            // Créer l'utilisateur avec status = 'pending' et password = NULL
            $user = User::create([
                'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'role' => 'talent',
                'status' => 'pending',
                'password' => null, // Sera défini lors de la création du mot de passe
                'secteur_souhaite_id' => $validated['secteur_souhaite_id'],
                'experience_id' => $validated['experience_id'],
                'ville' => $validated['ville'] ?? null,
                'pays' => $validated['pays'],
                'cv_path' => $cvPath,
                'titre_poste' => $validated['titre_poste'] ?? null,
                'competences' => $validated['competences'] ?? null,
                'pays_souhaites' => $validated['pays_souhaites'] ?? null,
                'villes_souhaitees' => $validated['villes_souhaitees'] ?? null,
                'matching_completed' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inscription enregistrée avec succès.',
                'user_id' => $user->id,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'inscription.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistrement d'une ENTREPRISE
     * POST /api/public/ats/corporate/register
     */
    public function registerCorporate(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'required|string|max:50',
            'entreprise' => 'required|string|max:255',
            'secteur_souhaite_id' => 'required|integer|exists:activity_sectors,id',
            'taille_entreprise' => 'required|string|max:100',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
        ]);

        try {
            $user = User::create([
                'name' => trim(($validated['first_name'] ?? '') . ' ' . $validated['last_name']),
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'role' => 'entreprise',
                'status' => 'pending',
                'password' => null,
                'telephone' => $validated['telephone'],
                'entreprise' => $validated['entreprise'],
                'secteur_souhaite_id' => $validated['secteur_souhaite_id'],
                'taille_entreprise' => $validated['taille_entreprise'],
                'ville' => $validated['ville'] ?? null,
                'pays' => $validated['pays'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inscription enregistrée avec succès.',
                'user_id' => $user->id,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'inscription.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Création du mot de passe pour un TALENT
     * POST /api/public/ats/set-password
     */
    public function setPasswordTalent(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            $user = User::where('email', $validated['email'])
                        ->where('role', 'talent')
                        ->where('status', 'pending')
                        ->firstOrFail();

            // Mettre à jour le mot de passe et activer le compte
            $user->update([
                'password' => Hash::make($validated['password']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Envoyer le mail de bienvenue (ne pas bloquer si erreur)
            try {
                $this->sendWelcomeEmailTalent($user);
            } catch (\Exception $mailError) {
                \Log::warning('Erreur envoi mail bienvenue talent: ' . $mailError->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable ou compte déjà activé.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Création du mot de passe pour une ENTREPRISE
     * POST /api/public/ats/corporate/set-password
     */
    public function setPasswordCorporate(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            $user = User::where('email', $validated['email'])
                        ->where('role', 'entreprise')
                        ->where('status', 'pending')
                        ->firstOrFail();

            $user->update([
                'password' => Hash::make($validated['password']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Envoyer le mail de bienvenue entreprise (ne pas bloquer si erreur)
            try {
                $this->sendWelcomeEmailCorporate($user);
            } catch (\Exception $mailError) {
                \Log::warning('Erreur envoi mail bienvenue entreprise: ' . $mailError->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable ou compte déjà activé.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Récupérer la liste des secteurs d'activité
     * GET /api/public/ats/activity-sectors
     */
    public function getActivitySectors()
    {
        $sectors = ActivitySector::orderBy('name')->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $sectors
        ]);
    }

    /**
     * Récupérer la liste des niveaux d'expérience
     * GET /api/public/ats/experiences
     */
    public function getExperiences()
    {
        $experiences = Experience::orderBy('id')->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $experiences
        ]);
    }

    /**
     * Envoyer le mail de bienvenue aux talents
     */
    private function sendWelcomeEmailTalent(User $user)
    {
        Mail::to($user->email)->send(new \App\Mail\WelcomeTalentMail($user));
    }

    /**
     * Envoyer le mail de bienvenue aux entreprises
     */
    private function sendWelcomeEmailCorporate(User $user)
    {
        Mail::to($user->email)->send(new \App\Mail\WelcomeCorporateMail($user));
    }
}
