<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Temoignage extends Model
{
    protected $fillable = ['auteur', 'poste', 'avatar', 'contenu'];

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? url(Storage::url($this->avatar)) : null;
    }

    public function categorieEvenements()
    {
        return $this->belongsToMany(CategorieEvenement::class, 'categorie_evenement_temoignage');
    }
}
