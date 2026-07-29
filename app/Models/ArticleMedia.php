<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ArticleMedia extends Model
{
    protected $table = 'article_media';

    protected $fillable = [
        'article_id',
        'type',
        'path',
        'position',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        return $this->path ? url(Storage::url($this->path)) : null;
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
