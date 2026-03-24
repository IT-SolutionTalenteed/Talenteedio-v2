<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $table = 'offres';

    protected $fillable = [
        'titre', 'mission', 'client', 'profil_recherche', 'a_propos',
        'liste_offre', 'description', 'date_mise_en_ligne', 'date_limite',
        'salaire', 'fourchette_salariale', 'localisation', 'nombre_candidatures',
    ];

    protected $casts = [
        'date_mise_en_ligne' => 'date',
        'date_limite'        => 'date',
        'salaire'            => 'decimal:2',
        'nombre_candidatures'=> 'integer',
    ];

    public function jobContracts()
    {
        return $this->belongsToMany(JobContract::class, 'offre_job_contract');
    }

    public function jobModes()
    {
        return $this->belongsToMany(JobMode::class, 'offre_job_mode');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'offre_skill');
    }

    public function studyLevels()
    {
        return $this->belongsToMany(StudyLevel::class, 'offre_study_level');
    }

    public function experiences()
    {
        return $this->belongsToMany(Experience::class, 'offre_experience');
    }
}
