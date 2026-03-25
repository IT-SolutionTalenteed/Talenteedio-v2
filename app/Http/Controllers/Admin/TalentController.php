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
            ->orderBy('name')
            ->paginate(20);

        return response()->json($talents);
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
