<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entretien extends Model
{
    protected $fillable = [
        'talent_id', 'entreprise_id', 'evenement_id',
        'date', 'heure_debut', 'heure_fin', 'statut',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function talent()
    {
        return $this->belongsTo(User::class, 'talent_id');
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }
}
