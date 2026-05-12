<?php

namespace App\Http\Middleware;

use App\Support\LocaleResolver;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetRequestLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $locale = LocaleResolver::resolve($request, $user);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        // Avant le contrôleur (emails notamment) pour que $user->locale reflète cette requête.
        if ($user !== null && $user->locale !== $locale) {
            $user->forceFill(['locale' => $locale])->saveQuietly();
        }

        return $next($request);
    }
}
