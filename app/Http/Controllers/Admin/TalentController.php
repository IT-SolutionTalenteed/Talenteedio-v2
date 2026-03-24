<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    public function index(Request $request)
    {
        $talents = User::where('role', 'talent')
            ->orderBy('name')
            ->paginate(20);

        return response()->json($talents);
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
