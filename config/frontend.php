<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL publique du front (SPA Vue)
    |--------------------------------------------------------------------------
    |
    | Utilisée pour construire les URL canoniques des aperçus de partage
    | (Open Graph). Le front n'est pas servi par Laravel : il faut donc son
    | URL publique explicitement.
    |
    */

    'url' => rtrim(env('FRONTEND_URL', 'https://talenteed.io'), '/'),

];
