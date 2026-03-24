<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieEvenement extends Model
{
    protected $table = 'categorie_evenements';

    protected $fillable = [
        'titre', 'description', 'image', 'video',
        'galerie', 'liste_details', 'liste_temoignages', 'liste_faqs',
    ];

    protected $casts = [
        'galerie'           => 'array',
        'liste_details'     => 'array',
        'liste_temoignages' => 'array',
        'liste_faqs'        => 'array',
    ];

    protected $appends = ['image_url', 'video_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? url(\Illuminate\Support\Facades\Storage::url($this->image)) : null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ? url(\Illuminate\Support\Facades\Storage::url($this->video)) : null;
    }

    public function evenements()
    {
        return $this->hasMany(Evenement::class);
    }
}
