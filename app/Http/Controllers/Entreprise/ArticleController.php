<?php

namespace App\Http\Controllers\Entreprise;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Entreprise;
use App\Models\MediaCategory;
use App\Traits\CheckPlanLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    use CheckPlanLimits;

    private function getEntreprise(): Entreprise
    {
        return Entreprise::with('plan')->where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $entreprise = $this->getEntreprise();

        $articles = Article::where('entreprise_id', $entreprise->id)
            ->with(['mediaCategories'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($articles);
    }

    public function referentiels()
    {
        return response()->json([
            'media_categories' => MediaCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $entreprise = $this->getEntreprise();
        $this->checkArticleLimit($entreprise);

        $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'image'       => 'nullable|image|max:4096',
            'category_ids'=> 'nullable|array',
        ]);

        $data = [
            'title'         => $request->title,
            'content'       => $request->content,
            'slug'          => Str::slug($request->title) . '-' . uniqid(),
            'is_published'  => $request->boolean('is_published', false),
            'user_id'       => auth()->id(),
            'entreprise_id' => $entreprise->id,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        $article = Article::create($data);
        $article->mediaCategories()->sync($request->input('category_ids', []));

        return response()->json($article->load('mediaCategories'), 201);
    }

    public function show(Article $article)
    {
        $entreprise = $this->getEntreprise();
        abort_if($article->entreprise_id !== $entreprise->id, 403);

        return response()->json($article->load('mediaCategories'));
    }

    public function update(Request $request, Article $article)
    {
        $entreprise = $this->getEntreprise();
        abort_if($article->entreprise_id !== $entreprise->id, 403);

        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:4096',
        ]);

        $data = [
            'title'        => $request->title,
            'content'      => $request->content,
            'is_published' => $request->boolean('is_published', false),
        ];

        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        $article->update($data);
        $article->mediaCategories()->sync($request->input('category_ids', []));

        return response()->json($article->fresh()->load('mediaCategories'));
    }

    public function destroy(Article $article)
    {
        $entreprise = $this->getEntreprise();
        abort_if($article->entreprise_id !== $entreprise->id, 403);

        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
        $article->mediaCategories()->detach();
        $article->delete();

        return response()->json(null, 204);
    }
}
