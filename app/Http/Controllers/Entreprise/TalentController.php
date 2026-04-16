<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'talent')
            ->with(['studyLevel', 'experience', 'activitySectors', 'skills', 'languages'])
            ->orderByDesc('created_at');

        // Filtres optionnels
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('titre_poste', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ville')) {
            $query->where('ville', 'like', "%{$request->ville}%");
        }

        if ($request->filled('pays')) {
            $query->where('pays', $request->pays);
        }

        if ($request->filled('study_level_id')) {
            $query->where('study_level_id', $request->study_level_id);
        }

        if ($request->filled('experience_id')) {
            $query->where('experience_id', $request->experience_id);
        }

        if ($request->filled('skill_ids')) {
            $skillIds = is_array($request->skill_ids) ? $request->skill_ids : explode(',', $request->skill_ids);
            $query->whereHas('skills', function ($q) use ($skillIds) {
                $q->whereIn('skills.id', $skillIds);
            });
        }

        if ($request->filled('activity_sector_ids')) {
            $sectorIds = is_array($request->activity_sector_ids) ? $request->activity_sector_ids : explode(',', $request->activity_sector_ids);
            $query->whereHas('activitySectors', function ($q) use ($sectorIds) {
                $q->whereIn('activity_sectors.id', $sectorIds);
            });
        }

        $talents = $query->paginate($request->input('per_page', 15));

        return response()->json($talents);
    }

    public function show(User $talent)
    {
        if ($talent->role !== 'talent') {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $talent->load(['studyLevel', 'experience', 'activitySectors', 'skills', 'languages', 'candidatures.offre']);

        return response()->json($talent);
    }
}