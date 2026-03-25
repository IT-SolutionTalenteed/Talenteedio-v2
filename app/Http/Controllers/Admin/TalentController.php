<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    public function index(Request $request)
    {
        $talents = User::whereIn('role', ['talent', 'consultant_externe'])
            ->with(['studyLevel', 'experience', 'activitySectors', 'languages', 'skills'])
            ->orderBy('name')
            ->paginate(20);

        return response()->json($talents);
    }

    public function show(User $user)
    {
        abort_if(!in_array($user->role, ['talent', 'consultant_externe']), 403);
        return response()->json($user->load(['studyLevel', 'experience', 'activitySectors', 'languages', 'skills']));
    }

    public function updateProfil(Request $request, User $user)
    {
        abort_if(!in_array($user->role, ['talent', 'consultant_externe']), 403);

        $validated = $request->validate([
            'civilite'          => 'nullable|string|max:10',
            'titre_poste'       => 'nullable|string|max:255',
            'telephone'         => 'nullable|string|max:30',
            'date_naissance'    => 'nullable|date',
            'nationalite'       => 'nullable|string|max:100',
            'ville'             => 'nullable|string|max:100',
            'pays'              => 'nullable|string|max:100',
            'disponibilite'     => 'nullable|string|max:100',
            'mobilite'              => 'nullable|string|max:100',
            'situation_familiale'   => 'nullable|in:celibataire,marie,pacse,divorce,veuf',
            'source_provenance'     => 'nullable|string|max:100',
            'study_level_id'    => 'nullable|exists:study_levels,id',
            'experience_id'     => 'nullable|exists:experiences,id',
            'activity_sector_ids' => 'nullable|array',
            'activity_sector_ids.*' => 'exists:activity_sectors,id',
            'language_ids'      => 'nullable|array',
            'language_ids.*'    => 'exists:languages,id',
            'skill_ids'         => 'nullable|array',
            'skill_ids.*'       => 'exists:skills,id',
        ]);

        $user->update(collect($validated)->except(['activity_sector_ids', 'language_ids', 'skill_ids'])->toArray());

        if (isset($validated['activity_sector_ids'])) {
            $user->activitySectors()->sync($validated['activity_sector_ids']);
        }
        if (isset($validated['language_ids'])) {
            $user->languages()->sync($validated['language_ids']);
        }
        if (isset($validated['skill_ids'])) {
            $user->skills()->sync($validated['skill_ids']);
        }

        return response()->json($user->load(['studyLevel', 'experience', 'activitySectors', 'languages', 'skills']));
    }

    public function updateStatutCrm(Request $request, User $user)
    {
        abort_if(!in_array($user->role, ['talent', 'consultant_externe']), 403);

        $request->validate([
            'statut_crm' => 'nullable|in:a_traiter,en_cours_qualif,vivier,top_profil,converti_ressource,recrute_client,ne_plus_contacter',
        ]);

        $data = ['statut_crm' => $request->statut_crm];

        // Ne plus contacter → bannir automatiquement
        if ($request->statut_crm === 'ne_plus_contacter') {
            $data['is_banned'] = true;
        }

        $user->update($data);

        return response()->json($user->only(['id', 'statut_crm', 'is_banned']));
    }

    public function toggleSuspend(User $user)
    {
        abort_if($user->role !== 'talent', 403, 'Utilisateur non talent');

        $user->update(['is_suspended' => !$user->is_suspended]);

        return response()->json(['is_suspended' => $user->is_suspended]);
    }

    public function toggleBan(User $user)
    {
        abort_if($user->role !== 'talent', 403, 'Utilisateur non talent');

        $user->update(['is_banned' => !$user->is_banned]);

        return response()->json(['is_banned' => $user->is_banned]);
    }

    public function destroy(User $user)
    {
        abort_if($user->role !== 'talent', 403, 'Utilisateur non talent');

        $user->delete();

        return response()->json(null, 204);
    }
}
