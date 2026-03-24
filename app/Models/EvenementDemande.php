<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvenementDemande extends Model
{
    protected $fillable = ['entreprise_id', 'evenement_id', 'statut', 'message'];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }
}
