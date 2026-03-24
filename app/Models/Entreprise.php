<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Offre;

class Entreprise extends Model
{
    protected $fillable = [
        'user_id', 'nom', 'logo', 'description',
        'site_web', 'telephone', 'adresse', 'ville', 'pays',
        'activity_sector_id',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? url(Storage::url($this->logo)) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activitySector()
    {
        return $this->belongsTo(ActivitySector::class);
    }

    public function evenements()
    {
        return $this->belongsToMany(Evenement::class, 'evenement_entreprise');
    }

    public function offres()
    {
        return $this->hasMany(Offre::class);
    }
}
