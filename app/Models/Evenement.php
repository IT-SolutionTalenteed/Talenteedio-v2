<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    protected $fillable = [
        'titre', 'image_mise_en_avant', 'description', 'details_supplementaires',
        'date_debut', 'date_fin', 'heure_debut_journee', 'heure_fin_journee',
        'categorie_evenement_id', 'pays', 'ville', 'adresse', 'is_featured',
    ];

    protected $casts = [
        'date_debut'   => 'date',
        'date_fin'     => 'date',
        'is_featured'  => 'boolean',
    ];

    protected $appends = ['image_mise_en_avant_url'];

    public function getImageMiseEnAvantUrlAttribute(): ?string
    {
        return $this->image_mise_en_avant
            ? url(\Illuminate\Support\Facades\Storage::url($this->image_mise_en_avant))
            : null;
    }

    public function categorie()
    {
        return $this->belongsTo(CategorieEvenement::class, 'categorie_evenement_id');
    }

    public function entreprises()
    {
        return $this->belongsToMany(Entreprise::class, 'evenement_entreprise');
    }
}
