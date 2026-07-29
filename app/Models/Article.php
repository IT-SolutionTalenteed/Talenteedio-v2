<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'slug',
        'is_published',
        'published_at',
        'user_id',
        'entreprise_id',
        'image',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? url(Storage::url($this->image)) : null;
    }

    /**
     * Get the user that owns the article.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * The media categories that belong to the article.
     */
    public function mediaCategories(): BelongsToMany
    {
        return $this->belongsToMany(MediaCategory::class);
    }

    /**
     * Galerie d'images / vidéos supplémentaires de l'article.
     */
    public function media(): HasMany
    {
        return $this->hasMany(ArticleMedia::class)->orderBy('position');
    }

    public function images(): HasMany
    {
        return $this->media()->where('type', 'image');
    }

    public function videos(): HasMany
    {
        return $this->media()->where('type', 'video');
    }
}