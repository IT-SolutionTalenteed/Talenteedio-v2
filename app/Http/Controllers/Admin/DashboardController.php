<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MediaCategory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord admin
     */
    public function index()
    {
        $user = auth()->user();
        
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_talents' => User::where('role', 'talent')->count(),
            'total_entreprises' => User::where('role', 'entreprise')->count(),
            'total_media_categories' => MediaCategory::count(),
            'active_media_categories' => MediaCategory::where('is_active', true)->count(),
        ];
        
        return response()->json([
            'message' => 'Tableau de bord administrateur',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => $stats
        ]);
    }
}
