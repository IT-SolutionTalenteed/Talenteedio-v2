<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'talent_id', 'entretien_id', 'note', 'commentaire',
    ];

    public function talent()
    {
        return $this->belongsTo(User::class, 'talent_id');
    }

    public function entretien()
    {
        return $this->belongsTo(Entretien::class);
    }
}
