<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 25), 100);
        $search  = trim($request->get('search', ''));

        $query = User::where('role', 'admin')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function show(User $user)
    {
        abort_if($user->role !== 'admin', 403, 'Utilisateur non administrateur');
        return response()->json($user);
    }

    public function toggleSuspend(User $user)
    {
        abort_if($user->role !== 'admin', 403, 'Utilisateur non administrateur');

        $user->update(['is_suspended' => !$user->is_suspended]);

        return response()->json(['is_suspended' => $user->is_suspended]);
    }

    public function toggleBan(User $user)
    {
        abort_if($user->role !== 'admin', 403, 'Utilisateur non administrateur');

        $user->update(['is_banned' => !$user->is_banned]);

        return response()->json(['is_banned' => $user->is_banned]);
    }
}
