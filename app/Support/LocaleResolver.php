<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class LocaleResolver
{
    public const SUPPORTED = ['fr', 'en'];

    /**
     * Priorité : explicite (body/query/header) → préférence persistée au compte
     * → Accept-Language. Une langue explicite (X-Locale, body, préférence compte)
     * prime donc sur le navigateur.
     */
    public static function resolve(?Request $request = null, ?User $user = null, ?string $preferred = null): string
    {
        $persistedLocale = self::normalize(($user ?? $request?->user())?->locale);

        return self::normalize(
            $preferred
            ?? $request?->input('locale')
            ?? $request?->query('locale')
            ?? self::normalize($request?->header('X-Locale'))
            ?? $persistedLocale
            ?? $request?->getPreferredLanguage(self::SUPPORTED)
        ) ?? self::fallback();
    }

    public static function normalize(?string $locale): ?string
    {
        if (!is_string($locale) || $locale === '') {
            return null;
        }

        $normalized = strtolower(str_replace('_', '-', trim($locale)));
        $baseLocale = explode('-', $normalized)[0];

        return in_array($baseLocale, self::SUPPORTED, true) ? $baseLocale : null;
    }

    public static function fallback(): string
    {
        return self::normalize(config('app.fallback_locale'))
            ?? self::normalize(config('app.locale'))
            ?? 'fr';
    }
}
