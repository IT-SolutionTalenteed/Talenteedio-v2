<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimpleRole extends Model
{
    protected $fillable = ['name', 'display_name'];

    /**
     * Get all users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role', 'name');
    }
}