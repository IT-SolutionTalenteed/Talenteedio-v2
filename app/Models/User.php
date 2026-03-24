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

#[Fillable(['name', 'email', 'password', 'role', 'is_suspended', 'is_banned'])]
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
}
