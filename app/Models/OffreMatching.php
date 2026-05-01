<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffreMatching extends Model
{
    protected $fillable = [
        'talent_id',
        'offre_id',
        'cv_path',
        'score',
        'raison',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'score' => 'integer',
    ];

    /**
     * Relation avec le talent.
     */
    public function talent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'talent_id');
    }

    /**
     * Relation avec l'offre.
     */
    public function offre(): BelongsTo
    {
        return $this->belongsTo(Offre::class);
    }
}
