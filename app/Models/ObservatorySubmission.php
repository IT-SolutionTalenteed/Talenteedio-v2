<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservatorySubmission extends Model
{
    protected $fillable = [
        'prenom',
        'email',
        'pays_residence',
        'pays_origine',
        'secteur_activite',
        'lien_diaspora',
        'consent_data',
        'consent_communications',
    ];

    protected $casts = [
        'consent_data' => 'boolean',
        'consent_communications' => 'boolean',
    ];
}
