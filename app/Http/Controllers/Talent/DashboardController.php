<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord du talent
     */
    public function index()
    {
        $user = auth()->user();
        
        return response()->json([
            'message' => 'Tableau de bord talent',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => [
                'total_projects' => 0, // À implémenter plus tard
                'active_projects' => 0,
                'completed_projects' => 0,
            ]
        ]);
    }
}
