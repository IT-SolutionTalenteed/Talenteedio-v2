<?php

namespace App\Providers;

use App\Models\Candidature;
use App\Models\Entreprise;
use App\Models\User;
use App\Observers\CandidatureObserver;
use App\Observers\EntrepriseObserver;
use App\Observers\TalentObserver;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, function () {
            if (extension_loaded('imagick')) {
                try {
                    return ImageManager::imagick();
                } catch (\Throwable) {
                    // use GD below
                }
            }

            return ImageManager::gd();
        });
    }

    public function boot(): void
    {
        User::observe(TalentObserver::class);
        Entreprise::observe(EntrepriseObserver::class);
        Candidature::observe(CandidatureObserver::class);
    }
}
