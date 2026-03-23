<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        if (!$user->hasRole($role)) {
            return response()->json([
                'message' => 'Accès non autorisé. Rôle requis: ' . $role
            ], 403);
        }

        return $next($request);
    }
}