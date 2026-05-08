<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Experience;
use App\Models\JobContract;
use App\Models\JobMode;
use App\Models\Offre;
use App\Models\Skill;
use App\Models\StudyLevel;
use App\Traits\CheckPlanLimits;
use Illuminate\Http\Request;

class OffreController extends Controller
{
    use CheckPlanLimits;

    private function getEntreprise(): Entreprise
    {
        return Entreprise::with('plan')->where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $entreprise = $this->getEntreprise();

        $offres = Offre::where('entreprise_id', $entreprise->id)
            ->notArchived() // Filtrer les offres non-archivées par défaut
            ->with(['jobContracts', 'jobModes', 'skills', 'studyLevels', 'experiences', 'languages'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($offres);
    }

    public function archived()
    {
        $entreprise = $this->getEntreprise();

        $offres = Offre::where('entreprise_id', $entreprise->id)
            ->archived() // Récupérer uniquement les offres archivées
            ->with(['jobContracts', 'jobModes', 'skills', 'studyLevels', 'experiences', 'languages'])
            ->orderByDesc('archived_at')
            ->get();

        return response()->json($offres);
    }

    public function archive(Offre $offre)
    {
        $entreprise = $this->getEntreprise();
        abort_if($offre->entreprise_id !== $entreprise->id, 403);

        if ($offre->isArchived()) {
            return response()->json(['message' => 'Cette offre est déjà archivée.'], 400);
        }

        $offre->archive();

        return response()->json([
            'message' => 'Offre archivée avec succès.',
            'offre' => $offre
        ]);
    }

    public function unarchive(Offre $offre)
    {
        $entreprise = $this->getEntreprise();
        abort_if($offre->entreprise_id !== $entreprise->id, 403);

        if (!$offre->isArchived()) {
            return response()->json(['message' => 'Cette offre n\'est pas archivée.'], 400);
        }

        $offre->unarchive();

        return response()->json([
            'message' => 'Offre désarchivée avec succès.',
            'offre' => $offre
        ]);
    }

    public function referentiels()
    {
        return response()->json([
            'job_contracts'  => JobContract::orderBy('name')->get(),
            'job_modes'      => JobMode::orderBy('name')->get(),
            'skills'         => Skill::orderBy('name')->get(),
            'study_levels'   => StudyLevel::orderBy('name')->get(),
            'experiences'    => Experience::orderBy('name')->get(),
            'languages'      => \App\Models\Language::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $entreprise = $this->getEntreprise();
        $this->checkOffreLimit($entreprise);

        $request->validate([
            'titre'           => 'required|string|max:255',
            'job_contract_ids'=> 'nullable|array',
            'job_mode_ids'    => 'nullable|array',
            'skill_ids'       => 'nullable|array',
            'study_level_ids' => 'nullable|array',
            'experience_ids'  => 'nullable|array',
            'language_ids'    => 'nullable|array',
        ]);

        $offre = Offre::create(array_merge(
            $request->only([
                'titre', 'mission', 'client', 'profil_recherche', 'a_propos',
                'liste_offre', 'description', 'date_mise_en_ligne', 'date_limite',
                'salaire', 'salaire_min', 'salaire_max', 'fourchette_salariale', 'localisation',
            ]),
            ['entreprise_id' => $entreprise->id]
        ));

        $this->syncRelations($offre, $request);

        return response()->json($offre->load(['jobContracts', 'jobModes', 'skills', 'studyLevels', 'experiences', 'languages']), 201);
    }

    public function show(Offre $offre)
    {
        $entreprise = $this->getEntreprise();
        abort_if($offre->entreprise_id !== $entreprise->id, 403);

        return response()->json($offre->load(['jobContracts', 'jobModes', 'skills', 'studyLevels', 'experiences', 'languages']));
    }

    public function update(Request $request, Offre $offre)
    {
        $entreprise = $this->getEntreprise();
        abort_if($offre->entreprise_id !== $entreprise->id, 403);

        $request->validate([
            'titre' => 'required|string|max:255',
        ]);

        $offre->update($request->only([
            'titre', 'mission', 'client', 'profil_recherche', 'a_propos',
            'liste_offre', 'description', 'date_mise_en_ligne', 'date_limite',
            'salaire', 'salaire_min', 'salaire_max', 'fourchette_salariale', 'localisation',
        ]));

        $this->syncRelations($offre, $request);

        return response()->json($offre->load(['jobContracts', 'jobModes', 'skills', 'studyLevels', 'experiences', 'languages']));
    }

    public function destroy(Offre $offre)
    {
        $entreprise = $this->getEntreprise();
        abort_if($offre->entreprise_id !== $entreprise->id, 403);

        $offre->delete();

        return response()->json(null, 204);
    }

    private function syncRelations(Offre $offre, Request $request): void
    {
        $offre->jobContracts()->sync($request->input('job_contract_ids', []));
        $offre->jobModes()->sync($request->input('job_mode_ids', []));
        $offre->skills()->sync($request->input('skill_ids', []));
        $offre->studyLevels()->sync($request->input('study_level_ids', []));
        $offre->experiences()->sync($request->input('experience_ids', []));
        $offre->languages()->sync($request->input('language_ids', []));
    }
}
