<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivitySector extends Model
{
    protected $fillable = ['name'];

    /**
     * Relation avec les entreprises
     */
    public function entreprises()
    {
        return $this->hasMany(Entreprise::class, 'activity_sector_id');
    }
}
