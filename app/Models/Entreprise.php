<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    protected $fillable = ['user_id', 'nom'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evenements()
    {
        return $this->belongsToMany(Evenement::class, 'evenement_entreprise');
    }
}
