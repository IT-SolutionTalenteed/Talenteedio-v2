<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $request->bearerToken();
        
        Log::info('Debug Auth Middleware', [
            'user' => $user ? $user->toArray() : null,
            'token_present' => !empty($token),
            'token_preview' => $token ? substr($token, 0, 20) . '...' : null,
            'headers' => $request->headers->all(),
        ]);
        
        return $next($request);
    }
}