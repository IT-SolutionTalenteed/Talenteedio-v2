<?php

namespace App\Providers;

use App\Models\Candidature;
use App\Models\Entreprise;
use App\Models\User;
use App\Observers\CandidatureObserver;
use App\Observers\EntrepriseObserver;
use App\Observers\TalentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        User::observe(TalentObserver::class);
        Entreprise::observe(EntrepriseObserver::class);
        Candidature::observe(CandidatureObserver::class);
    }
}
