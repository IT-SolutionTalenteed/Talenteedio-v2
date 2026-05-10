<?php

namespace App\Mail\Concerns;

use App\Models\User;
use App\Support\LocaleResolver;

trait LocalizesMail
{
    protected function useLocale(?string $locale = null): void
    {
        $this->locale(LocaleResolver::normalize($locale) ?? LocaleResolver::fallback());
    }

    protected function useUserLocale(?User $user): void
    {
        $this->useLocale($user?->locale);
    }
}
