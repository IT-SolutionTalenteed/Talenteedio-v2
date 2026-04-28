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
        'featured_events',
        'priority_support',
        'analytics',
        'is_active',
        'duration_days',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'featured_events' => 'boolean',
        'priority_support' => 'boolean',
        'analytics' => 'boolean',
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
