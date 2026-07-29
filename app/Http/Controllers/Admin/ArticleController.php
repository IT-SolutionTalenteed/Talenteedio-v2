<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\MediaCategory;
use App\Services\CompressedImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function __construct(
        private CompressedImageStorage $compressedImages,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with(['user', 'mediaCategories', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($articles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'nullable|string|unique:articles,slug',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'media_category_ids' => 'array',
            'media_category_ids.*' => 'exists:media_categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'videos' => 'nullable|array',
            'videos.*' => 'file|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime|max:102400',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Par défaut, la date de publication est la date de création
        if (empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $validated['image'] = $this->compressedImages->store(
                $request->file('image'),
                'articles',
                'content'
            );
        }

        $article = Article::create($validated);

        if (isset($validated['media_category_ids'])) {
            $article->mediaCategories()->attach($validated['media_category_ids']);
        }

        $this->storeGalleryMedia($request, $article);

        return response()->json($article->load(['user', 'mediaCategories', 'media']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return response()->json($article->load(['user', 'mediaCategories', 'media']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'nullable|string|unique:articles,slug,'.$article->id,
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'media_category_ids' => 'array',
            'media_category_ids.*' => 'exists:media_categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'videos' => 'nullable|array',
            'videos.*' => 'file|mimetypes:video/mp4,video/webm,video/ogg,video/quicktime|max:102400',
            'removed_media_ids' => 'nullable|array',
            'removed_media_ids.*' => 'integer',
        ]);

        if (isset($validated['title']) && ($validated['slug'] ?? '') === '') {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $validated['image'] = $this->compressedImages->store(
                $request->file('image'),
                'articles',
                'content'
            );
        }

        $article->update($validated);

        if (isset($validated['media_category_ids'])) {
            $article->mediaCategories()->sync($validated['media_category_ids']);
        }

        // Suppression des médias de galerie retirés côté admin
        if (! empty($validated['removed_media_ids'])) {
            $toRemove = $article->media()->whereIn('id', $validated['removed_media_ids'])->get();
            foreach ($toRemove as $media) {
                Storage::disk('public')->delete($media->path);
                $media->delete();
            }
        }

        $this->storeGalleryMedia($request, $article);

        return response()->json($article->load(['user', 'mediaCategories', 'media']));
    }

    /**
     * Stocke les images et vidéos supplémentaires (galerie) uploadées pour un article.
     */
    private function storeGalleryMedia(Request $request, Article $article): void
    {
        $position = (int) $article->media()->max('position');

        foreach ((array) $request->file('images', []) as $image) {
            $path = $this->compressedImages->store($image, 'articles/gallery', 'content');
            $article->media()->create([
                'type' => 'image',
                'path' => $path,
                'position' => ++$position,
            ]);
        }

        foreach ((array) $request->file('videos', []) as $video) {
            $path = $video->store('articles/videos', 'public');
            $article->media()->create([
                'type' => 'video',
                'path' => $path,
                'position' => ++$position,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        // Nettoyage des fichiers de galerie (la cascade DB ne supprime pas les fichiers)
        foreach ($article->media as $media) {
            Storage::disk('public')->delete($media->path);
        }

        $article->delete();

        return response()->json(['message' => 'Article supprimé avec succès']);
    }

    /**
     * Get all media categories for article form
     */
    public function getMediaCategories()
    {
        $categories = MediaCategory::active()->get();

        return response()->json($categories);
    }
}
