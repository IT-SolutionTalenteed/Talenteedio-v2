<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Candidature extends Model
{
    protected $fillable = ['talent_id', 'offre_id', 'statut', 'cv', 'message'];

    protected $appends = ['cv_url'];

    public function getCvUrlAttribute(): ?string
    {
        return $this->cv ? url(Storage::url($this->cv)) : null;
    }

    public function talent()
    {
        return $this->belongsTo(User::class, 'talent_id');
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }
}
