<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = MediaCategory::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:media_categories,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        $category = MediaCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
            'is_active' => $request->get('is_active', true),
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Catégorie créée avec succès',
            'category' => $category->load('creator')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MediaCategory $mediaCategory)
    {
        return response()->json($mediaCategory->load('creator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MediaCategory $mediaCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:media_categories,name,' . $mediaCategory->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        $mediaCategory->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
            'is_active' => $request->get('is_active', $mediaCategory->is_active)
        ]);

        return response()->json([
            'message' => 'Catégorie mise à jour avec succès',
            'category' => $mediaCategory->load('creator')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MediaCategory $mediaCategory)
    {
        $mediaCategory->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès'
        ]);
    }

    /**
     * Toggle the active status of a category
     */
    public function toggleStatus(MediaCategory $mediaCategory)
    {
        $mediaCategory->update([
            'is_active' => !$mediaCategory->is_active
        ]);

        return response()->json([
            'message' => 'Statut de la catégorie mis à jour',
            'category' => $mediaCategory->load('creator')
        ]);
    }

    /**
     * Get active categories only
     */
    public function active()
    {
        $categories = MediaCategory::active()
            ->with('creator')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}
