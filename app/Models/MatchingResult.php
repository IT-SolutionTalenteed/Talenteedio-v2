<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingResult extends Model
{
    protected $fillable = [
        'user_id',
        'evenement_id',
        'poste_recherche',
        'resultats',
        'cv_path',
    ];

    protected $casts = [
        'resultats' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }
}
