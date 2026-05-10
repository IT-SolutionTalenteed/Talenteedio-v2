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
        $locale = LocaleResolver::resolve($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        $response = $next($request);

        $user = $request->user();
        if ($user && $user->locale !== $locale) {
            $user->forceFill(['locale' => $locale])->saveQuietly();
        }

        return $response;
    }
}
