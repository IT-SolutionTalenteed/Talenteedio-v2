<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offre;
use Illuminate\Http\Request;

class OffreController extends Controller
{
    private array $relations = ['jobContracts', 'jobModes', 'skills', 'studyLevels', 'experiences'];

    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 25), 100);
        $search  = trim($request->get('search', ''));

        $query = Offre::with($this->relations)->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('titre',       'like', "%{$search}%")
                  ->orWhere('client',      'like', "%{$search}%")
                  ->orWhere('localisation','like', "%{$search}%");
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'                => 'required|string|max:255',
            'mission'              => 'nullable|string',
            'client'               => 'nullable|string|max:255',
            'profil_recherche'     => 'nullable|string',
            'a_propos'             => 'nullable|string',
            'liste_offre'          => 'nullable|string',
            'description'          => 'nullable|string',
            'date_mise_en_ligne'   => 'nullable|date',
            'date_limite'          => 'nullable|date',
            'salaire'              => 'nullable|numeric|min:0',
            'fourchette_salariale' => 'nullable|string|max:255',
            'localisation'         => 'nullable|string|max:255',
            'nombre_candidatures'  => 'nullable|integer|min:0',
            'image'                => 'nullable|image|max:2048',
            'job_contract_ids'     => 'array',
            'job_contract_ids.*'   => 'exists:job_contracts,id',
            'job_mode_ids'         => 'array',
            'job_mode_ids.*'       => 'exists:job_modes,id',
            'skill_ids'            => 'array',
            'skill_ids.*'          => 'exists:skills,id',
            'study_level_ids'      => 'array',
            'study_level_ids.*'    => 'exists:study_levels,id',
            'experience_ids'       => 'array',
            'experience_ids.*'     => 'exists:experiences,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('offres', 'public');
        }

        $offre = Offre::create($validated);
        $this->syncRelations($offre, $validated);

        return response()->json($offre->load($this->relations), 201);
    }

    public function show(Offre $offre)
    {
        return response()->json($offre->load($this->relations));
    }

    public function update(Request $request, Offre $offre)
    {
        $validated = $request->validate([
            'titre'                => 'required|string|max:255',
            'mission'              => 'nullable|string',
            'client'               => 'nullable|string|max:255',
            'profil_recherche'     => 'nullable|string',
            'a_propos'             => 'nullable|string',
            'liste_offre'          => 'nullable|string',
            'description'          => 'nullable|string',
            'date_mise_en_ligne'   => 'nullable|date',
            'date_limite'          => 'nullable|date',
            'salaire'              => 'nullable|numeric|min:0',
            'fourchette_salariale' => 'nullable|string|max:255',
            'localisation'         => 'nullable|string|max:255',
            'nombre_candidatures'  => 'nullable|integer|min:0',
            'image'                => 'nullable|image|max:2048',
            'remove_image'         => 'nullable|boolean',
            'job_contract_ids'     => 'array',
            'job_contract_ids.*'   => 'exists:job_contracts,id',
            'job_mode_ids'         => 'array',
            'job_mode_ids.*'       => 'exists:job_modes,id',
            'skill_ids'            => 'array',
            'skill_ids.*'          => 'exists:skills,id',
            'study_level_ids'      => 'array',
            'study_level_ids.*'    => 'exists:study_levels,id',
            'experience_ids'       => 'array',
            'experience_ids.*'     => 'exists:experiences,id',
        ]);

        if ($request->hasFile('image')) {
            if ($offre->image) {
                \Storage::disk('public')->delete($offre->image);
            }
            $validated['image'] = $request->file('image')->store('offres', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($offre->image) {
                \Storage::disk('public')->delete($offre->image);
            }
            $validated['image'] = null;
        }

        $offre->update($validated);
        $this->syncRelations($offre, $validated);

        return response()->json($offre->load($this->relations));
    }

    public function destroy(Offre $offre)
    {
        if ($offre->image) {
            \Storage::disk('public')->delete($offre->image);
        }
        $offre->delete();

        return response()->json(['message' => 'Offre supprimée avec succès']);
    }

    public function referentiels()
    {
        return response()->json([
            'job_contracts' => \App\Models\JobContract::orderBy('name')->get(),
            'job_modes'     => \App\Models\JobMode::orderBy('name')->get(),
            'skills'        => \App\Models\Skill::orderBy('name')->get(),
            'study_levels'  => \App\Models\StudyLevel::orderBy('name')->get(),
            'experiences'   => \App\Models\Experience::orderBy('name')->get(),
        ]);
    }

    private function syncRelations(Offre $offre, array $data): void
    {
        $offre->jobContracts()->sync($data['job_contract_ids'] ?? []);
        $offre->jobModes()->sync($data['job_mode_ids'] ?? []);
        $offre->skills()->sync($data['skill_ids'] ?? []);
        $offre->studyLevels()->sync($data['study_level_ids'] ?? []);
        $offre->experiences()->sync($data['experience_ids'] ?? []);
    }
}
