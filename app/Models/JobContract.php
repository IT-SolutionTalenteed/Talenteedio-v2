<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobContract extends Model
{
    protected $fillable = ['name'];

    /**
     * Relation avec les offres (many-to-many)
     */
    public function offres()
    {
        return $this->belongsToMany(Offre::class, 'offre_job_contract');
    }
}
