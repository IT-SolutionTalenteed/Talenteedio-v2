<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord de l'entreprise
     */
    public function index()
    {
        $user = auth()->user();
        
        return response()->json([
            'message' => 'Tableau de bord entreprise',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => [
                'total_projects' => 0, // À implémenter plus tard
                'active_projects' => 0,
                'posted_projects' => 0,
            ]
        ]);
    }
}
