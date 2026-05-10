<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class LocaleResolver
{
    public const SUPPORTED = ['fr', 'en'];

    public static function resolve(?Request $request = null, ?User $user = null, ?string $preferred = null): string
    {
        return self::normalize(
            $preferred
            ?? $request?->input('locale')
            ?? $request?->query('locale')
            ?? $request?->header('X-Locale')
            ?? $request?->getPreferredLanguage(self::SUPPORTED)
            ?? $user?->locale
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
