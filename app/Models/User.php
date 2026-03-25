<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'password', 'role', 'is_suspended', 'is_banned',
    // Profil talent étendu (H-02)
    'civilite', 'titre_poste', 'telephone', 'date_naissance', 'nationalite',
    'ville', 'pays', 'disponibilite', 'mobilite', 'source_provenance', 'ref_ancien_crm',
    // FK simples (H-02)
    'study_level_id', 'experience_id',
    // Statut CRM (H-03)
    'statut_crm',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_suspended'      => 'boolean',
            'is_banned'         => 'boolean',
            'date_naissance'    => 'date',
        ];
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Get the role model
     */
    public function roleModel()
    {
        return $this->belongsTo(SimpleRole::class, 'role', 'name');
    }

    /**
     * Relation avec les catégories de média créées par cet utilisateur
     */
    public function mediaCategories()
    {
        return $this->hasMany(MediaCategory::class, 'created_by');
    }

    /**
     * Relation avec les articles créés par cet utilisateur
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function favorisOffres()
    {
        return $this->belongsToMany(Offre::class, 'offre_favori', 'talent_id', 'offre_id');
    }

    // Relations profil talent
    public function studyLevel()
    {
        return $this->belongsTo(StudyLevel::class);
    }

    public function experience()
    {
        return $this->belongsTo(Experience::class);
    }

    public function activitySectors()
    {
        return $this->belongsToMany(ActivitySector::class, 'user_activity_sector');
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'user_language');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skill');
    }
}
