<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\MediaCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with(['user', 'mediaCategories'])
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
            'media_category_ids' => 'array',
            'media_category_ids.*' => 'exists:media_categories,id'
        ]);

        // Générer le slug si non fourni
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['user_id'] = auth()->id();

        $article = Article::create($validated);

        // Attacher les catégories média si fournies
        if (isset($validated['media_category_ids'])) {
            $article->mediaCategories()->attach($validated['media_category_ids']);
        }

        return response()->json($article->load(['user', 'mediaCategories']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return response()->json($article->load(['user', 'mediaCategories']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'nullable|string|unique:articles,slug,' . $article->id,
            'is_published' => 'boolean',
            'media_category_ids' => 'array',
            'media_category_ids.*' => 'exists:media_categories,id'
        ]);

        // Générer le slug si modifié
        if (isset($validated['title']) && ($validated['slug'] ?? '') === '') {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $article->update($validated);

        // Synchroniser les catégories média
        if (isset($validated['media_category_ids'])) {
            $article->mediaCategories()->sync($validated['media_category_ids']);
        }

        return response()->json($article->load(['user', 'mediaCategories']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
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