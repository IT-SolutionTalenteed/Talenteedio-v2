<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// M-05 — Rappel 1h avant l'entretien (toutes les minutes)
Schedule::command('entretien:rappel')->everyMinute();

// M-06 — Demande de feedback 30min après la fin de l'entretien (toutes les minutes)
Schedule::command('entretien:demander-feedback')->everyMinute();
