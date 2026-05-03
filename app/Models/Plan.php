<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'max_offres',
        'max_articles',
        'max_evenements',
        'max_entretiens_par_evenement',
        'max_candidatures_par_offre',
        'is_active',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relation avec les entreprises (si vous voulez lier les plans aux entreprises)
     */
    public function entreprises()
    {
        return $this->hasMany(Entreprise::class);
    }
}
